<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Building;

class UpdateImageSizes extends Command
{
    protected $signature = 'images:update';

    protected $description = 'Update image sizes for building slides';

    public function handle()
    {
        $buildings = \App\Models\Building::all();

        foreach ($buildings as $building) {
            $folderPath = public_path("media/{$building->slug}");

            if (!File::exists($folderPath)) {
                $this->warn("Folder not found: $folderPath");
                continue;
            }

            // Print the slug before processing images
            $this->line("Slug: {$building->slug}");

            for ($i = 1; $i <= 5; $i++) {
                $imagePath = "{$folderPath}/{$building->slug}-{$i}.jpg";

                if (File::exists($imagePath)) {
                    [$width, $height] = getimagesize($imagePath);

                    $this->line(
                        "<x-slideshow.slide number=\"{$i}\" slug=\"{{ \$building->slug }}\" alt=\"{{ \$building->title }}\" width=\"{$width}\" height=\"{$height}\" />"
                    );
                }
            }
        }

        $this->info('Image size processing completed.');
    }
}
