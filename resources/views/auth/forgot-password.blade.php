<x-guest-layout>
    <link rel="icon" type="image/png" href="{{ asset('images/logoweb.png') }}">
    <style>
        .full-form-container {
            min-height: 100vh;
            width: 100vw;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .full-form {
            width: 100%;
            max-width: 420px;
            background: rgba(255,255,255,0.92);
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            padding: 2.5rem 2rem 2rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .logo-pertamina {
            width: 220px;
            margin-bottom: 18px;
            margin-top: 10px;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.08));
        }
        .login-btn {
            background: linear-gradient(90deg, #2563eb 0%, #0ea5e9 100%);
            color: #fff;
            font-weight: 700;
            border-radius: 8px;
            padding: 0.7rem 2.2rem;
            box-shadow: 0 2px 8px 0 rgba(37,99,235,0.12);
            border: none;
            transition: background 0.2s, transform 0.2s;
            font-size: 1.1rem;
        }
        .login-btn:hover {
            background: linear-gradient(90deg, #0ea5e9 0%, #2563eb 100%);
            transform: translateY(-2px) scale(1.04);
        }
        .login-link {
            color: #2563eb;
            font-weight: 500;
            text-decoration: underline;
            transition: color 0.2s;
        }
        .login-link:hover {
            color: #0ea5e9;
        }
        @media (max-width: 640px) {
            .full-form { padding: 1.2rem !important; }
            .logo-pertamina { width: 80px; }
        }
    </style>

    <div class="full-form-container">
        <form method="POST" action="{{ route('password.email') }}" class="full-form w-full space-y-5">
            @csrf

            <!-- Logo -->
            <img src="{{ asset('images/PPN2.png') }}" alt="Logo Pertamina" class="logo-pertamina">

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Email Address -->
            <div class="w-full">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Tombol Submit -->
            <div class="flex items-center justify-end w-full mt-5">
                <button type="submit" class="login-btn">
                    {{ __('Send Email Password Reset') }}
                </button>
            </div>

            <!-- Link Back to Login -->
            <div class="w-full text-center mt-4">
                <a href="{{ route('login') }}" class="login-link text-sm">
                    {{ __('Back to Login') }}
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
