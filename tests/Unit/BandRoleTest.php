<?php

use App\Enums\BandRole;
use LaravelDaily\FilaTeams\Enums\TeamPermission;

test('owner() returns Owner case', function () {
    expect(BandRole::owner())->toBe(BandRole::Owner);
});

test('default() returns Performer case', function () {
    expect(BandRole::default())->toBe(BandRole::Performer);
});

test('assignable() excludes Owner', function () {
    $values = array_column(BandRole::assignable(), 'value');

    expect($values)->not->toContain('owner')
        ->and($values)->toContain('performer')
        ->and($values)->toContain('crew')
        ->and($values)->toContain('other');
});

test('Owner has all TeamPermission cases', function () {
    $permissions = BandRole::Owner->permissions();
    $allPermissions = TeamPermission::cases();

    foreach ($allPermissions as $permission) {
        expect($permissions)->toContain($permission);
    }
});

test('Performer has no permissions', function () {
    expect(BandRole::Performer->permissions())->toBeEmpty();
});

test('Crew has no permissions', function () {
    expect(BandRole::Crew->permissions())->toBeEmpty();
});

test('Other has no permissions', function () {
    expect(BandRole::Other->permissions())->toBeEmpty();
});

test('Owner hasPermission returns true for all permissions', function () {
    foreach (TeamPermission::cases() as $permission) {
        expect(BandRole::Owner->hasPermission($permission->value))->toBeTrue();
    }
});

test('Performer hasPermission returns false', function () {
    expect(BandRole::Performer->hasPermission('team:update'))->toBeFalse();
});

test('Owner level is highest', function () {
    expect(BandRole::Owner->level())->toBeGreaterThan(BandRole::Performer->level())
        ->and(BandRole::Owner->level())->toBeGreaterThan(BandRole::Crew->level())
        ->and(BandRole::Owner->level())->toBeGreaterThan(BandRole::Other->level());
});

test('Owner isAtLeast all roles', function () {
    foreach (BandRole::cases() as $role) {
        expect(BandRole::Owner->isAtLeast($role))->toBeTrue();
    }
});

test('Performer is not at least Owner', function () {
    expect(BandRole::Performer->isAtLeast(BandRole::Owner))->toBeFalse();
});
