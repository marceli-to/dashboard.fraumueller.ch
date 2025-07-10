<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Building;
use Illuminate\Support\Facades\File;

class CreateShape extends Command
{
    protected $signature = 'shape:create';
    protected $description = 'Create a new shape';

    public function handle()
    {
        // get all buildings
        $buildings = Building::all();

        foreach ($buildings as $building) {
            $slug = $building->slug;
            $directory = resource_path("views/components/buildings/teaser/shapes");
            $filePath = $directory . "/{$slug}.blade.php";

            // Ensure the directory exists
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->info("Created directory: $directory");
            }

            // Create the Blade file if it doesn't exist
            if (!File::exists($filePath)) {
                File::put($filePath, '');
                $this->info("Created template: {$filePath}");
            } else {
                $this->warn("Shape already exists: {$filePath}");
            }
        }

        $this->info('All shapes processed.');
    }
}
