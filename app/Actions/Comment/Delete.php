<?php
namespace App\Actions\Comment;
use App\Models\Comment;

class Delete
{
  public function execute(Comment $comment): Comment
  {
    $comment->published = false;
    $comment->save();
    $comment->delete();
    return $comment;
  }
}
