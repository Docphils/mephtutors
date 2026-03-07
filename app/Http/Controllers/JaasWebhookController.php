<?php

namespace App\Http\Controllers;

use App\Models\OnlineMeeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JaasWebhookController extends Controller
{
    public function __invoke(Request $request, ?string $token = null)
    {
        $expected = (string) env('JITSI_JAAS_WEBHOOK_TOKEN', '');
        if ($expected !== '' && $token !== $expected) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized webhook token'], 401);
        }

        $payload = $request->all();
        $event = (string) (data_get($payload, 'eventType')
            ?? data_get($payload, 'event')
            ?? data_get($payload, 'type')
            ?? '');

        $roomName = (string) (data_get($payload, 'roomName')
            ?? data_get($payload, 'room')
            ?? data_get($payload, 'conference.room')
            ?? '');

        $normalizedRoom = $this->normalizeRoomName($roomName);
        $meeting = OnlineMeeting::where('jitsi_room', $normalizedRoom)
            ->orWhere('jitsi_room', $roomName)
            ->first();

        if ($meeting) {
            $eventLower = strtolower($event);
            if (str_contains($eventLower, 'end') || str_contains($eventLower, 'destroy')) {
                $meeting->update([
                    'status' => 'completed',
                    'ended_at' => now(),
                ]);
                $meeting->attendances()
                    ->where('attendance_status', 'scheduled')
                    ->update(['attendance_status' => 'missed']);
            }

            if (str_contains($eventLower, 'start') || str_contains($eventLower, 'create')) {
                $meeting->update(['status' => 'live']);
            }
        }

        Log::info('JaaS webhook received', [
            'event' => $event,
            'room' => $roomName,
            'normalized_room' => $normalizedRoom,
            'meeting_id' => $meeting?->id,
        ]);

        return response()->json(['ok' => true]);
    }

    private function normalizeRoomName(string $roomName): string
    {
        $roomName = trim($roomName);
        $appId = (string) config('services.jitsi.jaas_app_id', '');

        if ($appId !== '' && str_starts_with($roomName, $appId . '/')) {
            return substr($roomName, strlen($appId) + 1);
        }

        return $roomName;
    }
}
