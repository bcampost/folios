<?php

namespace App\Mail;

use App\Models\Folio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrecioListaAsignado extends Mailable
{
    use Queueable, SerializesModels;

    public string $folioUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Folio $folio
    )
    {
        $this->folioUrl = env('FRONTEND_URL') . '/panel/previos/' . $this->folio->id;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Precio de Lista del Previo Asignado',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mails.previo.precio_lista_asignado',
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
