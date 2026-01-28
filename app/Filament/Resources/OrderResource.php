<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\Order;
use App\Models\Pay;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $recordTitleAttribute = 'order_sn';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-shopping-cart';
    }

    public static function getNavigationLabel(): string
    {
        return '订单管理';
    }

    public static function getModelLabel(): string
    {
        return '订单';
    }

    public static function getPluralModelLabel(): string
    {
        return '订单';
    }

    public static function getNavigationGroup(): ?string
    {
        return '销售管理';
    }

    public static function getNavigationSort(): ?int
    {
        return 0;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Info')
                    ->schema([
                        Forms\Components\TextInput::make('order_sn')
                            ->label('Order SN')
                            ->disabled(),

                        Forms\Components\TextInput::make('title')
                            ->label('Title'),

                        Forms\Components\Placeholder::make('goods_name')
                            ->label('Product')
                            ->content(fn ($record) => $record?->goods?->gd_name ?? '-'),

                        Forms\Components\Placeholder::make('pay_name')
                            ->label('Payment')
                            ->content(fn ($record) => $record?->pay?->pay_name ?? '-'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->disabled(),

                        Forms\Components\TextInput::make('buy_ip')
                            ->label('IP')
                            ->disabled(),
                    ])->columns(2),

                Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('goods_price')
                            ->label('Unit Price')
                            ->disabled(),

                        Forms\Components\TextInput::make('buy_amount')
                            ->label('Quantity')
                            ->disabled(),

                        Forms\Components\TextInput::make('total_price')
                            ->label('Total')
                            ->disabled(),

                        Forms\Components\Placeholder::make('coupon_name')
                            ->label('Coupon')
                            ->content(fn ($record) => $record?->coupon?->coupon ?? '-'),

                        Forms\Components\TextInput::make('coupon_discount_price')
                            ->label('Coupon Discount')
                            ->disabled(),

                        Forms\Components\TextInput::make('wholesale_discount_price')
                            ->label('Wholesale Discount')
                            ->disabled(),

                        Forms\Components\TextInput::make('actual_price')
                            ->label('Actual Price')
                            ->disabled(),
                    ])->columns(3),

                Section::make('Status & Delivery')
                    ->schema([
                        Forms\Components\Radio::make('status')
                            ->label('Order Status')
                            ->options([
                                Order::STATUS_WAIT_PAY => 'Wait Pay',
                                Order::STATUS_PENDING => 'Pending',
                                Order::STATUS_PROCESSING => 'Processing',
                                Order::STATUS_COMPLETED => 'Completed',
                                Order::STATUS_FAILURE => 'Failure',
                                Order::STATUS_ABNORMAL => 'Abnormal',
                                Order::STATUS_EXPIRED => 'Expired',
                            ]),

                        Forms\Components\Radio::make('type')
                            ->label('Delivery Type')
                            ->options([
                                Order::AUTOMATIC_DELIVERY => 'Automatic',
                                Order::MANUAL_PROCESSING => 'Manual',
                            ]),

                        Forms\Components\Textarea::make('info')
                            ->label('Delivery Info')
                            ->rows(6)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('search_pwd')
                            ->label('Search Password'),

                        Forms\Components\TextInput::make('trade_no')
                            ->label('Trade No')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_sn')
                    ->label('Order SN')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state == Order::AUTOMATIC_DELIVERY ? 'Auto' : 'Manual')
                    ->color(fn ($state) => $state == Order::AUTOMATIC_DELIVERY ? 'success' : 'info'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('goods.gd_name')
                    ->label('Product')
                    ->limit(15),

                Tables\Columns\TextColumn::make('actual_price')
                    ->label('Price')
                    ->money('CNY')
                    ->sortable(),

                Tables\Columns\TextColumn::make('buy_amount')
                    ->label('Qty'),

                Tables\Columns\TextColumn::make('pay.pay_name')
                    ->label('Payment')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('trade_no')
                    ->label('Trade No')
                    ->copyable()
                    ->limit(15)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        Order::STATUS_WAIT_PAY => 'Wait Pay',
                        Order::STATUS_PENDING => 'Pending',
                        Order::STATUS_PROCESSING => 'Processing',
                        Order::STATUS_COMPLETED => 'Completed',
                        Order::STATUS_FAILURE => 'Failure',
                        Order::STATUS_ABNORMAL => 'Abnormal',
                        Order::STATUS_EXPIRED => 'Expired',
                        default => 'Unknown',
                    })
                    ->color(fn ($state) => match($state) {
                        Order::STATUS_COMPLETED => 'success',
                        Order::STATUS_WAIT_PAY => 'warning',
                        Order::STATUS_PENDING, Order::STATUS_PROCESSING => 'info',
                        Order::STATUS_FAILURE, Order::STATUS_ABNORMAL => 'danger',
                        Order::STATUS_EXPIRED => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Order::STATUS_WAIT_PAY => 'Wait Pay',
                        Order::STATUS_PENDING => 'Pending',
                        Order::STATUS_PROCESSING => 'Processing',
                        Order::STATUS_COMPLETED => 'Completed',
                        Order::STATUS_FAILURE => 'Failure',
                        Order::STATUS_ABNORMAL => 'Abnormal',
                        Order::STATUS_EXPIRED => 'Expired',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        Order::AUTOMATIC_DELIVERY => 'Automatic',
                        Order::MANUAL_PROCESSING => 'Manual',
                    ]),
                Tables\Filters\SelectFilter::make('goods_id')
                    ->label('Product')
                    ->options(Goods::query()->pluck('gd_name', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('pay_id')
                    ->label('Payment')
                    ->options(Pay::query()->pluck('pay_name', 'id')),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
                Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
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
