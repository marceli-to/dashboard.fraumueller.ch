<?php
namespace App\Actions\Vote;

use App\Models\Voter;
use App\Models\Building;
use Illuminate\Http\Request;
use App\Stores\UserStore;

class Remove
{
  public function __construct(protected UserStore $store) {}

  public function execute(Request $request): void
  {
    $building = Building::where('slug', $request->slug)->firstOrFail();
    $voter = Voter::where('hash', $request->hash)->first();

    if ($voter) {
      $voter->buildings()->detach($building->id);
      $this->store->removeVote($building->id); // session cleanup
    }
  }
}

