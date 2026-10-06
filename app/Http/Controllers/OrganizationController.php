<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function showForEmployer(Request $request)
    {
        abort_unless($request->user()->role === 'employer', 403);

        $organization = $request->user()->employerProfile?->organization;

        return view('employer.organization', compact('organization'));
    }

    public function search(Request $request)
    {
        $validated = $request->validate(['search' => ['required', 'string', 'min:2', 'max:255']]);
        $term = Organization::normalizeName($validated['search']);

        return response()->json([
            'organizations' => Organization::whereRaw('LOWER(name) LIKE ?', ['%'.$term.'%'])
                ->orderBy('name')->limit(20)->get(['id', 'name']),
        ]);
    }
}
