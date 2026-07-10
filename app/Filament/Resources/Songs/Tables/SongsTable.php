<?php

namespace App\Filament\Resources\Songs\Tables;

use App\Enums\EnergyLevel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SongsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active?')
                    ->sortable(),
                TextColumn::make('artist')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('album')
                    ->searchable(),
                TextColumn::make('release_year')
                    ->numeric(thousandsSeparator: '')
                    ->sortable(),
                TextColumn::make('runtime')
                    ->sortable(),
                TextColumn::make('energy_level')
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_opener')
                    ->label('Opener?')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('genre')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('key')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('has_track')
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_acoustic')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tempo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('energy_level')
                    ->options(EnergyLevel::class),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('title');
    }
}
