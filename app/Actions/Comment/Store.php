<?php
namespace App\Actions\Comment;
use App\Models\Building;
use App\Models\Comment;
use Illuminate\Http\Request;

class Store
{
  public function execute(Request $request): void
  {
    $building = Building::where('slug', $request->slug)->firstOrFail();
    Comment::create([
      'building_id' => $building->id,
      'comment' => $request->comment,
    ]);
  }
}
