<?php

use App\Models\Building;
use App\Models\User;
use App\Models\Voter;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('GET /api/votes', function () {
    it('returns votes data', function () {
        $building = Building::factory()->create();
        $voter = Voter::factory()->create();
        $voter->buildings()->attach($building->id);
        
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/votes');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'ip_address', 'hash', 'buildings']
        ]);
    });

    it('returns empty array when no votes exist', function () {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/votes');
        
        $response->assertStatus(200);
        $response->assertJson([]);
    });

    it('includes building relationships in vote data', function () {
        $building1 = Building::factory()->create(['title' => 'Building 1']);
        $building2 = Building::factory()->create(['title' => 'Building 2']);
        $voter = Voter::factory()->create();
        $voter->buildings()->attach([$building1->id, $building2->id]);
        
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/votes');
        
        $response->assertStatus(200);
        
        $data = $response->json();
        expect(count($data))->toBe(1);
        expect(count($data[0]['buildings']))->toBe(2);
    });

    it('returns correct voter information', function () {
        $building = Building::factory()->create();
        $voter = Voter::factory()->create([
            'ip_address' => '192.168.1.1',
            'hash' => 'test-hash-123456789012345678901234'
        ]);
        $voter->buildings()->attach($building->id);
        
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/votes');
        
        $response->assertStatus(200);
        
        $data = $response->json();
        expect($data[0]['ip_address'])->toBe('192.168.1.1');
        expect($data[0]['hash'])->toBe('test-hash-123456789012345678901234');
    });

    it('handles multiple voters with different vote counts', function () {
        $building1 = Building::factory()->create();
        $building2 = Building::factory()->create();
        
        $voter1 = Voter::factory()->create();
        $voter1->buildings()->attach($building1->id);
        
        $voter2 = Voter::factory()->create();
        $voter2->buildings()->attach([$building1->id, $building2->id]);
        
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/votes');
        
        $response->assertStatus(200);
        
        $data = $response->json();
        expect(count($data))->toBe(2);
        
        // Find voters in response
        $voter1Data = collect($data)->firstWhere('id', $voter1->id);
        $voter2Data = collect($data)->firstWhere('id', $voter2->id);
        
        expect(count($voter1Data['buildings']))->toBe(1);
        expect(count($voter2Data['buildings']))->toBe(2);
    });

    it('excludes voters with no votes', function () {
        $building = Building::factory()->create();
        $voterWithVote = Voter::factory()->create();
        $voterWithoutVote = Voter::factory()->create();
        
        $voterWithVote->buildings()->attach($building->id);
        // voterWithoutVote has no votes
        
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/votes');
        
        $response->assertStatus(200);
        
        $data = $response->json();
        expect(count($data))->toBe(1);
        expect($data[0]['id'])->toBe($voterWithVote->id);
    });

    it('returns JSON content type', function () {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/votes');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/votes');
        
        $response->assertStatus(401);
    });
});