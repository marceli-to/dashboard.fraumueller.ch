<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Building;

class AssignBuildingCoordinates extends Command
{
    protected $signature = 'buildings:assign-coordinates';
    protected $description = 'Assign test coordinates to all Building records';

    public function handle()
    {
        $coordinates = [
            [47.3599, 8.535228],
            [47.388774, 8.530187],
            [47.3709186715214, 8.54881720630014],
            [47.379898, 8.489183],
            [47.36809, 8.50596],
            [47.372619, 8.538509],
            [47.396267, 8.528412],
            [47.397492, 8.533295],
            [47.378231, 8.493786],
            [47.387051, 8.474826],
            [47.410097, 8.546757],
            [47.38754, 8.525003],
            [47.39263, 8.491294],
            [47.39846, 8.593506],
            [47.412927, 8.547206],
            [47.408506, 8.530637]
        ];

        $buildings = Building::all();

        if ($buildings->count() !== count($coordinates)) {
            $this->error("The number of buildings does not match the number of coordinate pairs.");
            return 1;
        }

        foreach ($buildings as $index => $building) {
            $building->lat = $coordinates[$index][0];
            $building->long = $coordinates[$index][1];
            $building->save();
        }

        $this->info("Coordinates successfully assigned to all buildings.");
        return 0;
    }
}
