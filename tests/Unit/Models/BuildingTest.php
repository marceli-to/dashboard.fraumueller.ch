<?php

use App\Models\Building;
use App\Models\Comment;
use App\Models\Voter;

it('can create a building', function () {
    $building = Building::factory()->create([
        'title' => 'Test Building',
        'slug' => 'test-building',
        'year' => 2023,
    ]);

    expect($building->title)->toBe('Test Building');
    expect($building->slug)->toBe('test-building');
    expect($building->year)->toBe(2023);
    expect($building)->toBeInstanceOf(Building::class);
});

it('has fillable attributes', function () {
    $building = new Building();
    
    expect($building->getFillable())->toContain('slug');
    expect($building->getFillable())->toContain('title');
    expect($building->getFillable())->toContain('short_description');
    expect($building->getFillable())->toContain('year');
    expect($building->getFillable())->toContain('lat');
    expect($building->getFillable())->toContain('long');
    expect($building->getFillable())->toContain('maps');
});

it('has many comments relationship', function () {
    $building = Building::factory()->create();
    $comment = Comment::factory()->published()->forBuilding($building)->create();
    
    expect($building->comments)->toHaveCount(1);
    expect($building->comments->first())->toBeInstanceOf(Comment::class);
    expect($building->comments->first()->id)->toBe($comment->id);
});

it('only returns published comments in relationship', function () {
    $building = Building::factory()->create();
    
    Comment::factory()->published()->forBuilding($building)->create();
    Comment::factory()->draft()->forBuilding($building)->create();
    
    expect($building->comments)->toHaveCount(1);
    expect($building->comments->first()->published)->toBeTrue();
});

it('orders comments by created_at desc', function () {
    $building = Building::factory()->create();
    
    $oldComment = Comment::factory()->published()->forBuilding($building)->create([
        'created_at' => now()->subDays(2)
    ]);
    $newComment = Comment::factory()->published()->forBuilding($building)->create([
        'created_at' => now()->subDays(1)
    ]);
    
    expect($building->comments->first()->id)->toBe($newComment->id);
    expect($building->comments->last()->id)->toBe($oldComment->id);
});

it('has many to many voters relationship', function () {
    $building = Building::factory()->create();
    $voter = Voter::factory()->create();
    
    $building->voters()->attach($voter);
    
    expect($building->voters)->toHaveCount(1);
    expect($building->voters->first())->toBeInstanceOf(Voter::class);
    expect($building->voters->first()->id)->toBe($voter->id);
});

it('can have coordinates', function () {
    $building = Building::factory()->create([
        'lat' => 47.3769,
        'long' => 8.5417,
    ]);
    
    expect($building->lat)->toBe(47.3769);
    expect($building->long)->toBe(8.5417);
});

it('can store maps data as json', function () {
    $mapsData = [
        'street' => 'Bahnhofstrasse 1',
        'city' => 'Zürich',
        'postal_code' => 8001
    ];
    
    $building = Building::factory()->create([
        'maps' => json_encode($mapsData)
    ]);
    
    expect(json_decode($building->maps, true))->toBe($mapsData);
});