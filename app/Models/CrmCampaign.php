<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmCampaign extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $dateFormat = 'Y-m-d H:i:sP';

    protected $casts = ['scheduled_at' => 'immutable_datetime', 'media' => 'array', 'queued_at' => 'immutable_datetime', 'started_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime'];

    public function recipients()
    {
        return $this->hasMany(CrmCampaignRecipient::class, 'campaign_id');
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    public function ctaLink(): ?string
    {
        return $this->destinationLink((string) $this->cta_url);
    }

    public function destinationLink(string $url): ?string
    {
        $url = trim($url);
        $parts = parse_url($url);
        if (! $parts || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }
        $pairs = array_filter(explode('&', $parts['query'] ?? ''), function ($pair) {
            $key = strtolower(urldecode(explode('=', $pair, 2)[0]));

            return $pair !== '' && ! in_array($key, ['utm_source', 'utm_medium', 'utm_campaign'], true);
        });
        $utm = ['utm_source' => 'dalytrac', 'utm_medium' => 'email'];
        if (filled($this->utm_campaign)) {
            $utm['utm_campaign'] = $this->utm_campaign;
        }
        $pairs[] = http_build_query($utm, '', '&', PHP_QUERY_RFC3986);
        $base = preg_split('/[?#]/', $url, 2)[0];

        return $base.'?'.implode('&', $pairs).(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }

    public function trackingRate(int $count): string
    {
        return number_format($this->sent_count ? $count / $this->sent_count * 100 : 0, 1, ',', ' ').' %';
    }

    public function statusLabel(): string
    {
        return self::labels()[$this->status] ?? 'À vérifier';
    }

    public static function labels(): array
    {
        return ['draft' => 'Brouillon', 'scheduled' => 'Programmé', 'queued' => 'En attente', 'sending' => 'En cours', 'completed' => 'Terminée', 'completed_with_errors' => 'Terminée avec erreurs', 'failed' => 'Terminée avec erreurs'];
    }
}
