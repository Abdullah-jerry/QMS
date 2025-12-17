@extends('layouts.app')

@section('content')
<div class="flex justify-center items-center min-h-[60vh]">
    <div class="w-full max-w-md">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-2xl font-bold mb-6 justify-center">{{ __('Verify Your Email Address') }}</h2>

                @if (session('resent'))
                    <div class="alert alert-success mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ __('A fresh verification link has been sent to your email address.') }}</span>
                    </div>
                @endif

                <p class="mb-4">{{ __('Before proceeding, please check your email for a verification link.') }}</p>
                <p class="mb-4">
                    {{ __('If you did not receive the email') }},
                </p>
                
                <form class="inline" method="POST" action="{{ route('verification.resend') }}">
                    @csrf
                    <button type="submit" class="btn btn-link p-0 h-auto min-h-0 normal-case text-base font-normal align-baseline">
                        {{ __('click here to request another') }}
                    </button>.
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
