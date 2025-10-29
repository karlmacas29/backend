<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailApi extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;


    public $mailmessage;

    public $subject;
    /**
     * Create a new message instance.
     */
    public function __construct($message , $subject)
    {
        //
        $this->mailmessage = $message;
        $this->subject = $subject;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address("tagumcityrsp@gmail.com", "Recruitment, Selection and Placement"),
          subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail-template.mail',
            with: [
                'mailmessage' => $this->mailmessage,
                'subject' => $this->subject,
            ],

        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
