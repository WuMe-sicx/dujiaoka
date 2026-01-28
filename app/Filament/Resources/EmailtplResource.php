<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailtplResource\Pages;
use App\Models\Emailtpl;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmailtplResource extends Resource
{
    protected static ?string $model = Emailtpl::class;

    protected static ?string $recordTitleAttribute = 'tpl_name';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-envelope';
    }

    public static function getNavigationLabel(): string
    {
        return '邮件模板';
    }

    public static function getModelLabel(): string
    {
        return '邮件模板';
    }

    public static function getPluralModelLabel(): string
    {
        return '邮件模板';
    }

    public static function getNavigationGroup(): ?string
    {
        return '系统配置';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('tpl_name')
                    ->label('Template Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('tpl_token')
                    ->label('Token')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn ($record) => $record !== null)
                    ->dehydrated(fn ($record) => $record === null),

                Forms\Components\RichEditor::make('tpl_content')
                    ->label('Content')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tpl_name')
                    ->label('Template Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tpl_token')
                    ->label('Token')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
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
            'index' => Pages\ListEmailtpls::route('/'),
            'create' => Pages\CreateEmailtpl::route('/create'),
            'edit' => Pages\EditEmailtpl::route('/{record}/edit'),
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
