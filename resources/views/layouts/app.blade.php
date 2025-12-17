<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" data-theme="qms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'QMS') }} - @yield('title')</title>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Choices.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        .token-card {
            transition: all 0.3s ease;
        }
        .token-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-base-200">
    <div class="drawer lg:drawer-open">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />
        
        <!-- Main Content -->
        <div class="drawer-content flex flex-col">
            <!-- Mobile Navbar -->
            <div class="navbar bg-gradient-to-r from-primary to-secondary text-white lg:hidden">
                <div class="flex-none">
                    <label for="sidebar-drawer" class="btn btn-square btn-ghost">
                        <i class="bi bi-list text-2xl"></i>
                    </label>
                </div>
                <div class="flex-1">
                    <span class="text-xl font-bold">QMS</span>
                </div>
            </div>
            
            <!-- Page Content -->
            <main class="p-4 lg:p-6">
                @yield('content')
            </main>
        </div>
        
        <!-- Sidebar -->
        <div class="drawer-side">
            <label for="sidebar-drawer" class="drawer-overlay"></label>
            <aside class="min-h-screen w-64 bg-gradient-to-b from-primary to-secondary text-white">
                <!-- Logo/Brand -->
                <div class="p-6 text-center border-b border-white/10">
                    <h2 class="text-2xl font-bold">QMS</h2>
                    <p class="text-sm text-white/70 mt-1">Queue Management</p>
                </div>
                
                <!-- User Info -->
                @if(auth()->check())
                <div class="p-4 border-b border-white/10">
                    <div class="text-xs text-white/60">Logged in as:</div>
                    <div class="font-semibold mt-1">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-white/70 mt-1">{{ auth()->user()->getRoleNames()->first() ?? '' }}</div>
                </div>
                @endif
                
                <!-- Navigation Menu -->
                <ul class="menu p-4 space-y-1">
                    <li>
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center gap-3 {{ request()->routeIs('dashboard') ? 'active bg-white/20' : 'hover:bg-white/10' }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>{{ __('messages.dashboard') }}</span>
                        </a>
                    </li>
                    
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <li>
                            <a href="{{ route('admin.users.index') }}" 
                               class="flex items-center gap-3 {{ request()->routeIs('admin.users.*') ? 'active bg-white/20' : 'hover:bg-white/10' }}">
                                <i class="bi bi-people"></i>
                                <span>{{ __('messages.users') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.departments.index') }}" 
                               class="flex items-center gap-3 {{ request()->routeIs('admin.departments.*') ? 'active bg-white/20' : 'hover:bg-white/10' }}">
                                <i class="bi bi-building"></i>
                                <span>{{ __('messages.department') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.counters.index') }}" 
                               class="flex items-center gap-3 {{ request()->routeIs('admin.counters.*') ? 'active bg-white/20' : 'hover:bg-white/10' }}">
                                <i class="bi bi-display"></i>
                                <span>{{ __('messages.counter') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.services.index') }}" 
                               class="flex items-center gap-3 {{ request()->routeIs('admin.services.*') ? 'active bg-white/20' : 'hover:bg-white/10' }}">
                                <i class="bi bi-gear"></i>
                                <span>{{ __('messages.service') }}</span>
                            </a>
                        </li>
                    @endif
                    
                    <li>
                        <a href="{{ route('reports.index') }}" 
                           class="flex items-center gap-3 {{ request()->routeIs('reports.*') ? 'active bg-white/20' : 'hover:bg-white/10' }}">
                            <i class="bi bi-file-earmark-bar-graph"></i>
                            <span>{{ __('messages.reports') }}</span>
                        </a>
                    </li>
                    
                    <!-- Language Switcher -->
                    <li>
                        <details>
                            <summary class="flex items-center gap-3 hover:bg-white/10">
                                <i class="bi bi-translate"></i>
                                <span>{{ __('messages.language') }}</span>
                            </summary>
                            <ul>
                                <li><a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() == 'en' ? 'active' : '' }}">English</a></li>
                                <li><a href="{{ route('lang.switch', 'ar') }}" class="{{ app()->getLocale() == 'ar' ? 'active' : '' }}">العربية</a></li>
                            </ul>
                        </details>
                    </li>

                    <!-- Logout/Login -->
                    <li class="mt-4">
                        @auth
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 w-full hover:bg-white/10">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>{{ __('messages.logout') }}</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="flex items-center gap-3 hover:bg-white/10">
                                <i class="bi bi-box-arrow-in-right"></i>
                                <span>{{ __('messages.login') }}</span>
                            </a>
                        @endauth
                    </li>
                </ul>
            </aside>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <!-- Choices.js -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    
    <!-- SweetAlert2 Notifications -->
    <script>
        @if(session('success'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                confirmButtonColor: '#d33',
            });
        @endif

        @if(session('info'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: '{{ session('info') }}',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: '<ul class="text-start mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                confirmButtonColor: '#d33',
            });
        @endif
    </script>
    
    @stack('scripts')
</body>
</html>
