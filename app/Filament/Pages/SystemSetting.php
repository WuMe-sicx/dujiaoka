<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.system-setting';

    public ?array $data = [];

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationLabel(): string
    {
        return '系统设置';
    }

    public static function getNavigationGroup(): ?string
    {
        return '系统配置';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public function mount(): void
    {
        $settings = Cache::get('system-setting', []);
        $this->form->fill($settings ?: []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Base Settings')
                            ->schema([
                                Section::make('站点信息')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Site Title')
                                            ->required(),
                                        Forms\Components\FileUpload::make('img_logo')
                                            ->label('Logo Image')
                                            ->image()
                                            ->disk('admin'),
                                        Forms\Components\TextInput::make('text_logo')
                                            ->label('Text Logo'),
                                        Forms\Components\TextInput::make('keywords')
                                            ->label('SEO Keywords'),
                                        Forms\Components\Textarea::make('description')
                                            ->label('Description')
                                            ->columnSpanFull(),
                                    ])->columns(2),

                                Section::make('显示与安全')
                                    ->schema([
                                        Forms\Components\Select::make('template')
                                            ->label('Template')
                                            ->options(config('dujiaoka.templates', []))
                                            ->required(),
                                        Forms\Components\Select::make('language')
                                            ->label('Language')
                                            ->options(config('dujiaoka.language', []))
                                            ->required(),
                                        Forms\Components\TextInput::make('manage_email')
                                            ->label('Admin Email'),
                                        Forms\Components\TextInput::make('order_expire_time')
                                            ->label('Order Expire Time (minutes)')
                                            ->numeric()
                                            ->default(5)
                                            ->required(),
                                        Forms\Components\Toggle::make('is_open_anti_red')
                                            ->label('Anti-Red Mode'),
                                        Forms\Components\Toggle::make('is_open_img_code')
                                            ->label('Image Captcha'),
                                        Forms\Components\Toggle::make('is_open_search_pwd')
                                            ->label('Search Password'),
                                        Forms\Components\Toggle::make('is_open_google_translate')
                                            ->label('Google Translate'),
                                    ])->columns(2),

                                Section::make('公告与页脚')
                                    ->schema([
                                        Forms\Components\RichEditor::make('notice')
                                            ->label('Notice')
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('footer')
                                            ->label('Footer')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Order Push Settings')
                            ->schema([
                                Section::make('Server酱')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_open_server_jiang')
                                            ->label('Server Jiang'),
                                        Forms\Components\TextInput::make('server_jiang_token')
                                            ->label('Server Jiang Token'),
                                    ])->columns(2),

                                Section::make('Telegram')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_open_telegram_push')
                                            ->label('Telegram Push'),
                                        Forms\Components\TextInput::make('telegram_bot_token')
                                            ->label('Telegram Bot Token'),
                                        Forms\Components\TextInput::make('telegram_userid')
                                            ->label('Telegram User ID'),
                                    ])->columns(2),

                                Section::make('Bark')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_open_bark_push')
                                            ->label('Bark Push'),
                                        Forms\Components\Toggle::make('is_open_bark_push_url')
                                            ->label('Bark Custom URL'),
                                        Forms\Components\TextInput::make('bark_server')
                                            ->label('Bark Server'),
                                        Forms\Components\TextInput::make('bark_token')
                                            ->label('Bark Token'),
                                    ])->columns(2),

                                Section::make('企业微信')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_open_qywxbot_push')
                                            ->label('WeCom Bot Push'),
                                        Forms\Components\TextInput::make('qywxbot_key')
                                            ->label('WeCom Bot Key'),
                                    ])->columns(2),
                            ]),

                        Tab::make('Mail Settings')
                            ->schema([
                                Section::make('SMTP配置')
                                    ->schema([
                                        Forms\Components\TextInput::make('driver')
                                            ->label('Mail Driver')
                                            ->default('smtp')
                                            ->required(),
                                        Forms\Components\TextInput::make('host')
                                            ->label('SMTP Host'),
                                        Forms\Components\TextInput::make('port')
                                            ->label('SMTP Port')
                                            ->default('587'),
                                        Forms\Components\TextInput::make('username')
                                            ->label('Username'),
                                        Forms\Components\TextInput::make('password')
                                            ->label('Password')
                                            ->password(),
                                        Forms\Components\TextInput::make('encryption')
                                            ->label('Encryption')
                                            ->placeholder('tls'),
                                        Forms\Components\TextInput::make('from_address')
                                            ->label('From Address'),
                                        Forms\Components\TextInput::make('from_name')
                                            ->label('From Name'),
                                    ])->columns(2),
                            ]),

                        Tab::make('GeeTest')
                            ->schema([
                                Section::make('GeeTest 验证')
                                    ->schema([
                                        Forms\Components\TextInput::make('geetest_id')
                                            ->label('GeeTest ID'),
                                        Forms\Components\TextInput::make('geetest_key')
                                            ->label('GeeTest Key'),
                                        Forms\Components\Toggle::make('is_open_geetest')
                                            ->label('Enable GeeTest'),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        Cache::put('system-setting', $data);

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
