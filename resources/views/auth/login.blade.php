@php($title = 'Masuk')
@extends('layouts.app')

@section('content')
    @php($branding = config('kpspams.branding'))
    <div class="login-screen">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-circle">
                    <span class="material-symbols-rounded">water_drop</span>
                </div>
                <div class="login-meta">
                    <p class="app-name">MeterPAMS Android</p>
                    <p class="app-version">ver. {{ $branding['app_version'] ?? '1.0.0' }}</p>
                    <p class="app-desc">Aplikasi Pengelola Air Bersih Swadaya Masyarakat</p>
                </div>
            </div>
            <h1 class="login-title">Selamat Datang</h1>
            <form action="{{ route('login.attempt') }}" method="POST" class="login-form">
                @csrf
                <label class="input-field">
                    <span class="material-symbols-rounded">phone_in_talk</span>
                    <input
                        type="text"
                        name="phone_number"
                        value="{{ old('phone_number') }}"
                        placeholder="Nomor HP"
                        autofocus
                        required
                    >
                </label>
                <label class="input-field password">
                    <span class="material-symbols-rounded">lock</span>
                    <input
                        type="password"
                        name="password"
                        placeholder="Password"
                        required
                    >
                    <button type="button" class="toggle-password" data-target="password">
                        <span class="material-symbols-rounded">visibility</span>
                    </button>
                </label>
                @error('phone_number')
                    <p class="form-error">{{ $message }}</p>
                @enderror
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
                @if (session('status'))
                    <p class="form-info">{{ session('status') }}</p>
                @endif
                <button type="submit" class="btn-primary">
                    <span>Sign In</span>
                    <span class="material-symbols-rounded">arrow_forward</span>
                </button>
            </form>
            <div class="login-footer">
                <a href="#" class="link">Reset Ulang Password</a>
                <a href="{{ route('register') }}" class="link">Daftar Akun</a>
            </div>
            <div class="login-info">
                <small>Informasi Aplikasi</small>
                <a href="{{ $branding['support_whatsapp_link'] }}" target="_blank">{{ $branding['support_whatsapp'] }}</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.toggle-password').forEach((button) => {
            button.addEventListener('click', () => {
                const input = button.closest('.input-field').querySelector('input');
                const icon = button.querySelector('.material-symbols-rounded');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.textContent = 'visibility_off';
                } else {
                    input.type = 'password';
                    icon.textContent = 'visibility';
                }
            });
        });
    </script>
@endpush
