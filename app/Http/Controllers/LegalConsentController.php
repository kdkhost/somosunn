<?php

namespace App\Http\Controllers;

use App\Services\LegalConsentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LegalConsentController extends Controller
{
    public function __construct(
        private readonly LegalConsentService $legalConsent,
    ) {
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'accept' => ['required', 'accepted'],
        ]);

        $this->legalConsent->recordAcceptance($request->user(), $request);

        $message = 'Termos de LGPD aceitos com sucesso.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'accepted' => true,
                'version' => $this->legalConsent->currentVersion(),
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
