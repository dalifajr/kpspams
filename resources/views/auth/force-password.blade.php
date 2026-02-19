@php($title = 'Perbarui Kata Sandi')
@extends('layouts.app')

@section('content')
    <div class="login-screen">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-circle">
                    <span class="material-symbols-rounded">lock_reset</span>
                </div>
                <div class="login-meta">
                    <p class="app-name">MeterPAMS Android</p>
                    <p class="app-version">Wajib Ganti Password</p>
                </div>
            </div>
            <h1 class="login-title">Perbarui Kata Sandi</h1>
            <p class="muted" style="margin-bottom: 1.5rem;">Untuk alasan keamanan, silakan buat kata sandi baru sebelum melanjutkan.</p>
            <form action="{{ route('password.force.update') }}" method="POST" class="login-form">
                @csrf
                <label class="input-field password">
                    <span class="material-symbols-rounded">lock</span>
                    <input
                        type="password"
                        name="password"
                        placeholder="Kata sandi baru"
                        required
                        autofocus
                    >
                </label>
                <label class="input-field password">
                    <span class="material-symbols-rounded">lock_clock</span>
                    <input
                        type="password"
                        name="password_confirmation"
                        placeholder="Ulangi kata sandi"
                        required
                    >
                </label>
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
                <button type="submit" class="btn-primary" style="margin-top: 1rem;">
                    <span>Simpan Kata Sandi</span>
                    <span class="material-symbols-rounded">done</span>
                </button>
            </form>
        </div>
    </div>
@endsection
