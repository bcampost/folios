<?php

namespace App\Mail;

use App\Models\Folio;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class PrevioAprobadoFromProject extends Mailable
{
    use Queueable, SerializesModels;

    public string $folioUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Project $project
    )
    {
        $this->folioUrl = env('FRONTEND_URL') . '/panel/previos/';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Previo Aprobado',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mails.previo.previo_aprobado_project',
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
