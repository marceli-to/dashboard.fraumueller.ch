<?php

use App\Models\Building;

it('displays the home page', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertViewIs('pages.home');
});

it('passes buildings to the view', function () {
    $buildings = Building::factory()->count(3)->create();
    
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertViewHas('buildings');
    
    $viewBuildings = $response->viewData('buildings');
    expect($viewBuildings)->toHaveCount(3);
});

it('displays buildings in random order', function () {
    Building::factory()->count(10)->create();
    
    // Get the page multiple times to check randomness
    $response1 = $this->get('/');
    $response2 = $this->get('/');
    
    $buildings1 = $response1->viewData('buildings');
    $buildings2 = $response2->viewData('buildings');
    
    // While not guaranteed, it's very unlikely they'll be in the same order
    // if they're truly random (with 10 buildings, probability is 1/10!)
    expect($buildings1->pluck('id')->toArray())->not->toBe($buildings2->pluck('id')->toArray());
});

it('works with no buildings', function () {
    // Ensure no buildings exist
    expect(Building::count())->toBe(0);
    
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertViewHas('buildings');
    
    $viewBuildings = $response->viewData('buildings');
    expect($viewBuildings)->toHaveCount(0);
});

it('displays all buildings regardless of year', function () {
    Building::factory()->year(1950)->create();
    Building::factory()->year(2000)->create();
    Building::factory()->year(2025)->create();
    
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $viewBuildings = $response->viewData('buildings');
    expect($viewBuildings)->toHaveCount(3);
});

it('includes building properties in response', function () {
    $building = Building::factory()->create([
        'title' => 'Test Building',
        'slug' => 'test-building',
        'year' => 2023
    ]);
    
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $viewBuildings = $response->viewData('buildings');
    $firstBuilding = $viewBuildings->first();
    
    expect($firstBuilding->title)->toBe('Test Building');
    expect($firstBuilding->slug)->toBe('test-building');
    expect($firstBuilding->year)->toBe(2023);
});

it('uses correct route name', function () {
    $response = $this->get(route('page.home'));
    
    $response->assertStatus(200);
});

it('has correct content type', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
});