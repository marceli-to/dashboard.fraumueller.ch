<?php

it('displays the info page', function () {
    $response = $this->get('/info-zum-voting');
    
    $response->assertStatus(200);
    $response->assertViewIs('pages.info');
});

it('uses correct route name', function () {
    $response = $this->get(route('page.info'));
    
    $response->assertStatus(200);
});

it('has correct content type', function () {
    $response = $this->get('/info-zum-voting');
    
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
});

it('returns successful response', function () {
    $response = $this->get('/info-zum-voting');
    
    $response->assertSuccessful();
});

it('does not require authentication', function () {
    // Test that the page is accessible without being logged in
    $response = $this->get('/info-zum-voting');
    
    $response->assertStatus(200);
    $response->assertViewIs('pages.info');
});

it('is accessible via GET method only', function () {
    $response = $this->post('/info-zum-voting');
    $response->assertStatus(405); // Method Not Allowed
    
    $response = $this->put('/info-zum-voting');
    $response->assertStatus(405);
    
    $response = $this->delete('/info-zum-voting');
    $response->assertStatus(405);
});