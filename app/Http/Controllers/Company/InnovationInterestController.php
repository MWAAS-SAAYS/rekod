<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreInterestRequest;
use App\Models\Innovation;
use App\Models\InnovationAccessRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

final class InnovationInterestController extends Controller
{
    /**
     * Express interest / request official university legal channel for an innovation.
     */
    public function store(StoreInterestRequest $request, Innovation $innovation): RedirectResponse|JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyProfile = $user->companyProfile;

        if ($companyProfile === null) {
            return back()->withErrors(['error' => 'Corporate profile not initialized.']);
        }

        // Prevent duplicate interest submissions
        $existingRequest = InnovationAccessRequest::query()
            ->where('company_profile_id', $companyProfile->id)
            ->where('innovation_id', $innovation->id)
            ->first();

        if ($existingRequest !== null) {
            $message = 'An official access request is already in progress for this innovation.';

            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($companyProfile, $innovation, $validated): void {
            InnovationAccessRequest::create([
                'company_profile_id' => $companyProfile->id,
                'innovation_id' => $innovation->id,
                'proposed_scope' => $validated['proposed_scope'],
                'scout_message' => $validated['message'],
                'status' => InnovationAccessRequest::STATUS_PENDING_LIAISON,
            ]);
        });

        $successMessage = 'Interest registered successfully. The University Liaison Office has been notified.';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $successMessage,
            ], 201);
        }

        return back()->with('status', $successMessage);
    }
}
