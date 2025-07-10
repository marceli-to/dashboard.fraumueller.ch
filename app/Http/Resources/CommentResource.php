<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'date' => $this->created_at->toIso8601String(),
      'comment' => $this->comment,
      'building' => $this->building->title,
      'published' => $this->published,
      'deleted_at' => $this->deleted_at,
    ];
  }
}
