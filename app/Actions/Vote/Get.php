<?php
namespace App\Actions\Vote;
use App\Models\Building;
use Illuminate\Database\Eloquent\Collection;

class Get
{
  /**
   * Get all buildings sorted by vote count (descending)
   *
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function execute(): Collection
  {
    return Building::withCount('voters')->orderByDesc('voters_count')->get();
  }
}
