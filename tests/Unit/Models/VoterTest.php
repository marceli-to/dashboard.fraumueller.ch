<?php

use App\Models\Building;
use App\Models\Voter;

it('can create a voter', function () {
    $voter = Voter::factory()->create([
        'ip_address' => '192.168.1.1',
        'hash' => 'test-hash-123',
    ]);

    expect($voter->ip_address)->toBe('192.168.1.1');
    expect($voter->hash)->toBe('test-hash-123');
    expect($voter)->toBeInstanceOf(Voter::class);
});

it('has fillable attributes', function () {
    $voter = new Voter();
    
    expect($voter->getFillable())->toContain('ip_address');
    expect($voter->getFillable())->toContain('hash');
});

it('has many to many buildings relationship', function () {
    $voter = Voter::factory()->create();
    $building1 = Building::factory()->create();
    $building2 = Building::factory()->create();
    
    $voter->buildings()->attach([$building1->id, $building2->id]);
    
    expect($voter->buildings)->toHaveCount(2);
    expect($voter->buildings->pluck('id')->toArray())->toContain($building1->id);
    expect($voter->buildings->pluck('id')->toArray())->toContain($building2->id);
});

it('can vote for multiple buildings', function () {
    $voter = Voter::factory()->create();
    $buildings = Building::factory()->count(3)->create();
    
    foreach ($buildings as $building) {
        $voter->buildings()->attach($building);
    }
    
    expect($voter->buildings)->toHaveCount(3);
});

it('can check if voted for specific building', function () {
    $voter = Voter::factory()->create();
    $building1 = Building::factory()->create();
    $building2 = Building::factory()->create();
    
    $voter->buildings()->attach($building1);
    
    expect($voter->buildings->contains($building1))->toBeTrue();
    expect($voter->buildings->contains($building2))->toBeFalse();
});

it('stores unique hash for fingerprinting', function () {
    $hash = 'unique-fingerprint-hash-123';
    $voter = Voter::factory()->withHash($hash)->create();
    
    expect($voter->hash)->toBe($hash);
    expect(strlen($voter->hash))->toBe(strlen($hash));
});

it('can find voter by hash', function () {
    $hash = 'findable-hash-456';
    $voter = Voter::factory()->withHash($hash)->create();
    
    $foundVoter = Voter::where('hash', $hash)->first();
    
    expect($foundVoter)->not->toBeNull();
    expect($foundVoter->id)->toBe($voter->id);
});

it('can have same ip but different hash', function () {
    $ipAddress = '10.0.0.1';
    
    $voter1 = Voter::factory()->withIp($ipAddress)->withHash('hash1')->create();
    $voter2 = Voter::factory()->withIp($ipAddress)->withHash('hash2')->create();
    
    expect($voter1->ip_address)->toBe($voter2->ip_address);
    expect($voter1->hash)->not->toBe($voter2->hash);
    expect(Voter::where('ip_address', $ipAddress)->count())->toBe(2);
});