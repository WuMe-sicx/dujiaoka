<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TopupOrderResource\Pages;
use App\Models\TopupOrder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TopupOrderResource extends Resource
{
    protected static ?string $model = TopupOrder::class;

    protected static ?string $recordTitleAttribute = 'order_sn';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrow-up-circle';
    }

    public static function getNavigationLabel(): string
    {
        return '充值订单';
    }

    public static function getModelLabel(): string
    {
        return '充值订单';
    }

    public static function getPluralModelLabel(): string
    {
        return '充值订单';
    }

    public static function getNavigationGroup(): ?string
    {
        return '用户中心';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_sn')
                    ->label('订单号')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('邮箱')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('金额')
                    ->money('CNY')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pay.pay_name')
                    ->label('支付方式'),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn ($state) => TopupOrder::getStatusMap()[$state] ?? '未知')
                    ->color(fn ($state) => match($state) {
                        TopupOrder::STATUS_COMPLETED => 'success',
                        TopupOrder::STATUS_WAIT_PAY => 'warning',
                        TopupOrder::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('trade_no')
                    ->label('交易号')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('buy_ip')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options(TopupOrder::getStatusMap()),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('用户')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListTopupOrders::route('/'),
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
