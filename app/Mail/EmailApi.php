<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class EmailApi extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    // public $mailmessage;
    public $mailSubject;
    public $template;
    public $data;
    public function __construct($subject, $template, $data = [])
    {
        $this->mailSubject = $subject;
        $this->template = $template;
        $this->data = $data;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address("tagumcityrsp@gmail.com", "Recruitment, Selection and Placement"),
            subject: $this->mailSubject
        );
    }

    public function content(): Content
    {
        try {
            Log::info("🛠 EmailApi content called with template: {$this->template}, data: " . json_encode($this->data));

            return new Content(
                view: $this->template,
                with: $this->data
                // with: [
                //     'mailmessage' => $this->mailmessage,
                //     'mailSubject' => $this->mailSubject
                // ]
            );
        } catch (\Exception $e) {
            Log::error("❌ EmailApi failed to build email: " . $e->getMessage());
            throw $e;
        }
    }

    public function attachments(): array
    {
        return [];
    }
}
