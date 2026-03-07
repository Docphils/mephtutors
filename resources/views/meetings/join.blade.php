<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $meeting->title }} | {{ config('app.name', 'MephEd') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-950 text-slate-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <header class="px-4 py-3 bg-cyan-800 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold">{{ $meeting->title }}</h1>
                <p class="text-xs text-slate-400">
                    {{ $meeting->starts_at?->format('M d, Y h:i A') }} |
                    {{ $meeting->ends_at?->format('M d, Y h:i A') ?? 'Open session' }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if ($isModerator)
                    <button id="end-session-btn" type="button"
                        class="text-xs bg-rose-600 hover:bg-rose-700 px-3 py-2 rounded-lg font-bold">
                        End Session For Everyone
                    </button>
                @endif
                <a href="{{ $redirectRoute }}" class="text-xs bg-cyan-600 hover:bg-cyan-700 px-3 py-2 rounded-lg font-bold">
                    Back to Sessions
                </a>
            </div>
        </header>

        <div class="p-2 md:p-4 flex-1">
            <div id="jitsi-container"
                class="w-full h-[calc(100vh-88px)] rounded-2xl overflow-hidden border border-slate-800"></div>
            <div id="jitsi-error"
                class="hidden mt-4 p-3 rounded-lg border border-rose-800 bg-rose-950/50 text-rose-200 text-sm"></div>
        </div>
    </div>

    <script>
        const container = document.querySelector('#jitsi-container');
        const domain = @json($jitsiDomain);
        const roomName = @json($roomName);
        const displayName = @json($user->name);
        const email = @json($user->email);
        const opts = @json($meeting->jitsi_options ?? []);
        const jwt = @json($jwt);
        const redirectRoute = @json($redirectRoute);
        const jaasAppId = @json($jaasAppId);
        const endSessionUrl = @json($endSessionUrl);
        const isModerator = @json((bool) $isModerator);
        const errorBox = document.getElementById('jitsi-error');
        const endSessionBtn = document.getElementById('end-session-btn');
        let jitsiApi = null;
        let endNotified = false;

        const showError = (message) => {
            if (!errorBox) return;
            errorBox.textContent = message;
            errorBox.classList.remove('hidden');
        };

        const notifySessionEnded = () => {
            if (!isModerator || endNotified || !endSessionUrl) {
                return;
            }
            endNotified = true;

            fetch(endSessionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    source: 'moderator-client'
                }),
                keepalive: true
            }).catch(() => {});
        };

        const script = document.createElement('script');
        const isJaas = domain.toLowerCase().includes('8x8.vc') && !!jaasAppId;
        script.src = isJaas ? `https://${domain}/${jaasAppId}/external_api.js` : `https://${domain}/external_api.js`;
        script.onerror = () => {
            showError('Unable to load session interface script from provider.');
        };
        script.onload = () => {
            const api = new JitsiMeetExternalAPI(domain, {
                roomName,
                parentNode: container,
                jwt: jwt || undefined,
                userInfo: {
                    displayName,
                    email
                },
                configOverwrite: {
                    prejoinPageEnabled: true,
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                    disableDeepLinking: true,
                    fileRecordingsEnabled: @json((bool) $meeting->recording_enabled),
                    ...((opts.configOverwrite) || {})
                },
                interfaceConfigOverwrite: {
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    DEFAULT_BACKGROUND: '#020617',
                    TOOLBAR_BUTTONS: [
                        'microphone',
                        'camera',
                        'chat',
                        'desktop',
                        'tileview',
                        'participants-pane',
                        'raisehand',
                        'recording',
                        'hangup'
                    ],
                    ...((opts.interfaceConfigOverwrite) || {})
                }
            });
            jitsiApi = api;

            // Prevent Jitsi post-call landing screens by redirecting immediately.
            api.addListener('readyToClose', () => {
                notifySessionEnded();
                window.location.href = redirectRoute;
            });
            api.addListener('errorOccurred', (payload) => {
                const msg = payload?.name || payload?.type || 'Unknown session error';
                showError(`Session failed to initialize: ${msg}`);
            });

            window.addEventListener('beforeunload', () => {
                api.dispose();
            });
        };

        if (endSessionBtn) {
            endSessionBtn.addEventListener('click', () => {
                if (!jitsiApi || !isModerator) {
                    return;
                }

                const confirmed = window.confirm('End this session for everyone?');
                if (!confirmed) {
                    return;
                }

                try {
                    notifySessionEnded();
                    jitsiApi.executeCommand('endConference');
                } catch (error) {
                    showError('Unable to end the session from this client.');
                }
            });
        }

        document.body.appendChild(script);
    </script>
</body>

</html>
