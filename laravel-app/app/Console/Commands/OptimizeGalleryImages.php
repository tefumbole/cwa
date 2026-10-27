<?php

namespace App\Console\Commands;

use App\GalleryItem;
use App\Support\GalleryImage;
use Illuminate\Console\Command;

class OptimizeGalleryImages extends Command
{
    protected $signature = 'gallery:optimize';

    protected $description = 'Shrink gallery photos already on disk so the public page loads faster';

    public function handle()
    {
        @ini_set('memory_limit', '256M');
        $dir = public_path('images/gallery');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $items = GalleryItem::query()->where('type', 'image')->whereNotNull('file_path')->get();
        $done = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($items as $item) {
            $full = public_path($item->file_path);
            $thumb = $item->thumb_path ? public_path($item->thumb_path) : null;
            if (GalleryImage::alreadyFitted($full, $thumb)) {
                $skipped++;
                continue;
            }
            if (! is_file($full)) {
                $this->warn('Missing file for gallery item '.$item->id);
                $failed++;
                continue;
            }

            $oldThumb = $item->thumb_path ? public_path($item->thumb_path) : null;
            $pair = GalleryImage::optimizeExisting($full, $dir);
            if (! $pair) {
                $this->warn('Could not shrink gallery item '.$item->id);
                $failed++;
                continue;
            }
            if ($oldThumb && is_file($oldThumb) && realpath($oldThumb) !== realpath(public_path($pair['thumb']))) {
                @unlink($oldThumb);
            }

            $item->file_path = $pair['file'];
            $item->thumb_path = $pair['thumb'];
            $item->save();
            $done++;
            $this->line('Shrunk '.$item->id);
        }

        $this->info('Shrunk '.$done.', already fitted '.$skipped.', failed '.$failed.'.');

        return 0;
    }
}
