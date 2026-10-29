<?php

namespace App\Http\Controllers;

use App\Services\CrmCampaignUnsubscribe;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CrmCampaignUnsubscribeController extends Controller
{
    public function __invoke(Request $request, CrmCampaignUnsubscribe $unsubscribe)
    {
        $token = $request->route('token');
        $status = 200;
        $email = null;
        try {
            [$recipient, $prospect] = $unsubscribe->resolve($token);
            $email = $recipient->email;
            if ($request->isMethod('POST')) {
                $prospect = $unsubscribe->confirm($token);
            }
            $state = $prospect->unsubscribed_at ? 'done' : 'confirm';
        } catch (NotFoundHttpException) {
            $state = 'invalid';
            $status = 404;
        }

        return response()->view('crm.unsubscribe', compact('state', 'token', 'email'), $status, [
            'Cache-Control' => 'no-store, private', 'Referrer-Policy' => 'no-referrer',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
