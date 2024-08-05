<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FixedDepositResource\Pages;
use App\Filament\Resources\FixedDepositResource\RelationManagers;
use App\Models\FixedDeposit;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;

class FixedDepositResource extends Resource
{
    protected static ?string $model = FixedDeposit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('bank')
                    ->required(),
                TextInput::make('accountno')
                    ->required(),
                TextInput::make('principal_amt')
                    ->required()
                    ->numeric(),
                //->reactive()
                // ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //   self::updateIntAmt($set, $get);
                // }),
                TextInput::make('maturity_amt')
                    ->required()
                    ->numeric()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        self::updateIntAmt($set, $get);
                    }),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('maturity_date')
                    ->required(),
                TextInput::make('term')
                    ->required()
                    ->numeric()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        self::updateIntYear($set, $get);
                    }),
                TextInput::make('int_rate')
                    ->required()
                    ->numeric(),
                TextInput::make('Int_amt')
                    ->required()
                    ->numeric()
                    ->disabled()
                    ->reactive(),
                TextInput::make('Int_year')
                    ->numeric()
                    ->disabled()
                    ->reactive(),
            ]);
    }
    protected static function updateIntAmt(callable $set, callable $get)
    {
        $principal_amt = $get('principal_amt');
        $maturity_amt = $get('maturity_amt');

        if ($principal_amt !== null && $maturity_amt !== null) {
            $Int_amt = (float) $maturity_amt - (float) $principal_amt;
            $set('Int_amt', $Int_amt);
            self::updateIntYear($set, $get);
        }
    }

    protected static function updateIntYear(callable $set, callable $get)
    {
        $term = $get('term');
        $Int_amt = $get('Int_amt');

        if ($term !== null && $Int_amt !== null) {
            if ($term < 365) {
                $set('Int_year', $Int_amt);
            } else {
                $set('Int_year', ($Int_amt / $term) * 365);
            }
        }
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
