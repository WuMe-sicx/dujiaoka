<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class MailSend implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数。
     *
     * @var int
     */
    public $tries = 2;

    /**
     * 任务运行的超时时间。
     *
     * @var int
     */
    public $timeout = 30;

    private $to;

    private $content;

    private $title;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $to, string $title, string $content)
    {
        $this->to = $to;
        $this->title = $title;
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $body = $this->content;
        $title = $this->title;
        $sysConfig = cache('system-setting');

        // Laravel 9 邮件配置使用嵌套结构
        config([
            'mail.default' => $sysConfig['driver'] ?? 'smtp',
            'mail.mailers.smtp.host' => $sysConfig['host'] ?? '',
            'mail.mailers.smtp.port' => $sysConfig['port'] ?? '465',
            'mail.mailers.smtp.username' => $sysConfig['username'] ?? '',
            'mail.mailers.smtp.password' => $sysConfig['password'] ?? '',
            'mail.mailers.smtp.encryption' => $sysConfig['encryption'] ?? '',
            'mail.from' => [
                'address' => $sysConfig['from_address'] ?? '',
                'name' => $sysConfig['from_name'] ?? '独角发卡',
            ],
        ]);

        $to = $this->to;

        // 不再需要重新注册 MailServiceProvider
        // Laravel 9 中 config 变更会自动生效
        Mail::send(['html' => 'email.mail'], ['body' => $body], function ($message) use ($to, $title) {
            $message->to($to)->subject($title);
        });
    }
}
