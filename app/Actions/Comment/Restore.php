<?php
namespace App\Actions\Comment;
use App\Models\Comment;

class Restore
{
  public function execute($id)
  {
    $comment = Comment::withTrashed()->find($id)->restore();
    return Comment::find($id);
  }
}
