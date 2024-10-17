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
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Imports\FixedDepositImporter;
use App\Filament\Resources\FixedDepositResource\Pages;
use App\Filament\Resources\FixedDepositResource\Pages\EditFixedDeposit;
use App\Filament\Resources\FixedDepositResource\Pages\ListFixedDeposits;
use App\Filament\Resources\FixedDepositResource\Pages\CreateFixedDeposit;


class ChequeHelper
{
    public static function calculateRemainingDays(FixedDeposit $Cheque): string
    {
        $today = Carbon::today();
        $endDate = Carbon::parse($Cheque->maturity_date);

        if ($endDate->isBefore($today)) {
            return 'Past Due';
        } else {
            $diffInDays = abs($endDate->diffInDays($today)); // Use abs() for absolute value
            return number_format($diffInDays);
        }
    }
}

class FixedDepositResource extends Resource
{
    protected static ?string $model = FixedDeposit::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Banking';
    protected static ?string $navigationLabel = 'Fixed Deposits';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Account Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\select::make('bank')
                                    ->required()
                                    ->columnSpan(1)
                                    ->options([
                                        'HDFC' => 'HDFC',
                                        'ICICI' => 'ICICI',
                                        'SBI' => 'SBI',
                                    ])->preload()
                                    ->native(false),
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
                    ->searchable()
                    ->wrap()
                    ->limit(50)
                    ->label('Bank Name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('term')
                    ->searchable(),
                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Days Bal')
                    ->getStateUsing(function (FixedDeposit $record): ?string {
                        if ($record === null) {
                            return null;
                        }

                        try {
                            return ChequeHelper::calculateRemainingDays($record);
                        } catch (\Throwable $th) {
                            Log::error('Error in calculating days remaining:', ['error' => $th->getMessage()]);
                            return null;
                        }
                    })
                    ->badge()
                    ->color(function (?string $state): string {
                        if ($state === null) {
                            return 'primary'; // Example default color
                        }

                        if ($state === 'Past Due') {
                            return 'danger';
                        } elseif ($state > 30) {
                            return 'success';
                        } else {
                            // Optional: Define a default color for other states
                            return 'primary'; // Example default color
                        }
                    }),
                Tables\Columns\TextColumn::make('int_rate')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('Int_amt')
                    ->numeric()
                    ->sortable()
                    ->money('inr')
                    ->summarize(
                        Sum::make()
                            ->formatStateUsing(function ($state) {
                                // Ensure $state is treated as a number
                                $state = floatval($state);

                                // Round to 2 decimal places
                                $state = round($state, 2);

                                // Convert to Indian numbering system
                                $formatted = preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", number_format($state, 0, '.', ''));

                                return '₹' . $formatted;
                            })
                    ),
                Tables\Columns\TextColumn::make('Int_year')
                    ->numeric()
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 2);
                    })
                    ->sortable()
                    ->money('inr')
                    ->summarize(
                        Sum::make()
                            ->formatStateUsing(function ($state) {
                                // Ensure $state is treated as a number
                                $state = floatval($state);

                                // Round to 2 decimal places
                                $state = round($state, 2);

                                // Convert to Indian numbering system
                                $formatted = preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", number_format($state, 0, '.', ''));

                                return '₹' . $formatted;
                            })
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('maturity_date', 'asc')
            ->filters([
                SelectFilter::make('bank')
                    ->multiple()
                    ->options([
                        'HDFC' => 'HDFC',
                        'ICICI' => 'ICICI',
                        'SBI' => 'SBI',
                    ])
            ], layout: FiltersLayout::Modal)
            ->actions([
                EditAction::make(),

            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('totalPrincipal')
                    ->label(function () {
                        $model = FixedDepositResource::getModel();
                        $total = $model::query()->sum('principal_amt');
                        $formatter = new \NumberFormatter('en_IN', \NumberFormatter::DECIMAL);
                        return "Total Principal Amount: " . $formatter->format($total);
                    })
                    ->color('success')
                    ->icon('heroicon-o-currency-rupee'),

                Tables\Actions\Action::make('totalmaturity')
                    ->label(function () {
                        $model = FixedDepositResource::getModel();
                        $total = $model::query()->sum('maturity_amt');
                        $formatter = new \NumberFormatter('en_IN', \NumberFormatter::DECIMAL);
                        return "Total Maturity Amount: " . $formatter->format($total);
                    })
                    ->color('success')
                    ->icon('heroicon-o-currency-rupee'),

                ImportAction::make()
                    ->importer(FixedDepositImporter::class)
                    ->color('info')
                    ->label('Import')
                    ->icon('heroicon-o-arrow-up-on-square-stack'),
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
