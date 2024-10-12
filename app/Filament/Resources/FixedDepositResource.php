<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\FixedDeposit;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use App\Filament\Resources\FixedDepositResource\Pages;

class FixedDepositResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Account Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('bank')
                                    ->required()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('accountno')
                                    ->required()
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make('Deposit Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('principal_amt')
                                    ->required()
                                    ->numeric()
                                    ->label('Principal Amount')
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('maturity_amt')
                                    ->required()
                                    ->numeric()
                                    ->label('Maturity Amount')
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('int_rate')
                                    ->required()
                                    ->numeric()
                                    ->label('Interest Rate (%)')
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make('Term Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('start_date')
                                    ->required()
                                    ->live()
                                    ->label('Start Date')
                                    ->columnSpan(1)
                                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateTerm($set, $get)),
                                Forms\Components\DatePicker::make('maturity_date')
                                    ->required()
                                    ->live()
                                    ->label('Maturity Date')
                                    ->columnSpan(1)
                                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateTerm($set, $get)),
                                Forms\Components\TextInput::make('term_display')
                                    ->label('Term')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(2),
                                Forms\Components\Hidden::make('term')
                                    ->dehydrated(true),
                            ]),
                    ]),
            ]);
    }


    public static function calculateTerm(Set $set, Get $get): void
    {
        $startDate = $get('start_date');
        $maturityDate = $get('maturity_date');

        if ($startDate && $maturityDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($maturityDate);

            // Calculate the exact difference in years, months, and days
            $diff = $start->diff($end);

            $years = $diff->y;    // Years difference
            $months = $diff->m;   // Months difference
            $days = $diff->d;     // Days difference

            // Prepare display term based on the differences
            $displayTerm = '';

            // Add years to the display if there are any
            if ($years > 0) {
                $displayTerm .= $years . ' year' . ($years > 1 ? 's' : '') . ' ';
            }

            // Add months to the display if there are any
            if ($months > 0) {
                $displayTerm .= $months . ' month' . ($months > 1 ? 's' : '') . ' ';
            }

            // Add days to the display if there are any
            if ($days > 0) {
                $displayTerm .= $days . ' day' . ($days > 1 ? 's' : '');
            }

            // Set the display term, fallback to '1 day' if empty
            $set('term_display', trim($displayTerm) ?: '1 day');

            // Set the actual total number of days in the database
            $totalDays = $start->diffInDays($end);
            $set('term', $totalDays);
        } else {
            // Clear the term display and value if dates are missing
            $set('term_display', '');
            $set('term', null);
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
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
