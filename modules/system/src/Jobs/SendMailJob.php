<?php

namespace App\System\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use App\System\Mails\SendMail;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    public $to;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $view;

    /**
     * @var array
     */
    public $data = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $subject, $view, $data = [])
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->to)->send(new SendMail($this->subject, $this->view, $this->data));
    }
}
