<?php

namespace App\Services;

use Intervention\Image\Facades\Image;

class MembershipPortraitService
{
    const BLUE = '#003D82';
    const GOLD = '#C6A647';
    const GOLD_LIGHT = '#F5E6A8';

    /**
     * Center-crop to a square, composite onto CWA blue with a circular gold ring.
     *
     * @param  string  $sourcePath  Absolute path to the original selfie
     * @param  string  $destPath    Absolute path for the processed JPEG
     * @return bool
     */
    public function compose($sourcePath, $destPath)
    {
        if (! is_file($sourcePath)) {
            return false;
        }

        $dir = dirname($destPath);
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        try {
            $inner = 720;
            $canvasSize = 900;
            $photo = Image::make($sourcePath)->fit($inner, $inner, function ($constraint) {
                $constraint->upsize();
            });

            $mask = Image::canvas($inner, $inner);
            $mask->circle($inner, $inner / 2, $inner / 2, function ($draw) {
                $draw->background('#ffffff');
            });
            $photo->mask($mask, false);

            $canvas = Image::canvas($canvasSize, $canvasSize, self::BLUE);
            $cx = (int) ($canvasSize / 2);
            $cy = (int) ($canvasSize / 2);

            for ($i = 8; $i >= 1; $i--) {
                $d = $inner + 28 + ($i * 10);
                $canvas->circle($d, $cx, $cy, function ($draw) {
                    $draw->border(3, self::GOLD_LIGHT);
                });
            }

            $canvas->insert($photo, 'center');

            $canvas->circle($inner + 28, $cx, $cy, function ($draw) {
                $draw->border(14, self::GOLD);
            });
            $canvas->circle($inner + 52, $cx, $cy, function ($draw) {
                $draw->border(3, self::GOLD_LIGHT);
            });

            $canvas->encode('jpg', 82)->save($destPath);

            return is_file($destPath);
        } catch (\Throwable $e) {
            \Log::warning('Membership portrait compose failed: '.$e->getMessage());
            try {
                Image::make($sourcePath)
                    ->fit(800, 800)
                    ->encode('jpg', 82)
                    ->save($destPath);

                return is_file($destPath);
            } catch (\Throwable $e2) {
                return false;
            }
        }
    }
}
