<?php

use App\Models\Building;
use App\Models\Comment;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('GET /api/comments', function () {
    it('returns comments grouped by status', function () {
        $building = Building::factory()->create();
        
        $publishedComment = Comment::factory()->published()->forBuilding($building)->create();
        $draftComment = Comment::factory()->draft()->forBuilding($building)->create();
        $deletedComment = Comment::factory()->forBuilding($building)->create();
        $deletedComment->delete();
        
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/comments');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'published' => [
                '*' => ['id', 'comment', 'published', 'building_id']
            ],
            'drafts' => [
                '*' => ['id', 'comment', 'published', 'building_id']
            ],
            'deleted' => [
                '*' => ['id', 'comment', 'published', 'building_id']
            ]
        ]);
        
        $data = $response->json();
        expect(count($data['published']))->toBe(1);
        expect(count($data['drafts']))->toBe(1);
        expect(count($data['deleted']))->toBe(1);
    });

    it('returns empty arrays when no comments exist', function () {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/comments');
        
        $response->assertStatus(200);
        $response->assertJson([
            'published' => [],
            'drafts' => [],
            'deleted' => []
        ]);
    });
});

describe('PUT /api/comments/update/{comment}', function () {
    it('updates comment text', function () {
        $building = Building::factory()->create();
        $comment = Comment::factory()->forBuilding($building)->create([
            'comment' => 'Original comment'
        ]);
        
        $response = $this->actingAs($this->user, 'sanctum')->putJson("/api/comments/update/{$comment->id}", [
            'comment' => 'Updated comment text'
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['comment' => 'Updated comment text']);
        
        $comment->refresh();
        expect($comment->comment)->toBe('Updated comment text');
    });

    it('returns 404 for non-existent comment', function () {
        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/comments/update/999', [
            'comment' => 'Updated text'
        ]);
        
        $response->assertStatus(404);
    });

    it('validates comment parameter', function () {
        $building = Building::factory()->create();
        $comment = Comment::factory()->forBuilding($building)->create();
        
        $response = $this->actingAs($this->user, 'sanctum')->putJson("/api/comments/update/{$comment->id}", []);
        
        $response->assertStatus(422);
    });
});

describe('PUT /api/comments/toggle/{comment}', function () {
    it('toggles comment published status', function () {
        $building = Building::factory()->create();
        $comment = Comment::factory()->draft()->forBuilding($building)->create();
        
        expect($comment->published)->toBeFalse();
        
        $response = $this->actingAs($this->user, 'sanctum')->putJson("/api/comments/toggle/{$comment->id}");
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['published' => true]);
        
        $comment->refresh();
        expect($comment->published)->toBeTrue();
    });

    it('returns 404 for non-existent comment', function () {
        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/comments/toggle/999');
        
        $response->assertStatus(404);
    });
});

describe('DELETE /api/comments/{comment}', function () {
    it('soft deletes comment', function () {
        $building = Building::factory()->create();
        $comment = Comment::factory()->forBuilding($building)->create();
        
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/comments/{$comment->id}");
        
        $response->assertStatus(200);
        
        expect(Comment::find($comment->id))->toBeNull();
        expect(Comment::withTrashed()->find($comment->id))->not->toBeNull();
        expect(Comment::withTrashed()->find($comment->id)->trashed())->toBeTrue();
    });

    it('returns 404 for non-existent comment', function () {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/comments/999');
        
        $response->assertStatus(404);
    });
});

describe('PUT /api/comments/restore/{id}', function () {
    it('restores soft deleted comment', function () {
        $building = Building::factory()->create();
        $comment = Comment::factory()->forBuilding($building)->create();
        $comment->delete();
        
        expect(Comment::find($comment->id))->toBeNull();
        
        $response = $this->actingAs($this->user, 'sanctum')->putJson("/api/comments/restore/{$comment->id}", [
            'id' => $comment->id
        ]);
        
        $response->assertStatus(200);
        
        expect(Comment::find($comment->id))->not->toBeNull();
        expect(Comment::find($comment->id)->trashed())->toBeFalse();
    });

    it('handles non-existent comment gracefully', function () {
        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/comments/restore/999', [
            'id' => 999
        ]);
        
        $response->assertStatus(422);
    });

    it('requires id parameter', function () {
        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/comments/restore/1', []);
        
        $response->assertStatus(422);
    });
});