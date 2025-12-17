@extends('layouts.app')

@section('content')
<div class="flex justify-center items-center min-h-[80vh]">
    <div class="w-full max-w-md">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-2xl font-bold mb-6 justify-center">{{ __('Reset Password') }}</h2>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="form-control w-full mb-4">
                        <label class="label" for="email">
                            <span class="label-text">{{ __('Email Address') }}</span>
                        </label>
                        <input id="email" type="email" class="input input-bordered w-full @error('email') input-error @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control w-full mb-4">
                        <label class="label" for="password">
                            <span class="label-text">{{ __('Password') }}</span>
                        </label>
                        <input id="password" type="password" class="input input-bordered w-full @error('password') input-error @enderror" name="password" required autocomplete="new-password">
                        @error('password')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control w-full mb-6">
                        <label class="label" for="password-confirm">
                            <span class="label-text">{{ __('Confirm Password') }}</span>
                        </label>
                        <input id="password-confirm" type="password" class="input input-bordered w-full" name="password_confirmation" required autocomplete="new-password">
                    </div>

                    <div class="form-control">
                        <button type="submit" class="btn btn-primary w-full">
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
