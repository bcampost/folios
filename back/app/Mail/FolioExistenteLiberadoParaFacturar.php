<?php

namespace App\Mail;

use App\Models\Folio;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class FolioExistenteLiberadoParaFacturar extends Mailable
{
    use Queueable, SerializesModels;

    public string $folioUrl;

    public function __construct(
        public Folio $folio
    )
    {
        $this->folioUrl = env('FRONTEND_URL') . '/panel/previos/' . $this->folio->id;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Folio Existente — Liberado para Facturar',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mails.folio.folio_existente_liberado_para_facturar',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
