<?php

namespace App\Mail;

use App\Models\Folio;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\States\Folio\PrevioSolicitadoState;
use Illuminate\Contracts\Queue\ShouldQueue;

class PrevioRechazado extends Mailable
{
    use Queueable, SerializesModels;

    public ?string $folioUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Folio $folio
    )
    {
        if(! $this->folio->state instanceof PrevioSolicitadoState) {
            $this->folioUrl = env('FRONTEND_URL') . '/panel/previos/' . $this->folio->id;
        } else {
            $this->folioUrl = null;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: ucfirst($this->folio->type->value) . ' Rechazado',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mails.previo.previo_rechazado',
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
