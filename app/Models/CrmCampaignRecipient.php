<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmCampaignRecipient extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['tracking_token'];

    protected $dateFormat = 'Y-m-d H:i:sP';

    protected $casts = ['first_opened_at' => 'immutable_datetime', 'first_clicked_at' => 'immutable_datetime', 'open_count' => 'integer', 'click_count' => 'integer', 'attempted_at' => 'immutable_datetime', 'sent_at' => 'immutable_datetime', 'crm_synced_at' => 'immutable_datetime'];

    public function campaign()
    {
        return $this->belongsTo(CrmCampaign::class, 'campaign_id')->withTrashed();
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class)->withTrashed();
    }

    public function links()
    {
        return $this->hasMany(CrmCampaignLink::class, 'recipient_id');
    }

    public static function labels(): array
    {
        return ['selected' => 'Sélectionné', 'pending' => 'En attente', 'sending' => 'En cours', 'sent' => 'Envoyé', 'failed' => 'Échec', 'uncertain' => 'À vérifier', 'skipped' => 'Ignoré'];
    }
}
