<?php

namespace App\Http\Controllers;

use App\Models\OnlineMeeting;
use App\Services\JitsiTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnlineMeetingController extends Controller
{
    public function join(Request $request, OnlineMeeting $meeting, JitsiTokenService $tokenService)
    {
        $user = Auth::user();
        $jitsiDomain = $meeting->jitsi_domain ?: config('services.jitsi.domain');
        $jaasAppId = config('services.jitsi.jaas_app_id');
        $redirectRoute = $user->role === 'admin'
            ? route('admin.online-meetings')
            : ($user->role === 'tutor' ? route('tutor.online-meetings') : route('client.online-meetings'));

        abort_unless($meeting->isParticipant($user), 403);

        if (in_array($meeting->status, ['cancelled', 'completed'], true)) {
            return redirect()->to($redirectRoute)->with('error', 'This class session is closed.');
        }

        if (now()->lt($meeting->starts_at)) {
            return redirect()->to($redirectRoute)->with('error', 'This class session is not open yet.');
        }

        if ($meeting->ends_at && now()->gt($meeting->ends_at->copy()->addMinutes(10))) {
            $meeting->update(['status' => 'completed']);
            $meeting->attendances()
                ->where('attendance_status', 'scheduled')
                ->update(['attendance_status' => 'missed']);

            return redirect()->to($redirectRoute)->with('error', 'This class session has ended.');
        }

        $attendance = $meeting->attendances()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'role' => $user->role,
                'attendance_status' => 'scheduled',
            ]
        );

        if ($attendance->attendance_status !== 'attended') {
            $attendance->update([
                'attendance_status' => 'attended',
                'joined_at' => now(),
            ]);
        }

        if ($meeting->status === 'scheduled' && now()->greaterThanOrEqualTo($meeting->starts_at)) {
            $meeting->update(['status' => 'live']);
        }

        $privileges = data_get($meeting->jitsi_options, 'privileges', []);
        $isModerator = $user->role === 'admin'
            || ($user->role === 'tutor' && data_get($privileges, 'tutor', 'participant') === 'moderator');

        $jwt = $tokenService->generate($user, $meeting, $isModerator);
        $isJaas = str_contains(strtolower((string) $jitsiDomain), '8x8.vc') && filled($jaasAppId);

        if ($isJaas && blank($jwt)) {
            return redirect()->to($redirectRoute)->with('error', 'Unable to initialize 8x8 class session token. Check JaaS APP_ID/KID/private key.');
        }

        $roomName = $meeting->jitsi_room;
        if ($isJaas) {
            // JaaS room format must be "<appId>/<roomSlug>" (single slash only).
            $roomSlug = preg_replace('/[^A-Za-z0-9._-]/', '-', str_replace('/', '-', $roomName));
            $roomName = trim($jaasAppId, '/') . '/' . ltrim((string) $roomSlug, '-');
        }

        return view('meetings.join', [
            'meeting' => $meeting->fresh(['client', 'tutor']),
            'user' => $user,
            'jitsiDomain' => $jitsiDomain,
            'appName' => config('services.jitsi.app_name', config('app.name')),
            'jwt' => $jwt,
            'isModerator' => $isModerator,
            'roomName' => $roomName,
            'redirectRoute' => $redirectRoute,
            'jaasAppId' => $jaasAppId,
        ]);
    }
}
