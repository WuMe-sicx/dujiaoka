<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayResource\Pages;
use App\Models\Pay;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PayResource extends Resource
{
    protected static ?string $model = Pay::class;

    protected static ?string $recordTitleAttribute = 'pay_name';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-credit-card';
    }

    public static function getNavigationLabel(): string
    {
        return '支付方式';
    }

    public static function getModelLabel(): string
    {
        return '支付方式';
    }

    public static function getPluralModelLabel(): string
    {
        return '支付方式';
    }

    public static function getNavigationGroup(): ?string
    {
        return '系统配置';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('pay_name')
                    ->label('Payment Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('pay_check')
                    ->label('Payment Code')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('merchant_id')
                    ->label('Merchant ID')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('merchant_key')
                    ->label('Merchant Key')
                    ->rows(3),

                Forms\Components\Textarea::make('merchant_pem')
                    ->label('Merchant PEM')
                    ->required()
                    ->rows(3),

                Forms\Components\Radio::make('pay_client')
                    ->label('Client Type')
                    ->options([
                        Pay::PAY_CLIENT_PC => 'PC',
                        Pay::PAY_CLIENT_MOBILE => 'Mobile',
                        Pay::PAY_CLIENT_ALL => 'All',
                    ])
                    ->default(Pay::PAY_CLIENT_PC)
                    ->required(),

                Forms\Components\Radio::make('pay_method')
                    ->label('Payment Method')
                    ->options([
                        Pay::METHOD_JUMP => 'Jump/Redirect',
                        Pay::METHOD_SCAN => 'Scan QR Code',
                    ])
                    ->default(Pay::METHOD_JUMP)
                    ->required(),

                Forms\Components\TextInput::make('pay_handleroute')
                    ->label('Handler Route')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('/pay/alipay'),

                Forms\Components\Toggle::make('is_open')
                    ->label('Status')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pay_name')
                    ->label('Payment Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pay_check')
                    ->label('Code')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pay_method')
                    ->label('Method')
                    ->formatStateUsing(fn ($state) => $state == Pay::METHOD_JUMP ? 'Jump' : 'Scan'),

                Tables\Columns\TextColumn::make('merchant_id')
                    ->label('Merchant ID')
                    ->limit(20),

                Tables\Columns\TextColumn::make('pay_client')
                    ->label('Client')
                    ->formatStateUsing(fn ($state) => match($state) {
                        Pay::PAY_CLIENT_PC => 'PC',
                        Pay::PAY_CLIENT_MOBILE => 'Mobile',
                        Pay::PAY_CLIENT_ALL => 'All',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('pay_handleroute')
                    ->label('Route')
                    ->limit(20),

                Tables\Columns\ToggleColumn::make('is_open')
                    ->label('Status'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('pay_client')
                    ->label('Client')
                    ->options([
                        Pay::PAY_CLIENT_PC => 'PC',
                        Pay::PAY_CLIENT_MOBILE => 'Mobile',
                        Pay::PAY_CLIENT_ALL => 'All',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPays::route('/'),
            'create' => Pages\CreatePay::route('/create'),
            'edit' => Pages\EditPay::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
