<?php
namespace App\Actions\Comment;
use App\Models\Comment;

class Update
{
  public function execute(Comment $comment, $commentText)
  {
    $comment->update([
      'comment' => $commentText
    ]);
    return $comment;
  }
}
