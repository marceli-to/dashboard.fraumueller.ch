<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
  public static $wrap = null;

  public function toArray(Request $request): array
  {
    return [
      'uuid' => $this->uuid,
      'firstname' => $this->firstname,
      'name' => $this->name,
      'email' => $this->email,
      'roles' => $this->getRoleNames(),
    ];
  }
}