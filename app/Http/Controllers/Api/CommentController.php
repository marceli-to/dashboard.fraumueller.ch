<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actions\Comment\Get as GetCommentsAction;
use App\Actions\Comment\Toggle as ToggleCommentAction;
use App\Actions\Comment\Delete as DeleteCommentAction;
use App\Actions\Comment\Restore as RestoreCommentAction;
use App\Actions\Comment\Update as UpdateCommentAction;
use App\Http\Resources\CommentResource;
use App\Models\Comment;

class CommentController extends Controller
{
  public function get()
  {
    $comments = (new GetCommentsAction())->execute();
    return response()->json([
      'published' => CommentResource::collection($comments['published']),
      'drafts' => CommentResource::collection($comments['drafts']),
      'deleted' => CommentResource::collection($comments['deleted']),
    ]);
  }

  public function update(Comment $comment, Request $request)
  {
    $updatedComment = (new UpdateCommentAction())->execute($comment, $request->comment);
    return response()->json(new CommentResource($updatedComment));
  }

  public function toggle(Comment $comment)
  {
    $updatedComment = (new ToggleCommentAction())->execute($comment);
    return response()->json(new CommentResource($updatedComment));
  }

  public function destroy(Comment $comment)
  {
    $deletedComment = (new DeleteCommentAction())->execute($comment);
    return response()->json(new CommentResource($deletedComment));
  }

  public function restore(Request $request)
  {
    $restoredComment = (new RestoreCommentAction())->execute($request->id);
    return response()->json(new CommentResource($restoredComment));
  }
}
