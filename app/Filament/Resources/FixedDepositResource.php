<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FixedDepositResource\Pages;
use App\Filament\Resources\FixedDepositResource\RelationManagers;
use App\Models\FixedDeposit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FixedDepositResource extends Resource
{
    protected static ?string $model = FixedDeposit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('bank')
                    ->required(),
                Forms\Components\TextInput::make('accountno')
                    ->required(),
                Forms\Components\TextInput::make('principal_amt')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('maturity_amt')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('maturity_date')
                    ->required(),
                Forms\Components\TextInput::make('term')
                    ->required(),
                Forms\Components\TextInput::make('int_rate')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('Int_amt')

                    ->numeric(),
                Forms\Components\TextInput::make('Int_year')

                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bank')
                    ->searchable(),
                Tables\Columns\TextColumn::make('accountno')
                    ->searchable(),
                Tables\Columns\TextColumn::make('principal_amt')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('maturity_amt')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('maturity_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('term')
                    ->searchable(),
                Tables\Columns\TextColumn::make('int_rate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Int_amt')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Int_year')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFixedDeposits::route('/'),
            'create' => Pages\CreateFixedDeposit::route('/create'),
            'edit' => Pages\EditFixedDeposit::route('/{record}/edit'),
        ];
    }
}
