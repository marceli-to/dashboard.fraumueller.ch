<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoteResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'title' => $this->title,
      'votes' => $this->voters_count,
    ];
  }
}
