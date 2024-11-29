<?php

namespace App\Message\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Phone
     *
     * @var string
     */
    private string $phone;

    /**
     * Text
     *
     * @var string
     */
    private string $text;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $phone, string $text)
    {
        $this->phone = $phone;
        $this->text = $text;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('message-service')->to($this->phone)->text($this->text)->send();
    }
}
