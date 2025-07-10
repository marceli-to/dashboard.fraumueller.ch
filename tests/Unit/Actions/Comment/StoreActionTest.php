<?php

use App\Actions\Comment\Store as StoreAction;
use App\Models\Building;
use App\Models\Comment;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->action = new StoreAction();
});

it('creates comment for building', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    
    $request = new Request([
        'slug' => 'test-building',
        'comment' => 'This is a test comment'
    ]);
    
    expect(Comment::count())->toBe(0);
    
    $this->action->execute($request);
    
    expect(Comment::count())->toBe(1);
    
    $comment = Comment::first();
    expect($comment->building_id)->toBe($building->id);
    expect($comment->comment)->toBe('This is a test comment');
    expect($comment->published)->toBeFalse(); // Comments are unpublished by default
});

it('creates comment with correct building association', function () {
    $building1 = Building::factory()->create(['slug' => 'building-1']);
    $building2 = Building::factory()->create(['slug' => 'building-2']);
    
    $request = new Request([
        'slug' => 'building-1',
        'comment' => 'Comment for building 1'
    ]);
    
    $this->action->execute($request);
    
    $comment = Comment::first();
    expect($comment->building_id)->toBe($building1->id);
    expect($comment->building_id)->not->toBe($building2->id);
});

it('throws exception when building does not exist', function () {
    $request = new Request([
        'slug' => 'non-existent-building',
        'comment' => 'This comment will fail'
    ]);
    
    expect(fn() => $this->action->execute($request))
        ->toThrow(ModelNotFoundException::class);
});

it('creates multiple comments for same building', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    
    $request1 = new Request([
        'slug' => 'test-building',
        'comment' => 'First comment'
    ]);
    
    $request2 = new Request([
        'slug' => 'test-building',
        'comment' => 'Second comment'
    ]);
    
    $this->action->execute($request1);
    $this->action->execute($request2);
    
    expect(Comment::count())->toBe(2);
    
    $comments = Comment::all();
    expect($comments->pluck('comment')->toArray())->toContain('First comment');
    expect($comments->pluck('comment')->toArray())->toContain('Second comment');
    expect($comments->every(fn($comment) => $comment->building_id === $building->id))->toBeTrue();
});

it('creates comments for different buildings', function () {
    $building1 = Building::factory()->create(['slug' => 'building-1']);
    $building2 = Building::factory()->create(['slug' => 'building-2']);
    
    $request1 = new Request([
        'slug' => 'building-1',
        'comment' => 'Comment for building 1'
    ]);
    
    $request2 = new Request([
        'slug' => 'building-2',
        'comment' => 'Comment for building 2'
    ]);
    
    $this->action->execute($request1);
    $this->action->execute($request2);
    
    expect(Comment::count())->toBe(2);
    
    $comments = Comment::all();
    expect($comments->where('building_id', $building1->id)->count())->toBe(1);
    expect($comments->where('building_id', $building2->id)->count())->toBe(1);
});

it('handles empty comment text', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    
    $request = new Request([
        'slug' => 'test-building',
        'comment' => ''
    ]);
    
    $this->action->execute($request);
    
    $comment = Comment::first();
    expect($comment->comment)->toBe('');
});

it('handles long comment text', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    $longComment = str_repeat('This is a very long comment. ', 100);
    
    $request = new Request([
        'slug' => 'test-building',
        'comment' => $longComment
    ]);
    
    $this->action->execute($request);
    
    $comment = Comment::first();
    expect($comment->comment)->toBe($longComment);
});