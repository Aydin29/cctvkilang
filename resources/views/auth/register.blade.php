<x-guest-layout>
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
            max-width: 520px;
            background: rgba(255,255,255,0.92);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(31,38,135,0.18);
            padding: 2.5rem 2rem 2rem;
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
        .form-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #1e293b;
            margin-bottom: 18px;
            text-align: center;
        }
        .register-btn {
            background: linear-gradient(90deg, #2563eb 0%, #0ea5e9 100%);
            color: #fff;
            font-weight: 700;
            border-radius: 8px;
            padding: 0.7rem 2.2rem;
            box-shadow: 0 2px 8px rgba(37,99,235,0.12);
            border: none;
            transition: background 0.2s, transform 0.2s;
            font-size: 1.1rem;
        }
        .register-btn:hover {
            background: linear-gradient(90deg, #0ea5e9 0%, #2563eb 100%);
            transform: translateY(-2px) scale(1.04);
        }
        .register-link {
            color: #2563eb;
            font-weight: 500;
            text-decoration: underline;
            transition: color 0.2s;
        }
        .register-link:hover {
            color: #0ea5e9;
        }
        @media (max-width: 640px) {
            .full-form { padding: 1.2rem !important; }
            .logo-pertamina { width: 80px; }
        }
    </style>

    <div class="full-form-container">
        <form method="POST" action="{{ route('register') }}" class="full-form w-full space-y-5">
            @csrf

            <img src="{{ asset('images/PPN2.png') }}" alt="Logo Pertamina" class="logo-pertamina">

            <!-- Name -->
            <div class="w-full">
                <x-input-label for="name" :value="__('Full Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="w-full">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="w-full">
                <x-input-label for="password" :value="__('Password')" />
                <div class="relative">
                    <x-text-input id="password" class="block mt-1 w-full pr-10" type="password" name="password" required autocomplete="new-password" />
                    <button type="button" id="togglePassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="w-full">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <div class="relative">
                    <x-text-input id="password_confirmation" class="block mt-1 w-full pr-10" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <button type="button" id="togglePasswordConfirm" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg id="eyeIconConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
            <script>
                function togglePasswordVisibility(inputId, iconId) {
                    const input = document.getElementById(inputId);
                    const eyeIcon = document.getElementById(iconId);
                    if (input.type === 'password') {
                        input.type = 'text';
                        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
                    } else {
                        input.type = 'password';
                        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
                    }
                }
                document.getElementById('togglePassword').addEventListener('click', function(e) {
                    e.preventDefault();
                    togglePasswordVisibility('password', 'eyeIcon');
                });
                document.getElementById('togglePasswordConfirm').addEventListener('click', function(e) {
                    e.preventDefault();
                    togglePasswordVisibility('password_confirmation', 'eyeIconConfirm');
                });
            </script>

            <!-- Actions -->
            <div class="w-full flex items-center justify-between mt-4">
                <a class="register-link text-sm" href="{{ route('login') }}">
                    {{ __('Already have an account? Sign in') }}
                </a>

                <button type="submit" class="register-btn">
                    {{ __('Register') }}
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
