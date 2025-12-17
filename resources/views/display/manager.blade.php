<!DOCTYPE html>
<html lang="en" data-theme="qms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Display - QMS</title>
    @vite(['resources/css/app.css'])
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="text-white min-h-screen p-6">
    <div class="container mx-auto">
        <h1 class="text-4xl font-bold text-center mb-8">Manager Display</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($departments as $dept)
            <div class="card bg-white/10 backdrop-blur-md border border-white/20 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-2xl mb-4">{{ $dept->name }}</h2>
                    
                    <!-- Current Serving -->
                    @php
                        $serving = $dept->tokens->where('status', 'serving')->first();
                    @endphp
                    
                    @if($serving)
                    <div class="bg-white/20 rounded-lg p-6 text-center mb-4">
                        <div class="text-sm opacity-70 mb-2">Now Serving</div>
                        <div class="text-5xl font-bold">
                            {{ $serving->token_number }}
                            @if($serving->is_vip)
                                <span class="badge badge-warning ml-2">VIP</span>
                            @endif
                        </div>
                        <div class="text-lg mt-2 opacity-80">{{ $serving->counter->name ?? '-' }}</div>
                    </div>
                    @else
                    <div class="bg-white/20 rounded-lg p-6 text-center mb-4">
                        <div class="text-2xl opacity-50">No Active Token</div>
                    </div>
                    @endif
                    
                    <!-- Waiting List -->
                    <div class="mt-4">
                        <h3 class="font-semibold mb-3">Waiting ({{ $dept->tokens->where('status', 'waiting')->count() }})</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($dept->tokens->where('status', 'waiting')->take(10) as $token)
                            <div class="bg-white/15 px-4 py-2 rounded-lg text-lg font-semibold">
                                {{ $token->token_number }}
                                @if($token->is_vip)
                                    <span class="badge badge-warning badge-xs ml-1">VIP</span>
                                @endif
                            </div>
                            @endforeach
                            
                            @if($dept->tokens->where('status', 'waiting')->count() > 10)
                            <div class="bg-white/15 px-4 py-2 rounded-lg text-lg opacity-70">
                                +{{ $dept->tokens->where('status', 'waiting')->count() - 10 }} more
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Stats -->
                    <div class="stats stats-vertical shadow mt-4 bg-white/10">
                        <div class="stat">
                            <div class="stat-title text-white/70">Completed Today</div>
                            <div class="stat-value text-2xl text-white">{{ $dept->tokens->where('status', 'completed')->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    
    <script>
        // Auto-refresh every 5 seconds
        setInterval(() => {
            window.location.reload();
        }, 5000);
    </script>
</body>
</html>
