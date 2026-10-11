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
        .verify-btn {
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
        .verify-btn:hover {
            background: linear-gradient(90deg, #0ea5e9 0%, #2563eb 100%);
            transform: translateY(-2px) scale(1.04);
        }
        .logout-link {
            color: #2563eb;
            font-weight: 500;
            text-decoration: underline;
            transition: color 0.2s;
        }
        .logout-link:hover {
            color: #0ea5e9;
        }
        @media (max-width: 640px) {
            .full-form { padding: 1.2rem !important; }
            .logo-pertamina { width: 80px; }
        }
    </style>

    <div class="full-form-container">
        <div class="full-form w-full space-y-5">
            <!-- Logo -->
            <img src="{{ asset('images/PPN2.png') }}" alt="Logo Pertamina" class="logo-pertamina">

            <!-- Title -->
            <h2 class="form-title">{{ __('Verifikasi Email') }}</h2>

            <div class="mb-4 text-sm text-gray-600 text-center">
                {{ __('Terima kasih telah mendaftar di sistem CCTV Monitoring! Sebelum dapat mengakses dashboard, silakan verifikasi alamat email Anda dengan mengklik tautan verifikasi yang telah kami kirimkan ke email Anda. Jika Anda tidak menerima email, silakan periksa folder spam atau minta tautan verifikasi baru.') }}
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 font-medium text-sm text-green-600 text-center">
                    {{ __('Tautan verifikasi baru telah berhasil dikirim ke alamat email Anda. Silakan cek inbox atau folder spam email Anda.') }}
                </div>
            @endif

            <div class="mt-4 flex items-center justify-between w-full">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <div>
                        <button type="submit" class="verify-btn">
                            {{ __('Kirim Ulang Email Verifikasi') }}
                        </button>
                    </div>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="logout-link text-sm">
                        {{ __('Keluar') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
