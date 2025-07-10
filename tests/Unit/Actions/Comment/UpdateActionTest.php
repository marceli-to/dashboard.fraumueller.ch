<?php

use App\Actions\Comment\Update as UpdateAction;
use App\Models\Comment;
use App\Models\Building;

beforeEach(function () {
    $this->action = new UpdateAction();
});

it('updates comment text', function () {
    $building = Building::factory()->create();
    $comment = Comment::factory()->forBuilding($building)->create([
        'comment' => 'Original comment text'
    ]);
    
    $updatedComment = $this->action->execute($comment, 'Updated comment text');
    
    expect($updatedComment->comment)->toBe('Updated comment text');
    expect($updatedComment->id)->toBe($comment->id);
});

it('returns the updated comment instance', function () {
    $building = Building::factory()->create();
    $comment = Comment::factory()->forBuilding($building)->create();
    
    $result = $this->action->execute($comment, 'New text');
    
    expect($result)->toBeInstanceOf(Comment::class);
    expect($result->id)->toBe($comment->id);
});

it('persists comment update to database', function () {
    $building = Building::factory()->create();
    $comment = Comment::factory()->forBuilding($building)->create([
        'comment' => 'Original text'
    ]);
    
    $this->action->execute($comment, 'Persisted text');
    
    $comment->refresh();
    expect($comment->comment)->toBe('Persisted text');
});

it('does not change other comment attributes', function () {
    $building = Building::factory()->create();
    $comment = Comment::factory()->forBuilding($building)->create([
        'comment' => 'Original text',
        'published' => true
    ]);
    
    $originalPublished = $comment->published;
    $originalBuildingId = $comment->building_id;
    $originalCreatedAt = $comment->created_at;
    
    $this->action->execute($comment, 'New text');
    
    $comment->refresh();
    expect($comment->published)->toBe($originalPublished);
    expect($comment->building_id)->toBe($originalBuildingId);
    expect($comment->created_at->toDateTimeString())->toBe($originalCreatedAt->toDateTimeString());
});

it('handles empty comment text', function () {
    $building = Building::factory()->create();
    $comment = Comment::factory()->forBuilding($building)->create([
        'comment' => 'Original text'
    ]);
    
    $this->action->execute($comment, '');
    
    $comment->refresh();
    expect($comment->comment)->toBe('');
});

it('handles long comment text', function () {
    $building = Building::factory()->create();
    $comment = Comment::factory()->forBuilding($building)->create();
    
    $longText = str_repeat('Very long comment text. ', 200);
    
    $this->action->execute($comment, $longText);
    
    $comment->refresh();
    expect($comment->comment)->toBe($longText);
});

it('handles special characters in comment text', function () {
    $building = Building::factory()->create();
    $comment = Comment::factory()->forBuilding($building)->create();
    
    $specialText = 'Comment with special chars: ñáéíóú ©®™ <script>alert("test")</script>';
    
    $this->action->execute($comment, $specialText);
    
    $comment->refresh();
    expect($comment->comment)->toBe($specialText);
});

it('updates comment text multiple times', function () {
    $building = Building::factory()->create();
    $comment = Comment::factory()->forBuilding($building)->create([
        'comment' => 'Original'
    ]);
    
    $this->action->execute($comment, 'First update');
    $comment->refresh();
    expect($comment->comment)->toBe('First update');
    
    $this->action->execute($comment, 'Second update');
    $comment->refresh();
    expect($comment->comment)->toBe('Second update');
    
    $this->action->execute($comment, 'Final update');
    $comment->refresh();
    expect($comment->comment)->toBe('Final update');
});