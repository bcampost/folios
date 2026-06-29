<?php

namespace App\Mail;

use App\Models\Folio;
use App\Models\Project;
use App\States\Folio\FolioAprobadoState;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class FolioAprobadoFromProject extends Mailable
{
    use Queueable, SerializesModels;

    public string $folioUrl;

    public array|Collection $folios;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Project $project,
        public ?Folio $folio = null
    )
    {
        $this->folioUrl = env('FRONTEND_URL') . '/panel/previos/';

        if ($folio) {
            $this->folios = new Collection([$folio]);
        } else {
            $this->folios = $project->folios()->where('state', FolioAprobadoState::getStateId())->get();
        }

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Folio Aprobado',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mails.folio.folio_aprobado_from_project',
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
