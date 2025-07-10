<?php

use App\Models\Building;
use App\Models\Comment;

it('displays building page with valid slug', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    
    $response = $this->get('/test-building');
    
    $response->assertStatus(200);
    $response->assertViewIs('pages.building');
});

it('returns 404 for non-existent building', function () {
    $response = $this->get('/non-existent-building');
    
    $response->assertStatus(404);
});

it('passes building data to view', function () {
    $building = Building::factory()->create([
        'slug' => 'test-building',
        'title' => 'Test Building',
        'year' => 2023
    ]);
    
    $response = $this->get('/test-building');
    
    $response->assertStatus(200);
    $response->assertViewHas('building');
    
    $viewBuilding = $response->viewData('building');
    expect($viewBuilding->id)->toBe($building->id);
    expect($viewBuilding->title)->toBe('Test Building');
    expect($viewBuilding->year)->toBe(2023);
});

it('includes published comments with building', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    $publishedComment = Comment::factory()->published()->forBuilding($building)->create();
    $draftComment = Comment::factory()->draft()->forBuilding($building)->create();
    
    $response = $this->get('/test-building');
    
    $response->assertStatus(200);
    $viewBuilding = $response->viewData('building');
    
    expect($viewBuilding->comments)->toHaveCount(1);
    expect($viewBuilding->comments->first()->id)->toBe($publishedComment->id);
    expect($viewBuilding->comments->first()->published)->toBeTrue();
});

it('orders comments by created_at desc', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    $oldComment = Comment::factory()->published()->forBuilding($building)->create([
        'created_at' => now()->subDays(2)
    ]);
    $newComment = Comment::factory()->published()->forBuilding($building)->create([
        'created_at' => now()->subDays(1)
    ]);
    
    $response = $this->get('/test-building');
    
    $viewBuilding = $response->viewData('building');
    expect($viewBuilding->comments->first()->id)->toBe($newComment->id);
    expect($viewBuilding->comments->last()->id)->toBe($oldComment->id);
});

it('passes hasVote status to view', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    
    $response = $this->get('/test-building');
    
    $response->assertStatus(200);
    $response->assertViewHas('hasVote');
    
    // Default should be false since no vote exists
    expect($response->viewData('hasVote'))->toBeFalse();
});

it('works with building that has no comments', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    
    $response = $this->get('/test-building');
    
    $response->assertStatus(200);
    $viewBuilding = $response->viewData('building');
    expect($viewBuilding->comments)->toHaveCount(0);
});

it('uses route model binding with slug', function () {
    $building = Building::factory()->create(['slug' => 'unique-building-slug']);
    
    $response = $this->get('/unique-building-slug');
    
    $response->assertStatus(200);
    $viewBuilding = $response->viewData('building');
    expect($viewBuilding->slug)->toBe('unique-building-slug');
});

it('loads comments relationship efficiently', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    Comment::factory()->published()->forBuilding($building)->count(5)->create();
    
    // Enable query logging to verify the relationship is eager loaded
    \DB::enableQueryLog();
    
    $response = $this->get('/test-building');
    
    $queries = \DB::getQueryLog();
    \DB::disableQueryLog();
    
    $response->assertStatus(200);
    
    // Should only have one query thanks to our RouteServiceProvider optimization
    expect(count($queries))->toBe(1);
    
    $viewBuilding = $response->viewData('building');
    expect($viewBuilding->comments)->toHaveCount(5);
});

it('uses correct route name', function () {
    $building = Building::factory()->create(['slug' => 'test-building']);
    
    $response = $this->get(route('page.building', $building));
    
    $response->assertStatus(200);
});

it('handles special characters in slug', function () {
    $building = Building::factory()->create(['slug' => 'building-with-numbers-123']);
    
    $response = $this->get('/building-with-numbers-123');
    
    $response->assertStatus(200);
    $viewBuilding = $response->viewData('building');
    expect($viewBuilding->slug)->toBe('building-with-numbers-123');
});