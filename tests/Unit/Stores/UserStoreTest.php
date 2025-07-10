<?php

use App\Stores\UserStore;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    $this->store = new UserStore();
    Session::flush(); // Clear session between tests
});

it('can set and get store data', function () {
    $data = ['test' => 'value', 'number' => 123];
    
    $this->store->set($data);
    
    expect($this->store->get())->toBe($data);
});

it('returns empty array when store is empty', function () {
    expect($this->store->get())->toBe([]);
});

it('can clear store data', function () {
    $this->store->set(['test' => 'value']);
    expect($this->store->get())->not->toBe([]);
    
    $this->store->clear();
    expect($this->store->get())->toBe([]);
});

it('can set and get specific attributes', function () {
    $this->store->setAttribute('name', 'John Doe');
    $this->store->setAttribute('age', 30);
    
    expect($this->store->getAttribute('name'))->toBe('John Doe');
    expect($this->store->getAttribute('age'))->toBe(30);
});

it('returns null for non-existent attribute', function () {
    expect($this->store->getAttribute('non-existent'))->toBeNull();
});

it('can check if attribute exists', function () {
    $this->store->setAttribute('exists', 'value');
    
    expect($this->store->has('exists'))->toBeTrue();
    expect($this->store->has('does-not-exist'))->toBeFalse();
});

it('can remove specific attribute', function () {
    $this->store->setAttribute('remove-me', 'value');
    $this->store->setAttribute('keep-me', 'value');
    
    expect($this->store->has('remove-me'))->toBeTrue();
    
    $this->store->removeAttribute('remove-me');
    
    expect($this->store->has('remove-me'))->toBeFalse();
    expect($this->store->has('keep-me'))->toBeTrue();
});

it('can add votes', function () {
    expect($this->store->hasVote(1))->toBeFalse();
    
    $this->store->addVote(1);
    
    expect($this->store->hasVote(1))->toBeTrue();
});

it('can add multiple votes', function () {
    $this->store->addVote(1);
    $this->store->addVote(2);
    $this->store->addVote(3);
    
    expect($this->store->hasVote(1))->toBeTrue();
    expect($this->store->hasVote(2))->toBeTrue();
    expect($this->store->hasVote(3))->toBeTrue();
    expect($this->store->hasVote(4))->toBeFalse();
});

it('does not add duplicate votes', function () {
    $this->store->addVote(1);
    $this->store->addVote(1);
    $this->store->addVote(1);
    
    $votes = $this->store->getAttribute('votes');
    expect(count($votes))->toBe(1);
    expect($votes)->toBe([1]);
});

it('can remove votes', function () {
    $this->store->addVote(1);
    $this->store->addVote(2);
    
    expect($this->store->hasVote(1))->toBeTrue();
    expect($this->store->hasVote(2))->toBeTrue();
    
    $this->store->removeVote(1);
    
    expect($this->store->hasVote(1))->toBeFalse();
    expect($this->store->hasVote(2))->toBeTrue();
});

it('handles removing non-existent vote gracefully', function () {
    $this->store->addVote(1);
    
    $this->store->removeVote(999); // Non-existent vote
    
    expect($this->store->hasVote(1))->toBeTrue(); // Original vote still exists
});

it('maintains vote array indices after removal', function () {
    $this->store->addVote(1);
    $this->store->addVote(2);
    $this->store->addVote(3);
    
    $this->store->removeVote(2); // Remove middle element
    
    $votes = $this->store->getAttribute('votes');
    expect($votes)->toBe([1, 3]); // Array should be re-indexed
    expect(array_keys($votes))->toBe([0, 1]); // Keys should be sequential
});

it('works with no existing votes attribute', function () {
    // Don't set up any votes initially
    expect($this->store->hasVote(1))->toBeFalse();
    
    $this->store->addVote(1);
    expect($this->store->hasVote(1))->toBeTrue();
    
    $this->store->removeVote(1);
    expect($this->store->hasVote(1))->toBeFalse();
});

it('persists data across multiple operations', function () {
    $this->store->setAttribute('user_id', 123);
    $this->store->addVote(1);
    $this->store->addVote(2);
    
    // Create new instance to simulate new request
    $newStore = new UserStore();
    
    expect($newStore->getAttribute('user_id'))->toBe(123);
    expect($newStore->hasVote(1))->toBeTrue();
    expect($newStore->hasVote(2))->toBeTrue();
});