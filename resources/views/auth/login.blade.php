@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-2xl font-bold mb-6">{{ __('Login') }}</h2>
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-control mb-4">
                        <label class="label" for="email">
                            <span class="label-text">{{ __('Email Address') }}</span>
                        </label>
                        <input id="email" 
                               type="email" 
                               class="input input-bordered w-full @error('email') input-error @enderror" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autocomplete="email" 
                               autofocus>
                        
                        @error('email')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control mb-4">
                        <label class="label" for="password">
                            <span class="label-text">{{ __('Password') }}</span>
                        </label>
                        <input id="password" 
                               type="password" 
                               class="input input-bordered w-full @error('password') input-error @enderror" 
                               name="password" 
                               required 
                               autocomplete="current-password">
                        
                        @error('password')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control mb-6">
                        <label class="label cursor-pointer justify-start gap-2">
                            <input type="checkbox" 
                                   class="checkbox checkbox-primary" 
                                   name="remember" 
                                   id="remember" 
                                   {{ old('remember') ? 'checked' : '' }}>
                            <span class="label-text">{{ __('Remember Me') }}</span>
                        </label>
                    </div>

                    <div class="form-control">
                        <button type="submit" class="btn btn-primary w-full mb-3">
                            {{ __('Login') }}
                        </button>

                        @if (Route::has('password.request'))
                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                {{ __('Forgot Your Password?') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
