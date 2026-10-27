<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ProspectCampaignMail extends Mailable
{
    public ?string $pixelUrl = null;

    public ?string $trackedContentHtml = null;

    private bool $trackingDecorated = false;

    public function __construct(public string $campaignSubject, public string $content,
        public ?string $preheader = null, public ?string $ctaText = null, public ?string $ctaUrl = null, public ?array $image = null, public ?array $document = null, public ?\App\Models\CrmCampaignRecipient $trackingRecipient = null) {}

    public function build(): self
    {
        foreach ([$this->image, $this->document] as $asset) {
            if ($asset && ! \Illuminate\Support\Facades\Storage::disk('local')->exists($asset['path'])) {
                throw new \RuntimeException('Un fichier enregistré de la campagne est indisponible.');
            }
        }
        if ($this->document) {
            $this->attachFromStorageDisk('local', $this->document['path'], $this->document['name'], ['mime' => $this->document['mime']]);
        }

        if ($this->trackingRecipient && ! $this->trackingDecorated) {
            app(\App\Services\CrmCampaignTracking::class)->decorate($this, $this->trackingRecipient);
            $this->trackingDecorated = true;
        }

        return $this->subject($this->campaignSubject)->view('emails.prospect-campaign')->text('emails.prospect-campaign-text');
    }
}
