<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actions\Vote\Get as GetVotesAction;
use App\Http\Resources\VoteResource;

class VoteController extends Controller
{
  public function get()
  {
    return response()->json(
      VoteResource::collection(
        (new GetVotesAction())->execute()
      )
    );
  }
}
