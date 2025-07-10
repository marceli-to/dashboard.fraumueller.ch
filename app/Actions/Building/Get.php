<?php
namespace App\Actions\Building;
use App\Models\Building;

class Get
{
  public function execute()
  {
    return Building::all();
  }
}

