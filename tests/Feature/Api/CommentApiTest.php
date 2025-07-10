<?php

use App\Models\Building;
use App\Models\Comment;

describe('POST /api/comment', function () {
    it('creates comment for building', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        
        $response = $this->postJson('/api/comment', [
            'slug' => 'test-building',
            'comment' => 'This is a test comment'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Comment stored']);
        
        expect(Comment::count())->toBe(1);
        
        $comment = Comment::first();
        expect($comment->building_id)->toBe($building->id);
        expect($comment->comment)->toBe('This is a test comment');
        expect($comment->published)->toBeFalse(); // Comments are unpublished by default
    });

    it('creates comment with correct building association', function () {
        $building1 = Building::factory()->create(['slug' => 'building-1']);
        $building2 = Building::factory()->create(['slug' => 'building-2']);
        
        $response = $this->postJson('/api/comment', [
            'slug' => 'building-1',
            'comment' => 'Comment for building 1'
        ]);
        
        $response->assertStatus(200);
        
        $comment = Comment::first();
        expect($comment->building_id)->toBe($building1->id);
        expect($comment->building_id)->not->toBe($building2->id);
    });

    it('validates required fields', function () {
        $response = $this->postJson('/api/comment', []);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['slug', 'comment']);
    });

    it('validates building exists', function () {
        $response = $this->postJson('/api/comment', [
            'slug' => 'non-existent-building',
            'comment' => 'This comment will fail'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['slug']);
    });

    it('validates comment is required', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        
        $response = $this->postJson('/api/comment', [
            'slug' => 'test-building',
            'comment' => ''
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['comment']);
    });

    it('validates comment max length', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        $longComment = str_repeat('a', 251); // 251 characters, exceeds max of 250
        
        $response = $this->postJson('/api/comment', [
            'slug' => 'test-building',
            'comment' => $longComment
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['comment']);
    });

    it('accepts comment at max length', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        $maxLengthComment = str_repeat('a', 250); // Exactly 250 characters
        
        $response = $this->postJson('/api/comment', [
            'slug' => 'test-building',
            'comment' => $maxLengthComment
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Comment stored']);
        
        $comment = Comment::first();
        expect($comment->comment)->toBe($maxLengthComment);
    });

    it('creates multiple comments for same building', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        
        $response1 = $this->postJson('/api/comment', [
            'slug' => 'test-building',
            'comment' => 'First comment'
        ]);
        
        $response2 = $this->postJson('/api/comment', [
            'slug' => 'test-building',
            'comment' => 'Second comment'
        ]);
        
        $response1->assertStatus(200);
        $response2->assertStatus(200);
        
        expect(Comment::count())->toBe(2);
        
        $comments = Comment::all();
        expect($comments->pluck('comment')->toArray())->toContain('First comment');
        expect($comments->pluck('comment')->toArray())->toContain('Second comment');
        expect($comments->every(fn($comment) => $comment->building_id === $building->id))->toBeTrue();
    });

    it('creates comments for different buildings', function () {
        $building1 = Building::factory()->create(['slug' => 'building-1']);
        $building2 = Building::factory()->create(['slug' => 'building-2']);
        
        $response1 = $this->postJson('/api/comment', [
            'slug' => 'building-1',
            'comment' => 'Comment for building 1'
        ]);
        
        $response2 = $this->postJson('/api/comment', [
            'slug' => 'building-2',
            'comment' => 'Comment for building 2'
        ]);
        
        $response1->assertStatus(200);
        $response2->assertStatus(200);
        
        expect(Comment::count())->toBe(2);
        
        $comments = Comment::all();
        expect($comments->where('building_id', $building1->id)->count())->toBe(1);
        expect($comments->where('building_id', $building2->id)->count())->toBe(1);
    });

    it('handles special characters in comment', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        $specialComment = 'Comment with ñáéíóú and 🏢 emoji';
        
        $response = $this->postJson('/api/comment', [
            'slug' => 'test-building',
            'comment' => $specialComment
        ]);
        
        $response->assertStatus(200);
        
        $comment = Comment::first();
        expect($comment->comment)->toBe($specialComment);
    });

    it('handles HTML content in comment', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        $htmlComment = 'Comment with <strong>HTML</strong> tags';
        
        $response = $this->postJson('/api/comment', [
            'slug' => 'test-building',
            'comment' => $htmlComment
        ]);
        
        $response->assertStatus(200);
        
        $comment = Comment::first();
        expect($comment->comment)->toBe($htmlComment);
    });

    it('returns correct JSON response structure', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        
        $response = $this->postJson('/api/comment', [
            'slug' => 'test-building',
            'comment' => 'Test comment'
        ]);
        
        $response->assertStatus(200);
        $response->assertExactJson(['message' => 'Comment stored']);
    });

    it('sets correct content type', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        
        $response = $this->postJson('/api/comment', [
            'slug' => 'test-building',
            'comment' => 'Test comment'
        ]);
        
        $response->assertHeader('Content-Type', 'application/json');
    });
});