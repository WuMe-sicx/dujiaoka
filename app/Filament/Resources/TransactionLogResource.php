<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionLogResource\Pages;
use App\Models\TransactionLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionLogResource extends Resource
{
    protected static ?string $model = TransactionLog::class;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-banknotes';
    }

    public static function getNavigationLabel(): string
    {
        return '交易记录';
    }

    public static function getModelLabel(): string
    {
        return '交易记录';
    }

    public static function getPluralModelLabel(): string
    {
        return '交易记录';
    }

    public static function getNavigationGroup(): ?string
    {
        return '用户中心';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
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

                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('邮箱')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('类型')
                    ->badge()
                    ->formatStateUsing(fn ($state) => TransactionLog::getTypeMap()[$state] ?? $state)
                    ->color(fn ($state) => match($state) {
                        TransactionLog::TYPE_TOPUP => 'success',
                        TransactionLog::TYPE_PURCHASE => 'info',
                        TransactionLog::TYPE_REFUND => 'warning',
                        TransactionLog::TYPE_ADJUSTMENT => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('金额')
                    ->money('CNY')
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('balance_before')
                    ->label('变动前余额')
                    ->money('CNY')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('balance_after')
                    ->label('变动后余额')
                    ->money('CNY'),

                Tables\Columns\TextColumn::make('order_sn')
                    ->label('订单号')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('remark')
                    ->label('备注')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('类型')
                    ->options(TransactionLog::getTypeMap()),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('用户')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactionLogs::route('/'),
        ];
    }
}
