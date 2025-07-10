<?php

use App\Actions\Vote\Check as CheckAction;
use App\Models\Building;
use App\Models\Voter;
use App\Stores\UserStore;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->userStore = Mockery::mock(UserStore::class);
    $this->action = new CheckAction($this->userStore);
});

it('returns false when voter does not exist', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    
    $request = new Request([
        'slug' => 'test-building',
        'hash' => 'non-existent-hash'
    ]);
    
    $result = $this->action->execute($request);
    
    expect($result)->toBe(['has_vote' => false]);
});

it('returns false when voter exists but has not voted for building', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    $voter = Voter::factory()->create(['hash' => 'test-hash']);
    
    $request = new Request([
        'slug' => 'test-building',
        'hash' => 'test-hash'
    ]);
    
    $result = $this->action->execute($request);
    
    expect($result)->toBe(['has_vote' => false]);
});

it('returns true when voter has voted for building', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    $voter = Voter::factory()->create(['hash' => 'test-hash']);
    $voter->buildings()->attach($building->id);
    
    $this->userStore->shouldReceive('addVote')->once()->with($building->id);
    
    $request = new Request([
        'slug' => 'test-building',
        'hash' => 'test-hash'
    ]);
    
    $result = $this->action->execute($request);
    
    expect($result)->toBe(['has_vote' => true]);
});

it('adds vote to user store when vote exists', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    $voter = Voter::factory()->create(['hash' => 'test-hash']);
    $voter->buildings()->attach($building->id);
    
    $this->userStore->shouldReceive('addVote')->once()->with($building->id);
    
    $request = new Request([
        'slug' => 'test-building',
        'hash' => 'test-hash'
    ]);
    
    $this->action->execute($request);
});

it('throws exception when building does not exist', function () {
    $request = new Request([
        'slug' => 'non-existent-building',
        'hash' => 'test-hash'
    ]);
    
    expect(fn() => $this->action->execute($request))
        ->toThrow(ModelNotFoundException::class);
});

it('handles multiple buildings for same voter', function () {
    $building1 = Building::factory()->create(['slug' => 'building-1']);
    $building2 = Building::factory()->create(['slug' => 'building-2']);
    $voter = Voter::factory()->create(['hash' => 'test-hash']);
    
    // Voter voted for building1 but not building2
    $voter->buildings()->attach($building1->id);
    
    $this->userStore->shouldReceive('addVote')->once()->with($building1->id);
    
    $request1 = new Request(['slug' => 'building-1', 'hash' => 'test-hash']);
    $request2 = new Request(['slug' => 'building-2', 'hash' => 'test-hash']);
    
    $result1 = $this->action->execute($request1);
    $result2 = $this->action->execute($request2);
    
    expect($result1)->toBe(['has_vote' => true]);
    expect($result2)->toBe(['has_vote' => false]);
});