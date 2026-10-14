<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserManualController extends Controller
{
    public function index(Request $request)
    {
        $manuals = require base_path('docs/manuals.php');
        $visible = ['general' => $manuals['general']];
        $role = $request->user()?->role;

        if ($role !== null && isset($manuals[$role])) {
            $visible[$role] = $manuals[$role];
        }

        return view('front.user-manuals', ['manuals' => $visible]);
    }

    public function download(Request $request, string $manual, string $format): BinaryFileResponse
    {
        $manuals = require base_path('docs/manuals.php');
        abort_unless(isset($manuals[$manual]) && $format === 'pdf', 404);
        abort_unless($manual === 'general' || $request->user()?->role === $manual, 403, 'This manual is not available for your account role.');

        $filename = 'FNU-Job-Placement-'.$manual.'-Manual.'.$format;
        $path = base_path('docs/manuals/'.$filename);
        abort_unless(is_file($path), 404, 'The requested user manual is not available.');

        $headers = [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
        ];

        $response = ! $request->boolean('download')
            ? response()->file($path, $headers)
            : response()->download($path, $filename, $headers);

        if (! $request->boolean('download')) {
            $response->setContentDisposition('inline', $filename);
        }

        return $response->setPrivate();
    }
}
