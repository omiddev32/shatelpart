<?php

namespace App\System\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

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
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject , $view , $data = [])
    {

        \Log::info("Subject {$subject}");

        $this->fromAddress = env('MAIL_FROM_ADDRESS');
        $this->name = env('MAIL_FROM_NAME');
        $this->subject = $subject;
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->view($this->view)
            ->from($this->fromAddress, $this->name)
            ->cc($this->fromAddress, $this->name)
            ->bcc($this->fromAddress, $this->name)
            ->replyTo($this->fromAddress, $this->name)
            ->with([ 'data' => $this->data ]);
    }
}