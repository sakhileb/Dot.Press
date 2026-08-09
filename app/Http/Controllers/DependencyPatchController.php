<?php

namespace App\Http\Controllers;

use App\Jobs\ApplyDependencyPatchJob;
use App\Models\DependencyPatchProposal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DependencyPatchController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('DependencyPatches/Index', [
            'pending' => DependencyPatchProposal::where('status', 'pending_approval')->with('reviewer')->latest()->get(),
            'reviewed' => DependencyPatchProposal::whereIn('status', ['approved', 'rejected', 'applied', 'failed'])
                ->with('reviewer')->latest()->limit(20)->get(),
        ]);
    }

    public function approve(DependencyPatchProposal $proposal): RedirectResponse
    {
        if ($proposal->status !== 'pending_approval') {
            return back()->withErrors(['status' => 'Only a pending proposal can be approved.']);
        }

        $proposal->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        ApplyDependencyPatchJob::dispatch($proposal);

        return back()->with('status', 'Patch approved and queued.');
    }

    public function reject(DependencyPatchProposal $proposal, Request $request): RedirectResponse
    {
        if ($proposal->status !== 'pending_approval') {
            return back()->withErrors(['status' => 'Only a pending proposal can be rejected.']);
        }

        $validated = $request->validate(['reason' => 'required|string']);

        $proposal->update([
            'status' => 'rejected',
            'rejected_reason' => $validated['reason'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'Proposal rejected.');
    }
}
