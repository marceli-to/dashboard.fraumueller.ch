<?php

use App\Actions\Vote\Remove as RemoveAction;
use App\Models\Building;
use App\Models\Voter;
use App\Stores\UserStore;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->userStore = Mockery::mock(UserStore::class);
    $this->action = new RemoveAction($this->userStore);
});

it('removes vote when voter and vote exist', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    $voter = Voter::factory()->create(['hash' => 'test-hash']);
    $voter->buildings()->attach($building->id);
    
    $request = new Request([
        'slug' => 'test-building',
        'hash' => 'test-hash'
    ]);
    
    $this->userStore->shouldReceive('removeVote')->once()->with($building->id);
    
    expect($voter->buildings)->toHaveCount(1);
    
    $this->action->execute($request);
    
    $voter->refresh();
    expect($voter->buildings)->toHaveCount(0);
});

it('does nothing when voter does not exist', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    
    $request = new Request([
        'slug' => 'test-building',
        'hash' => 'non-existent-hash'
    ]);
    
    $this->userStore->shouldNotReceive('removeVote');
    
    $this->action->execute($request);
    
    // No exception should be thrown
    expect(true)->toBe(true);
});

it('does nothing when voter exists but has not voted for building', function () {
    $building1 = Building::factory()->create(['slug' => 'building-1']);
    $building2 = Building::factory()->create(['slug' => 'building-2']);
    $voter = Voter::factory()->create(['hash' => 'test-hash']);
    $voter->buildings()->attach($building1->id);
    
    $request = new Request([
        'slug' => 'building-2',
        'hash' => 'test-hash'
    ]);
    
    $this->userStore->shouldReceive('removeVote')->once()->with($building2->id);
    
    expect($voter->buildings)->toHaveCount(1);
    
    $this->action->execute($request);
    
    $voter->refresh();
    expect($voter->buildings)->toHaveCount(1); // Vote for building1 still exists
    expect($voter->buildings->first()->id)->toBe($building1->id);
});

it('removes only specified building vote when voter has multiple votes', function () {
    $building1 = Building::factory()->create(['slug' => 'building-1']);
    $building2 = Building::factory()->create(['slug' => 'building-2']);
    $voter = Voter::factory()->create(['hash' => 'test-hash']);
    $voter->buildings()->attach([$building1->id, $building2->id]);
    
    $request = new Request([
        'slug' => 'building-1',
        'hash' => 'test-hash'
    ]);
    
    $this->userStore->shouldReceive('removeVote')->once()->with($building1->id);
    
    expect($voter->buildings)->toHaveCount(2);
    
    $this->action->execute($request);
    
    $voter->refresh();
    expect($voter->buildings)->toHaveCount(1);
    expect($voter->buildings->first()->id)->toBe($building2->id);
});

it('throws exception when building does not exist', function () {
    $request = new Request([
        'slug' => 'non-existent-building',
        'hash' => 'test-hash'
    ]);
    
    expect(fn() => $this->action->execute($request))
        ->toThrow(ModelNotFoundException::class);
});

it('updates user store when removing vote', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    $voter = Voter::factory()->create(['hash' => 'test-hash']);
    $voter->buildings()->attach($building->id);
    
    $request = new Request([
        'slug' => 'test-building',
        'hash' => 'test-hash'
    ]);
    
    $this->userStore->shouldReceive('removeVote')->once()->with($building->id);
    
    $this->action->execute($request);
});

it('handles voter with no votes gracefully', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    $voter = Voter::factory()->create(['hash' => 'test-hash']);
    // Voter exists but has no votes
    
    $request = new Request([
        'slug' => 'test-building',
        'hash' => 'test-hash'
    ]);
    
    $this->userStore->shouldReceive('removeVote')->once()->with($building->id);
    
    expect($voter->buildings)->toHaveCount(0);
    
    $this->action->execute($request);
    
    $voter->refresh();
    expect($voter->buildings)->toHaveCount(0); // Still no votes
});