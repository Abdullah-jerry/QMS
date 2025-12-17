<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="qms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QMS Display {{ $department ? '- ' . $department->name : '' }}</title>
    
    @vite(['resources/css/app.css'])
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
    /* Light, soft background for the whole display */
    body {
        background-color: #f5f7fa;   /* very light gray‑blue */
        color: #2d2d2d;              /* dark‑gray text for readability */
        overflow: hidden;
        font-family: 'Inter', system-ui, sans-serif;
    }

    /* Keep the pulse glow for the main token card – just a softer pink */
    @keyframes pulse-glow {
        0%   { box-shadow: 0 0 0 0 rgba(255, 99, 71, 0.3); }   /* tomato */
        70%  { box-shadow: 0 0 0 20px rgba(255, 99, 71, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 99, 71, 0); }
    }

    /* Simple blink for the “calling” badge */
    @keyframes blinker {
        50% { opacity: 0; }
    }

    .pulse-animation {
        animation: pulse-glow 2s infinite;
    }

    .blink {
        animation: blinker 1s linear infinite;
    }
</style>
</head>
<body class="text-white font-sans">
    <div class="h-screen flex flex-col p-6 gap-4">
        
        <!-- TOP: Current Calling Token (Compact, Horizontal) -->
        <div id="current-call-container" class="h-[22vh]">
            @if($mainToken)
                <div id="main-token-card" class="bg-gradient-to-r from-cyan-600 via-blue-600 to-purple-600 rounded-3xl shadow-2xl w-full h-full flex items-center justify-around px-16 border-2 border-cyan-400 pulse-animation">
                    <!-- Left: Token Number -->
                    <div class="flex flex-col items-center gap-2">
                        <div class="text-sm text-cyan-100 uppercase tracking-widest font-semibold">Token Number</div>
                        <div id="main-token-number" class="text-7xl font-black text-white leading-none drop-shadow-2xl">
                            {{ $mainToken->token_number }}
                        </div>
                    </div>
                    
                    <!-- Center: Status Badge -->
                    <div class="flex items-center">
                        <span id="main-status" class="badge bg-white text-cyan-600 border-0 text-xl px-10 py-7 font-bold {{ $mainToken->status == 'called' ? 'blink' : '' }} shadow-xl">
                            {{ $mainToken->status == 'called' ? '🔔 CALLING...' : '✓ NOW SERVING' }}
                        </span>
                    </div>
                    
                    <!-- Right: Counter -->
                    <div class="flex flex-col items-center gap-2">
                        <div class="text-sm text-cyan-100 uppercase tracking-widest font-semibold">Counter</div>
                        <div id="main-counter" class="text-7xl font-black text-white leading-none drop-shadow-2xl">
                            {{ str_replace('Counter ', '', $mainToken->counter->name) }}
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-gradient-to-r from-slate-700 via-slate-600 to-slate-700 rounded-3xl shadow-2xl w-full h-full flex items-center justify-center border-2 border-slate-500">
                    <div class="text-center">
                        <div class="text-5xl font-black text-slate-300 mb-3">⏳ WAITING</div>
                        <div class="text-lg text-slate-400 uppercase tracking-wider">Please wait for your number</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- BOTTOM: Split View (Video Left | Recent Calls Right) -->
        <div class="flex-1 grid grid-cols-12 gap-4">
            
            <!-- LEFT: Company Video -->
            <div class="col-span-7">
                <div class="bg-[#16213e]/80 rounded-2xl shadow-xl w-full h-full flex items-center justify-center overflow-hidden">
                    <video id="companyVideo" autoplay muted loop playsinline>
                        <source src="/videos/company.mp4" type="video/mp4">
                        <source src="/videos/company.webm" type="video/webm">
                        <div class="text-center p-8">
                            <i class="bi bi-play-circle text-6xl text-gray-600 mb-4"></i>
                            <p class="text-gray-400 text-xl">Company Video</p>
                            <p class="text-gray-500 text-sm mt-2">Place your video at: public/videos/company.mp4</p>
                        </div>
                    </video>
                </div>
            </div>

            <!-- RIGHT: Recent Calls -->
            <div class="col-span-5 flex flex-col">
                <h2 class="text-2xl font-bold text-white mb-4 pb-3 border-b-2 border-cyan-400/30">
                    📋 Recent Calls
                </h2>
                
                <div id="history-container" class="space-y-3 overflow-y-auto flex-1">
                    @foreach($historyTokens as $token)
                    <div class="bg-gradient-to-r from-slate-800 to-slate-700 rounded-xl p-4 border-l-4 {{ $token->status == 'completed' ? 'border-gray-500 opacity-70' : ($token->status == 'serving' ? 'border-cyan-400' : 'border-blue-400') }} flex justify-between items-center hover:shadow-lg transition-all">
                        <div>
                            <div class="text-3xl font-bold text-white">
                                {{ $token->token_number }}
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                {{ $token->department->name }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xl text-cyan-300 font-semibold">
                                Counter {{ str_replace('Counter ', '', $token->counter->name) }}
                            </div>
                            <div class="mt-1">
                                <span class="badge badge-sm {{ $token->status == 'completed' ? 'badge-ghost' : ($token->status == 'serving' ? 'bg-cyan-500 text-white border-0' : 'bg-blue-500 text-white border-0') }}">
                                    {{ ucfirst($token->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Subtle Audio Notification (only shows if not enabled) -->
    <div id="audioNotification" class="fixed bottom-0 left-0 right-0 bg-cyan-600/90 text-white text-center py-2 text-sm z-50 hidden">
        <i class="bi bi-volume-up"></i> Click anywhere to enable audio announcements
    </div>

    <!-- Token Popup Modal -->
<div id="token-modal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/50 backdrop-blur-md transition-opacity duration-300 opacity-0">        <div id="token-modal-content" class="bg-gradient-to-br from-cyan-600 to-blue-700 rounded-3xl p-1 shadow-2xl transform scale-0 transition-transform duration-500 cubic-bezier(0.34, 1.56, 0.64, 1) max-w-5xl w-full mx-4">
            <div class="bg-[#1a1a2e] rounded-[1.4rem] p-12 text-center border border-white/10 relative overflow-hidden">
                <!-- Background Glow -->
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-cyan-500/10 blur-3xl rounded-full pointer-events-none"></div>
                
                <div class="relative z-10">
                    <div class="text-3xl text-cyan-400 font-bold uppercase tracking-[0.2em] mb-4 animate-pulse">
                        Now Calling
                    </div>
                    
                    <div class="text-[12rem] font-black text-white leading-none mb-8 drop-shadow-[0_0_30px_rgba(6,182,212,0.5)]">
                        <span id="modal-token-number">--</span>
                    </div>
                    
                    <div class="flex items-center justify-center gap-6">
                        <div class="h-px bg-gradient-to-r from-transparent via-gray-500 to-transparent w-24"></div>
                        <div class="text-5xl font-bold text-white">
                            Counter <span id="modal-counter-number" class="text-cyan-400">--</span>
                        </div>
                        <div class="h-px bg-gradient-to-r from-transparent via-gray-500 to-transparent w-24"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/display-audio.js'])
</body>
</html>
