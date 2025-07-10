<?php
namespace App\Actions\Comment;

use App\Models\Comment;

class Toggle
{
  public function execute(Comment $comment): Comment
  {
    $comment->published = !$comment->published;
    $comment->save();
    return $comment;
  }
}
