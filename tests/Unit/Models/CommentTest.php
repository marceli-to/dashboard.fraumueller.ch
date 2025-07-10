<?php

use App\Models\Building;
use App\Models\Comment;

it('can create a comment', function () {
    $building = Building::factory()->create();
    $comment = Comment::factory()->forBuilding($building)->create([
        'comment' => 'This is a test comment',
        'published' => true,
    ]);

    expect($comment->comment)->toBe('This is a test comment');
    expect($comment->published)->toBeTrue();
    expect($comment->building_id)->toBe($building->id);
    expect($comment)->toBeInstanceOf(Comment::class);
});

it('has fillable attributes', function () {
    $comment = new Comment();
    
    expect($comment->getFillable())->toContain('comment');
    expect($comment->getFillable())->toContain('published');
    expect($comment->getFillable())->toContain('building_id');
});

it('uses soft deletes', function () {
    $comment = Comment::factory()->create();
    $commentId = $comment->id;
    
    $comment->delete();
    
    expect(Comment::find($commentId))->toBeNull();
    expect(Comment::withTrashed()->find($commentId))->not->toBeNull();
    expect(Comment::withTrashed()->find($commentId)->trashed())->toBeTrue();
});

it('belongs to a building', function () {
    $building = Building::factory()->create();
    $comment = Comment::factory()->forBuilding($building)->create();
    
    expect($comment->building)->toBeInstanceOf(Building::class);
    expect($comment->building->id)->toBe($building->id);
});

it('has published scope', function () {
    $building = Building::factory()->create();
    Comment::factory()->published()->forBuilding($building)->create();
    Comment::factory()->draft()->forBuilding($building)->create();
    Comment::factory()->published()->forBuilding($building)->create();
    
    $publishedComments = Comment::published()->get();
    
    expect($publishedComments)->toHaveCount(2);
    expect($publishedComments->every(fn($comment) => $comment->published === true))->toBeTrue();
});

it('has drafts scope', function () {
    $building = Building::factory()->create();
    Comment::factory()->published()->forBuilding($building)->create();
    Comment::factory()->draft()->forBuilding($building)->create();
    Comment::factory()->draft()->forBuilding($building)->create();
    
    $draftComments = Comment::drafts()->get();
    
    expect($draftComments)->toHaveCount(2);
    expect($draftComments->every(fn($comment) => $comment->published === false))->toBeTrue();
});

it('can be restored after soft delete', function () {
    $comment = Comment::factory()->create();
    $commentId = $comment->id;
    
    $comment->delete();
    expect(Comment::find($commentId))->toBeNull();
    
    Comment::withTrashed()->find($commentId)->restore();
    expect(Comment::find($commentId))->not->toBeNull();
    expect(Comment::find($commentId)->trashed())->toBeFalse();
});

it('can toggle published status', function () {
    $comment = Comment::factory()->draft()->create();
    
    expect($comment->published)->toBeFalse();
    
    $comment->update(['published' => true]);
    
    expect($comment->fresh()->published)->toBeTrue();
});