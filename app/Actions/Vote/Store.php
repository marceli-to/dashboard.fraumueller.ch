<?php
namespace App\Actions\Vote;
use App\Models\Voter;
use App\Models\Building;
use Illuminate\Http\Request;
use App\Stores\UserStore;

class Store
{
  public function __construct(protected UserStore $store) {}
  
  public function execute(Request $request): void
  {
    $building = Building::where('slug', $request->slug)->firstOrFail();

    $voter = Voter::firstOrCreate(
      ['hash' => $request->hash],
      ['ip_address' => md5($request->ip())]
    );

    // Only attach if vote doesn't already exist
    if (!$voter->buildings()->where('building_id', $building->id)->exists()) {
      $voter->buildings()->attach($building->id, ['voted_at' => now()]);
    }

    // Update session store
    $this->store->addVote($building->id);
  }
  
}
