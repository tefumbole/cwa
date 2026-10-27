<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Fit gallery photos so the public page can load on a slow connection.
 * The card file is 800px wide. The zoom file fits inside 1600×1600.
 */
class GalleryImage
{
    const FULL = 1600;

    const CARD = 800;

    const QUALITY = 82;

    /**
     * @return array{file:string,thumb:string}|null
     */
    public static function storeUpload(UploadedFile $file, $dir)
    {
        $tmp = $file->getRealPath();
        if (! $tmp || ! is_file($tmp)) {
            return null;
        }
        $base = 'gallery_image_'.time().'_'.bin2hex(random_bytes(4));

        return self::writePair($tmp, $dir, $base);
    }

    /**
     * Rewrite an existing photo. Returns null when the file cannot be read.
     *
     * @return array{file:string,thumb:string,replaced:bool}|null
     */
    public static function optimizeExisting($absolutePath, $dir)
    {
        if (! is_file($absolutePath)) {
            return null;
        }
        $base = pathinfo($absolutePath, PATHINFO_FILENAME);
        if (substr($base, -5) === '_card') {
            return null;
        }
        $pair = self::writePair($absolutePath, $dir, $base.'_opt');
        if (! $pair) {
            return null;
        }
        $full = $dir.'/'.basename($pair['file']);
        $thumb = $dir.'/'.basename($pair['thumb']);
        if (realpath($absolutePath) && realpath($absolutePath) !== realpath($full)) {
            @unlink($absolutePath);
        }

        return [
            'file' => $pair['file'],
            'thumb' => $pair['thumb'],
            'replaced' => true,
            'full_path' => $full,
            'thumb_path' => $thumb,
        ];
    }

    public static function alreadyFitted($fullPath, $thumbPath)
    {
        $full = self::dimensions($fullPath);
        $thumb = self::dimensions($thumbPath);
        if (! $full || ! $thumb) {
            return false;
        }

        return $full['w'] <= self::FULL && $full['h'] <= self::FULL
            && $thumb['w'] <= self::CARD && $thumb['h'] <= self::CARD
            && self::isJpeg($fullPath) && self::isJpeg($thumbPath);
    }

    /**
     * @return array{file:string,thumb:string}|null
     */
    public static function writePair($source, $dir, $base)
    {
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $loaded = self::load($source);
        if (! $loaded) {
            return null;
        }

        $fullName = $base.'.jpg';
        $cardName = $base.'_card.jpg';
        $fullDest = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$fullName;
        $cardDest = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$cardName;

        $full = self::fit($loaded['im'], $loaded['w'], $loaded['h'], self::FULL, self::FULL);
        $card = self::fit($loaded['im'], $loaded['w'], $loaded['h'], self::CARD, self::CARD);
        imagedestroy($loaded['im']);

        $okFull = $full && @imagejpeg($full, $fullDest, self::QUALITY);
        $okCard = $card && @imagejpeg($card, $cardDest, self::QUALITY);
        if ($full) {
            imagedestroy($full);
        }
        if ($card) {
            imagedestroy($card);
        }
        if (! $okFull || ! $okCard || ! is_file($fullDest) || ! is_file($cardDest)) {
            @unlink($fullDest);
            @unlink($cardDest);

            return null;
        }
        @chmod($fullDest, 0664);
        @chmod($cardDest, 0664);

        return [
            'file' => 'images/gallery/'.$fullName,
            'thumb' => 'images/gallery/'.$cardName,
        ];
    }

    protected static function load($path)
    {
        if (! function_exists('imagecreatetruecolor') || ! is_file($path)) {
            return null;
        }
        $size = @getimagesize($path);
        if (! $size || empty($size[0]) || empty($size[1])) {
            return null;
        }
        switch ($size[2]) {
            case IMAGETYPE_JPEG:
                $im = @imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG:
                $im = @imagecreatefrompng($path);
                break;
            case IMAGETYPE_GIF:
                $im = @imagecreatefromgif($path);
                break;
            case IMAGETYPE_WEBP:
                $im = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false;
                break;
            default:
                $im = false;
        }
        if (! $im) {
            return null;
        }
        if ($size[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            $orientation = (int) ($exif['Orientation'] ?? 0);
            $angle = $orientation === 3 ? 180 : ($orientation === 6 ? -90 : ($orientation === 8 ? 90 : 0));
            if ($angle) {
                $rotated = @imagerotate($im, $angle, 0);
                if ($rotated) {
                    imagedestroy($im);
                    $im = $rotated;
                }
            }
        }

        return ['im' => $im, 'w' => imagesx($im), 'h' => imagesy($im)];
    }

    protected static function fit($src, $srcW, $srcH, $maxW, $maxH)
    {
        $scale = min($maxW / max(1, $srcW), $maxH / max(1, $srcH), 1);
        $dstW = max(1, (int) round($srcW * $scale));
        $dstH = max(1, (int) round($srcH * $scale));
        $dst = imagecreatetruecolor($dstW, $dstH);
        if (! $dst) {
            return null;
        }
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        return $dst;
    }

    protected static function dimensions($path)
    {
        if (! $path || ! is_file($path)) {
            return null;
        }
        $size = @getimagesize($path);
        if (! $size || empty($size[0]) || empty($size[1])) {
            return null;
        }

        return ['w' => (int) $size[0], 'h' => (int) $size[1]];
    }

    protected static function isJpeg($path)
    {
        $size = @getimagesize($path);

        return $size && isset($size[2]) && $size[2] === IMAGETYPE_JPEG;
    }
}
