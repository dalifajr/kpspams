@php($title = 'Daftar Akun')
@extends('layouts.app')

@section('content')
    @php($areas = $areas ?? collect())
    <div class="login-screen register-screen">
        <div class="login-card register-card">
            <div class="login-header">
                <div class="logo-circle">
                    <span class="material-symbols-rounded">water_drop</span>
                </div>
                <div class="login-meta">
                    <p class="app-name">MeterPAMS Android</p>
                    <p class="app-version">Formulir Registrasi</p>
                    <p class="app-desc">Lengkapi data berikut untuk mengajukan akun.</p>
                </div>
            </div>
            <h1 class="login-title">Daftar Akun</h1>
            <form action="{{ route('register.store') }}" method="POST" enctype="multipart/form-data" class="user-form__grid">
                @csrf
                <label class="form-field {{ $errors->has('avatar') ? 'has-error' : '' }}">
                    <span>Foto Profil (opsional)</span>
                    <input type="file" name="avatar" accept="image/*">
                    @error('avatar')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
                <label class="form-field {{ $errors->has('name') ? 'has-error' : '' }}">
                    <span>Nama Lengkap</span>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
                <label class="form-field {{ $errors->has('email') ? 'has-error' : '' }}">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
                <label class="form-field {{ $errors->has('phone_number') ? 'has-error' : '' }}">
                    <span>Nomor HP</span>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" required>
                    @error('phone_number')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
                <label class="form-field {{ $errors->has('password') ? 'has-error' : '' }}">
                    <span>Password</span>
                    <input type="password" name="password" required>
                    @error('password')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
                <label class="form-field">
                    <span>Konfirmasi Password</span>
                    <input type="password" name="password_confirmation" required>
                </label>
                <label class="form-field {{ $errors->has('area_id') ? 'has-error' : '' }}">
                    <span>Area</span>
                    <select name="area_id" required>
                        <option value="" disabled {{ old('area_id') ? '' : 'selected' }}>Pilih area</option>
                        @foreach ($areas as $areaOption)
                            @php($petugasNames = $areaOption->petugas->pluck('name')->implode(', '))
                            <option value="{{ $areaOption->id }}" @selected((string) old('area_id') === (string) $areaOption->id)>
                                {{ $areaOption->name }}{{ $petugasNames ? ' — ' . $petugasNames : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('area_id')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
                <label class="form-field">
                    <span>Alamat Singkat</span>
                    <input type="text" name="address_short" value="{{ old('address_short') }}" placeholder="Contoh: Jl. Melati No.12">
                </label>
                <div class="form-actions">
                    <a href="{{ route('login') }}" class="btn-secondary light">Kembali</a>
                    <button type="submit" class="btn-primary">Kirim Pengajuan</button>
                </div>
            </form>
            <div class="register-info">
                <p>Data akan ditinjau oleh admin. Anda akan menerima notifikasi setelah akun aktif.</p>
            </div>
        </div>
    </div>
@endsection
