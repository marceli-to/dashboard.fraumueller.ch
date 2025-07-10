<?php

use App\Models\User;

it('can create a user', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($user->name)->toBe('John Doe');
    expect($user->email)->toBe('john@example.com');
    expect($user)->toBeInstanceOf(User::class);
});

it('has fillable attributes', function () {
    $user = new User();
    
    expect($user->getFillable())->toContain('name');
    expect($user->getFillable())->toContain('email');
    expect($user->getFillable())->toContain('password');
});

it('hides password and remember_token from arrays', function () {
    $user = User::factory()->create();
    
    expect($user->getHidden())->toContain('password');
    expect($user->getHidden())->toContain('remember_token');
    expect($user->toArray())->not->toHaveKey('password');
    expect($user->toArray())->not->toHaveKey('remember_token');
});

it('casts email_verified_at to datetime', function () {
    $user = User::factory()->create();
    
    expect($user->getCasts())->toHaveKey('email_verified_at');
    expect($user->getCasts()['email_verified_at'])->toBe('datetime');
    expect($user->email_verified_at)->toBeInstanceOf(DateTime::class);
});

it('casts password to hashed', function () {
    $user = User::factory()->create();
    
    expect($user->getCasts())->toHaveKey('password');
    expect($user->getCasts()['password'])->toBe('hashed');
});

it('can be created with unverified email', function () {
    $user = User::factory()->unverified()->create();
    
    expect($user->email_verified_at)->toBeNull();
    expect($user->hasVerifiedEmail())->toBeFalse();
});

it('can be created with verified email', function () {
    $user = User::factory()->create();
    
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->hasVerifiedEmail())->toBeTrue();
});

it('can find user by email', function () {
    $user = User::factory()->create(['email' => 'unique@example.com']);
    
    $foundUser = User::where('email', 'unique@example.com')->first();
    
    expect($foundUser)->not->toBeNull();
    expect($foundUser->id)->toBe($user->id);
});

it('requires unique email', function () {
    $email = 'duplicate@example.com';
    User::factory()->create(['email' => $email]);
    
    expect(fn() => User::factory()->create(['email' => $email]))
        ->toThrow(Exception::class);
});

it('can update user information', function () {
    $user = User::factory()->create();
    
    $user->update([
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
    
    expect($user->fresh()->name)->toBe('Updated Name');
    expect($user->fresh()->email)->toBe('updated@example.com');
});