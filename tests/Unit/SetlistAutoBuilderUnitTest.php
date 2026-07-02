<?php

use App\Enums\EnergyLevel;

it('orders energy levels correctly', function () {
    expect(EnergyLevel::High->sortOrder())->toBeGreaterThan(EnergyLevel::Medium->sortOrder());
    expect(EnergyLevel::Medium->sortOrder())->toBeGreaterThan(EnergyLevel::Low->sortOrder());
});

it('has correct labels', function () {
    expect(EnergyLevel::High->getLabel())->toBe('High');
    expect(EnergyLevel::Medium->getLabel())->toBe('Medium');
    expect(EnergyLevel::Low->getLabel())->toBe('Low');
});
