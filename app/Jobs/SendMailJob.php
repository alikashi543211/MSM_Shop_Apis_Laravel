<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $email, $subject, $view, $body, $cc, $attachments;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $subject, $body, $view, $cc = [], $attachments = [])
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->view = $view;
        $this->body = $body;
        $this->cc = $cc;
        $this->attachments = $attachments;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email      = $this->email;
        $subject    = $this->subject;
        Mail::send('emails.' . $this->view, $this->body, function ($message) use ($email, $subject) {
            $message->to($email)
                ->subject($subject)
                ->cc($this->cc);
            foreach ($this->attachments as $key => $attachment) {
                $message->attach($attachment['path'], ['as' => $attachment['name']]);
            };
        });
        return true;
    }
}
