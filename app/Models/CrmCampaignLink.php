<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmCampaignLink extends Model
{
    protected $guarded = ['id'];

    protected $dateFormat = 'Y-m-d H:i:sP';

    protected $hidden = ['token'];

    protected $casts = ['first_clicked_at' => 'immutable_datetime', 'click_count' => 'integer'];

    public function recipient()
    {
        return $this->belongsTo(CrmCampaignRecipient::class, 'recipient_id');
    }
}
