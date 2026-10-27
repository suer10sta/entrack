<?php

namespace App\Services;

use App\Models\CrmCampaign;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CrmCampaignAssets
{
    private array $created = [];

    public const IMAGES = 'jpg,jpeg,png,gif,webp';

    public const DOCUMENTS = 'pdf,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,rtf,txt,csv';

    public function transaction(Closure $callback): mixed
    {
        try {
            return DB::transaction(fn () => $callback($this));
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($this->created);
            throw $exception;
        }
    }

    public function apply(Request $request, CrmCampaign $campaign, array $data): array
    {
        $media = $campaign->media ?? [];
        foreach (['image', 'attachment'] as $kind) {
            $file = $request->file($kind.'_file');
            $url = $data[$kind.'_url'] ?? null;
            if ($file && $url) {
                throw ValidationException::withMessages([$kind.'_url' => 'Choisissez un fichier OU une URL.']);
            }
            if ($file || $url || $request->boolean('remove_'.$kind)) {
                $old = $media[$kind]['path'] ?? null;
                if ($file || $url) {
                    $media[$kind] = $file ? $this->store($file, $kind, $campaign->id) : $this->download($url, $kind, $campaign->id);
                } else {
                    unset($media[$kind]);
                }
                if ($old) {
                    DB::afterCommit(fn () => Storage::disk('local')->delete($old));
                }
            }
            unset($data[$kind.'_file'], $data[$kind.'_url'], $data['remove_'.$kind]);
        }

        return $data + ['media' => $media ?: null];
    }

    private function store(UploadedFile $file, string $kind, int $campaignId, ?string $url = null): array
    {
        $rules = $kind === 'image' ? ['file', 'image', 'mimes:'.self::IMAGES, 'max:2048'] : ['file', 'mimes:'.self::DOCUMENTS, 'max:10240'];
        Validator::make([$kind.'_file' => $file], [$kind.'_file' => $rules])->validate();
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = preg_replace('/[\x00-\x1f\x7f]/', '', $name);
        $path = $file->storeAs('crm-campaigns/'.$campaignId, Str::uuid().'.'.$file->guessExtension(), 'local');
        if (! $path) {
            throw ValidationException::withMessages([$kind.'_file' => 'Impossible d’enregistrer le fichier.']);
        }
        $this->created[] = $path;

        return ['path' => $path, 'name' => mb_substr($name, 0, 180), 'mime' => $file->getMimeType(), 'size' => $file->getSize(), 'source_url' => $url];
    }

    private function download(string $url, string $kind, int $campaignId): array
    {
        $field = $kind.'_url';
        $parts = parse_url($url);
        $host = $parts['host'] ?? '';
        $port = $parts['port'] ?? (($parts['scheme'] ?? '') === 'https' ? 443 : 80);
        if (! $parts || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true) || isset($parts['user']) || isset($parts['pass']) || ! in_array($port, [80, 443], true)) {
            throw ValidationException::withMessages([$field => 'Utilisez une URL HTTP(S) publique, sans identifiants.']);
        }
        $addresses = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);
        if (! $addresses || collect($addresses)->contains(fn ($ip) => ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))) {
            throw ValidationException::withMessages([$field => 'Le fichier doit être accessible sur une adresse Internet publique.']);
        }
        $temporary = tempnam(sys_get_temp_dir(), 'crm-media-');
        $sink = tempnam(sys_get_temp_dir(), 'crm-download-');
        try {
            // Pin the checked address and disable redirects/proxies to prevent private-network requests.
            $limit = ($kind === 'image' ? 2048 : 10240) * 1024;
            $response = Http::connectTimeout(5)->timeout(20)->withOptions([
                'sink' => $sink, 'allow_redirects' => false, 'proxy' => '',
                'curl' => [CURLOPT_RESOLVE => [$host.':'.$port.':'.$addresses[0]]],
                'progress' => function ($total, $downloaded) use ($limit) {
                    if ($total > $limit || $downloaded > $limit) {
                        throw new \RuntimeException('File too large');
                    }
                },
            ])->get($url);
            if (! $response->successful()) {
                throw new \RuntimeException('Download failed');
            }
            if ((int) $response->header('Content-Length') > $limit) {
                throw new \RuntimeException('File too large');
            }
            $body = $response->toPsrResponse()->getBody();
            $body->rewind();
            $output = fopen($temporary, 'wb');
            try {
                $size = 0;
                while (! $body->eof()) {
                    $chunk = $body->read(65536);
                    $size += strlen($chunk);
                    if ($size > $limit) {
                        throw new \RuntimeException('File too large');
                    }
                    fwrite($output, $chunk);
                }
            } finally {
                fclose($output);
                $body->close();
            }
            $name = rawurldecode(basename($parts['path'] ?? '')) ?: ($kind === 'image' ? 'image' : 'document');

            // MIME validation inspects the downloaded bytes, not the server's Content-Type.
            return $this->store(new UploadedFile($temporary, $name, null, null, true), $kind, $campaignId, $url);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([$field => 'Fichier inaccessible, format non pris en charge ou taille excessive (image : 2 Mo ; document : 10 Mo).']);
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
            if (is_file($sink)) {
                unlink($sink);
            }
        }
    }
}
