<?php
namespace App\Actions\Comment;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

class Get
{
  /**
   * Get all comments. Split the comments into 3 groups: 
   * - published comments
   * - not published comments (drafts)
   * - deleted comments
   *
   * @return array
   */
  public function execute(): array
  {
    $publishedComments = Comment::published()->with('building')->orderBy('created_at', 'desc')->get();
    $notPublishedComments = Comment::drafts()->with('building')->orderBy('created_at', 'desc')->get();
    $deletedComments = Comment::onlyTrashed()->with('building')->orderBy('deleted_at', 'desc')->get();

    return [
      'published' => $publishedComments,
      'drafts' => $notPublishedComments,
      'deleted' => $deletedComments
    ];
  }
}

