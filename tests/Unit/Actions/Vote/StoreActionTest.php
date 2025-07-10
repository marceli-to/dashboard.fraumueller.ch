<?php

use App\Actions\Vote\Store as StoreAction;
use App\Models\Building;
use App\Models\Voter;
use App\Stores\UserStore;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->userStore = Mockery::mock(UserStore::class);
    $this->action = new StoreAction($this->userStore);
});

it('creates new voter and vote when voter does not exist', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    
    $request = new Request([
        'slug' => 'test-building',
        'hash' => 'new-hash'
    ]);
    $request->setUserResolver(function () {
        return null;
    });
    
    // Mock the IP method
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('__get')->with('slug')->andReturn('test-building');
    $request->shouldReceive('__get')->with('hash')->andReturn('new-hash');
    $request->shouldReceive('ip')->andReturn('192.168.1.1');
    
    $this->userStore->shouldReceive('addVote')->once()->with($building->id);
    
    expect(Voter::count())->toBe(0);
    
    $this->action->execute($request);
    
    expect(Voter::count())->toBe(1);
    
    $voter = Voter::first();
    expect($voter->hash)->toBe('new-hash');
    expect($voter->ip_address)->toBe(md5('192.168.1.1'));
    expect($voter->buildings)->toHaveCount(1);
    expect($voter->buildings->first()->id)->toBe($building->id);
});

it('uses existing voter when hash exists', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    $existingVoter = Voter::factory()->create(['hash' => 'existing-hash']);
    
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('__get')->with('slug')->andReturn('test-building');
    $request->shouldReceive('__get')->with('hash')->andReturn('existing-hash');
    $request->shouldReceive('ip')->andReturn('192.168.1.1');
    
    $this->userStore->shouldReceive('addVote')->once()->with($building->id);
    
    expect(Voter::count())->toBe(1);
    
    $this->action->execute($request);
    
    expect(Voter::count())->toBe(1); // Still only one voter
    
    $voter = Voter::first();
    expect($voter->id)->toBe($existingVoter->id);
    expect($voter->buildings)->toHaveCount(1);
    expect($voter->buildings->first()->id)->toBe($building->id);
});

it('does not create duplicate vote for same building', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    $voter = Voter::factory()->create(['hash' => 'test-hash']);
    $voter->buildings()->attach($building->id);
    
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('__get')->with('slug')->andReturn('test-building');
    $request->shouldReceive('__get')->with('hash')->andReturn('test-hash');
    $request->shouldReceive('ip')->andReturn('192.168.1.1');
    
    $this->userStore->shouldReceive('addVote')->once()->with($building->id);
    
    expect($voter->buildings)->toHaveCount(1);
    
    $this->action->execute($request);
    
    $voter->refresh();
    expect($voter->buildings)->toHaveCount(1); // Still only one vote
});

it('allows voter to vote for multiple buildings', function () {
    $building1 = Building::factory()->create(['slug' => 'building-1']);
    $building2 = Building::factory()->create(['slug' => 'building-2']);
    $voter = Voter::factory()->create(['hash' => 'test-hash']);
    
    $request1 = Mockery::mock(Request::class);
    $request1->shouldReceive('__get')->with('slug')->andReturn('building-1');
    $request1->shouldReceive('__get')->with('hash')->andReturn('test-hash');
    $request1->shouldReceive('ip')->andReturn('192.168.1.1');
    
    $request2 = Mockery::mock(Request::class);
    $request2->shouldReceive('__get')->with('slug')->andReturn('building-2');
    $request2->shouldReceive('__get')->with('hash')->andReturn('test-hash');
    $request2->shouldReceive('ip')->andReturn('192.168.1.1');
    
    $this->userStore->shouldReceive('addVote')->once()->with($building1->id);
    $this->userStore->shouldReceive('addVote')->once()->with($building2->id);
    
    $this->action->execute($request1);
    $this->action->execute($request2);
    
    $voter->refresh();
    expect($voter->buildings)->toHaveCount(2);
    expect($voter->buildings->pluck('id')->toArray())->toContain($building1->id);
    expect($voter->buildings->pluck('id')->toArray())->toContain($building2->id);
});

it('throws exception when building does not exist', function () {
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('__get')->with('slug')->andReturn('non-existent-building');
    $request->shouldReceive('__get')->with('hash')->andReturn('test-hash');
    
    expect(fn() => $this->action->execute($request))
        ->toThrow(ModelNotFoundException::class);
});

it('updates user store with building id', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('__get')->with('slug')->andReturn('test-building');
    $request->shouldReceive('__get')->with('hash')->andReturn('test-hash');
    $request->shouldReceive('ip')->andReturn('192.168.1.1');
    
    $this->userStore->shouldReceive('addVote')->once()->with($building->id);
    
    $this->action->execute($request);
});