<?php

use App\Models\Building;

it('displays the map page', function () {
    $response = $this->get('/uebersichtskarte');
    
    $response->assertStatus(200);
    $response->assertViewIs('pages.map');
});

it('passes building data to view', function () {
    $buildings = Building::factory()->count(3)->create();
    
    $response = $this->get('/uebersichtskarte');
    
    $response->assertStatus(200);
    $response->assertViewHas('data');
    
    $mapData = $response->viewData('data');
    expect($mapData)->toHaveCount(3);
});

it('formats building data correctly for map', function () {
    $building = Building::factory()->create([
        'title' => 'Test Building',
        'slug' => 'test-building',
        'lat' => 47.3769,
        'long' => 8.5417
    ]);
    
    $response = $this->get('/uebersichtskarte');
    
    $response->assertStatus(200);
    $mapData = $response->viewData('data');
    
    expect($mapData)->toHaveCount(1);
    expect($mapData[0])->toBe([
        'title' => 'Test Building',
        'slug' => 'test-building',
        'lat' => 47.3769,
        'long' => 8.5417
    ]);
});

it('includes all buildings in map data', function () {
    $building1 = Building::factory()->create(['title' => 'Building 1']);
    $building2 = Building::factory()->create(['title' => 'Building 2']);
    $building3 = Building::factory()->create(['title' => 'Building 3']);
    
    $response = $this->get('/uebersichtskarte');
    
    $mapData = $response->viewData('data');
    $titles = collect($mapData)->pluck('title')->toArray();
    
    expect($titles)->toContain('Building 1');
    expect($titles)->toContain('Building 2');
    expect($titles)->toContain('Building 3');
});

it('works with no buildings', function () {
    expect(Building::count())->toBe(0);
    
    $response = $this->get('/uebersichtskarte');
    
    $response->assertStatus(200);
    $mapData = $response->viewData('data');
    expect($mapData)->toHaveCount(0);
    expect($mapData)->toBe([]);
});

it('handles buildings with null coordinates', function () {
    Building::factory()->create([
        'title' => 'Building without coords',
        'slug' => 'no-coords',
        'lat' => null,
        'long' => null
    ]);
    
    $response = $this->get('/uebersichtskarte');
    
    $response->assertStatus(200);
    $mapData = $response->viewData('data');
    
    expect($mapData)->toHaveCount(1);
    expect($mapData[0]['lat'])->toBeNull();
    expect($mapData[0]['long'])->toBeNull();
});

it('only includes necessary fields in map data', function () {
    Building::factory()->create([
        'title' => 'Test Building',
        'slug' => 'test-building',
        'lat' => 47.3769,
        'long' => 8.5417,
        'short_description' => 'This should not be in map data',
        'year' => 2023,
        'maps' => json_encode(['street' => 'Test Street'])
    ]);
    
    $response = $this->get('/uebersichtskarte');
    
    $mapData = $response->viewData('data');
    $buildingData = $mapData[0];
    
    expect(array_keys($buildingData))->toBe(['title', 'slug', 'lat', 'long']);
    expect($buildingData)->not->toHaveKey('short_description');
    expect($buildingData)->not->toHaveKey('year');
    expect($buildingData)->not->toHaveKey('maps');
});

it('uses correct route name', function () {
    $response = $this->get(route('page.map'));
    
    $response->assertStatus(200);
});

it('handles buildings with special characters in title', function () {
    Building::factory()->create([
        'title' => 'Bâtiment Spécial ñáéíóú',
        'slug' => 'batiment-special'
    ]);
    
    $response = $this->get('/uebersichtskarte');
    
    $response->assertStatus(200);
    $mapData = $response->viewData('data');
    
    expect($mapData[0]['title'])->toBe('Bâtiment Spécial ñáéíóú');
});

it('preserves coordinate precision', function () {
    Building::factory()->create([
        'lat' => 47.376912345,
        'long' => 8.541678901
    ]);
    
    $response = $this->get('/uebersichtskarte');
    
    $mapData = $response->viewData('data');
    
    expect($mapData[0]['lat'])->toBe(47.376912345);
    expect($mapData[0]['long'])->toBe(8.541678901);
});