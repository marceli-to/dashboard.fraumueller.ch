<?php

use App\Models\Building;
use App\Models\Voter;

describe('POST /api/voter/check', function () {
    it('returns false when voter does not exist', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        
        $response = $this->postJson('/api/voter/check', [
            'slug' => 'test-building',
            'hash' => 'non-existent-hash-123456789012'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['has_vote' => false]);
    });

    it('returns false when voter exists but has not voted', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        $voter = Voter::factory()->create(['hash' => 'existing-hash-123456789012345']);
        
        $response = $this->postJson('/api/voter/check', [
            'slug' => 'test-building',
            'hash' => 'existing-hash-123456789012345'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['has_vote' => false]);
    });

    it('returns true when voter has voted for building', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        $voter = Voter::factory()->create(['hash' => 'voted-hash-1234567890123456']);
        $voter->buildings()->attach($building->id);
        
        $response = $this->postJson('/api/voter/check', [
            'slug' => 'test-building',
            'hash' => 'voted-hash-1234567890123456'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['has_vote' => true]);
    });

    it('validates required fields', function () {
        $response = $this->postJson('/api/voter/check', []);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['hash', 'slug']);
    });

    it('validates hash format', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        
        $response = $this->postJson('/api/voter/check', [
            'slug' => 'test-building',
            'hash' => 'too-short' // Less than 32 characters
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['hash']);
    });

    it('validates building exists', function () {
        $response = $this->postJson('/api/voter/check', [
            'slug' => 'non-existent-building',
            'hash' => 'valid-hash-123456789012345678'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['slug']);
    });
});

describe('POST /api/vote', function () {
    it('creates vote for new voter', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        
        $response = $this->postJson('/api/vote', [
            'slug' => 'test-building',
            'hash' => 'new-voter-hash-123456789012345'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Vote stored']);
        
        expect(Voter::count())->toBe(1);
        $voter = Voter::first();
        expect($voter->hash)->toBe('new-voter-hash-123456789012345');
        expect($voter->buildings)->toHaveCount(1);
        expect($voter->buildings->first()->id)->toBe($building->id);
    });

    it('adds vote for existing voter', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        $voter = Voter::factory()->create(['hash' => 'existing-voter-hash-1234567890']);
        
        $response = $this->postJson('/api/vote', [
            'slug' => 'test-building',
            'hash' => 'existing-voter-hash-1234567890'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Vote stored']);
        
        expect(Voter::count())->toBe(1); // Still only one voter
        $voter->refresh();
        expect($voter->buildings)->toHaveCount(1);
        expect($voter->buildings->first()->id)->toBe($building->id);
    });

    it('does not create duplicate vote', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        $voter = Voter::factory()->create(['hash' => 'duplicate-test-hash-12345678901']);
        $voter->buildings()->attach($building->id);
        
        $response = $this->postJson('/api/vote', [
            'slug' => 'test-building',
            'hash' => 'duplicate-test-hash-12345678901'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Vote stored']);
        
        $voter->refresh();
        expect($voter->buildings)->toHaveCount(1); // Still only one vote
    });

    it('allows multiple votes for different buildings', function () {
        $building1 = Building::factory()->create(['slug' => 'building-1']);
        $building2 = Building::factory()->create(['slug' => 'building-2']);
        $hash = 'multi-vote-hash-123456789012345';
        
        $response1 = $this->postJson('/api/vote', [
            'slug' => 'building-1',
            'hash' => $hash
        ]);
        
        $response2 = $this->postJson('/api/vote', [
            'slug' => 'building-2',
            'hash' => $hash
        ]);
        
        $response1->assertStatus(200);
        $response2->assertStatus(200);
        
        $voter = Voter::where('hash', $hash)->first();
        expect($voter->buildings)->toHaveCount(2);
    });

    it('validates request data', function () {
        $response = $this->postJson('/api/vote', []);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['hash', 'slug']);
    });
});

describe('PUT /api/vote', function () {
    it('removes vote when voter and vote exist', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        $voter = Voter::factory()->create(['hash' => 'remove-vote-hash-123456789012']);
        $voter->buildings()->attach($building->id);
        
        $response = $this->putJson('/api/vote', [
            'slug' => 'test-building',
            'hash' => 'remove-vote-hash-123456789012'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Vote removed']);
        
        $voter->refresh();
        expect($voter->buildings)->toHaveCount(0);
    });

    it('handles non-existent voter gracefully', function () {
        $building = Building::factory()->create(['slug' => 'test-building']);
        
        $response = $this->putJson('/api/vote', [
            'slug' => 'test-building',
            'hash' => 'non-existent-hash-123456789012'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Vote removed']);
    });

    it('removes only specified building vote', function () {
        $building1 = Building::factory()->create(['slug' => 'building-1']);
        $building2 = Building::factory()->create(['slug' => 'building-2']);
        $voter = Voter::factory()->create(['hash' => 'selective-remove-hash-123456789']);
        $voter->buildings()->attach([$building1->id, $building2->id]);
        
        $response = $this->putJson('/api/vote', [
            'slug' => 'building-1',
            'hash' => 'selective-remove-hash-123456789'
        ]);
        
        $response->assertStatus(200);
        
        $voter->refresh();
        expect($voter->buildings)->toHaveCount(1);
        expect($voter->buildings->first()->id)->toBe($building2->id);
    });

    it('validates request data', function () {
        $response = $this->putJson('/api/vote', []);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['hash', 'slug']);
    });
});