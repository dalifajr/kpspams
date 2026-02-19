@php($title = 'Tambah User')
@php($bodyClass = 'page-user-admin')
@extends('layouts.app')

@section('content')
    <div class="user-detail-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('menu.user') }}" class="chip-back" aria-label="Kembali ke daftar user">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>Tambah User</h1>
                    </div>
                </div>
                <div class="toolbar-actions">
                    <button type="button" class="icon-button ghost" data-toggle-panel="create-info" aria-controls="panel-create-info" aria-expanded="false">
                        <span class="material-symbols-rounded">info</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="collapsible-panel" data-panel="create-info" id="panel-create-info">
            <div class="panel-header">
                <p>Panduan Input</p>
                <button type="button" class="panel-close" data-panel-close="create-info">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <p class="muted">Pastikan data pelanggan yang dimasukkan sudah lengkap agar tidak perlu pengeditan ulang setelah akun aktif.</p>
        </div>

        <div class="user-detail-card">
            <div class="card-header">
                <div>
                    <p class="eyebrow">Formulir</p>
                    <h2>Data Pelanggan</h2>
                </div>
            </div>
            <form action="{{ route('menu.user.store') }}" method="POST" enctype="multipart/form-data" class="user-form__grid">
                @csrf
                <label class="form-field {{ $errors->has('avatar') ? 'has-error' : '' }}">
                    <span>Foto Profil</span>
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
                    <input type="text" name="password" value="{{ old('password', \Illuminate\Support\Str::random(6)) }}" required>
                    @error('password')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
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
                    <a href="{{ route('menu.user') }}" class="btn-secondary light">Batal</a>
                    <button type="submit" class="btn-primary">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
    <x-bottom-nav />
@endsection
