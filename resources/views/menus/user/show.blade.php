@php($title = 'Detail User')
@php($bodyClass = 'page-user-admin')
@php($areaOptions = $areas ?? collect())
@php($currentAreaId = old('area_id', $managedUser->area_id))
@php($avatarUrl = $managedUser->avatar_path ? Illuminate\Support\Facades\Storage::url($managedUser->avatar_path) : null)
@php($whatsappLink = $whatsappLink ?? null)
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
                        <h1>Detail User</h1>
                    </div>
                </div>
                <div class="toolbar-actions">
                    <button type="button" class="icon-button ghost" data-toggle-panel="contact" aria-controls="panel-contact" aria-expanded="false">
                        <span class="material-symbols-rounded">call</span>
                    </button>
                    <button type="button" class="icon-button ghost" data-toggle-panel="shortcuts" aria-controls="panel-shortcuts" aria-expanded="false">
                        <span class="material-symbols-rounded">more_vert</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="collapsible-panel" data-panel="contact" id="panel-contact">
            <div class="panel-header">
                <p>Kontak cepat</p>
                <button type="button" class="panel-close" data-panel-close="contact">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <p class="muted">Gunakan tombol WhatsApp atau telepon di bagian detail user untuk menghubungi pelanggan secara langsung.</p>
        </div>

        <div class="collapsible-panel" data-panel="shortcuts" id="panel-shortcuts">
            <div class="panel-header">
                <p>Pintasan</p>
                <button type="button" class="panel-close" data-panel-close="shortcuts">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <div class="quick-actions">
                <a href="{{ route('menu.user') }}" class="btn-secondary light">Kembali ke daftar</a>
                <a href="{{ route('menu.user.create') }}" class="btn-secondary light">Tambah user baru</a>
            </div>
        </div>

        @if (session('status'))
            <div class="alert-success">
                <span class="material-symbols-rounded">check_circle</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        <div class="user-detail-card user-detail-card--hero">
            <div class="user-card__avatar" style="--avatar-color: {{ $managedUser->isPetugas() ? '#0f9d58' : '#2563eb' }}">
                @if ($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="{{ $managedUser->name }}">
                @else
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($managedUser->name, 0, 1)) }}
                @endif
            </div>
            <div>
                <p class="user-detail__name">{{ $managedUser->name }}</p>
                <p class="user-detail__role">{{ $managedUser->isPetugas() ? 'Petugas' : 'User' }}</p>
                <p class="user-detail__address">{{ $managedUser->area ?? 'Area belum diisi' }}</p>
                <p class="user-detail__address">{{ $managedUser->address_short ?? 'Alamat belum diisi' }}</p>
                <p class="user-detail__status {{ $managedUser->isApproved() ? 'is-approved' : 'is-pending' }}">
                    Status: {{ $managedUser->isApproved() ? 'Disetujui' : 'Menunggu Persetujuan' }}
                </p>
                @if ($managedUser->isApproved() && $whatsappLink)
                    <a href="{{ $whatsappLink }}" target="_blank" class="btn-secondary">WhatsApp User</a>
                @endif
            </div>
        </div>

        @if ($managedUser->isPending())
            <div class="user-detail-card user-detail-card--warning">
                <div>
                    <p class="eyebrow">Persetujuan</p>
                    <h2>Akun Menunggu Review Admin</h2>
                    <p class="muted">Setujui akun agar user dapat login. Opsi kedua langsung membuka WhatsApp untuk menginformasikan bahwa akun aktif.</p>
                </div>
                <div class="pending-card__actions">
                    <form action="{{ route('menu.user.approve', $managedUser) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-primary">Setujui Akun</button>
                    </form>
                    <form action="{{ route('menu.user.approve', $managedUser) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="notify" value="1">
                        <button type="submit" class="btn-secondary">Setujui &amp; WhatsApp</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="user-detail-card">
            <div class="card-header">
                <div>
                    <p class="eyebrow">Ringkasan</p>
                    <h2>Informasi Kontak</h2>
                </div>
            </div>
            <dl class="user-detail__list">
                <div>
                    <dt>Email</dt>
                    <dd>{{ $managedUser->email }}</dd>
                </div>
                <div>
                    <dt>Nomor HP</dt>
                    <dd>{{ $managedUser->phone_number }}</dd>
                </div>
                <div>
                    <dt>Area</dt>
                    <dd>{{ $managedUser->area ?? 'Belum diisi' }}</dd>
                </div>
                <div>
                    <dt>Alamat Singkat</dt>
                    <dd>{{ $managedUser->address_short ?? 'Belum diisi' }}</dd>
                </div>
            </dl>
        </div>

        <div class="user-detail-card">
            <div class="card-header">
                <div>
                    <p class="eyebrow">Perbarui Data</p>
                    <h2>Edit Detail User</h2>
                </div>
            </div>
            <form action="{{ route('menu.user.update', $managedUser) }}" method="POST" enctype="multipart/form-data" class="user-form__grid">
                @csrf
                @method('PUT')
                <input type="hidden" name="form_context" value="edit">
                <input type="hidden" name="edit_user_id" value="{{ $managedUser->id }}">
                <label class="form-field {{ $errors->editUser?->has('avatar') ? 'has-error' : '' }}">
                    <span>Foto Profil</span>
                    <input type="file" name="avatar" accept="image/*">
                    @error('avatar', 'editUser')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
                <label class="form-field {{ $errors->editUser?->has('name') ? 'has-error' : '' }}">
                    <span>Nama Lengkap</span>
                    <input type="text" name="name" value="{{ old('name', $managedUser->name) }}" required>
                    @error('name', 'editUser')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
                <label class="form-field {{ $errors->editUser?->has('email') ? 'has-error' : '' }}">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email', $managedUser->email) }}" required>
                    @error('email', 'editUser')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
                <label class="form-field {{ $errors->editUser?->has('phone_number') ? 'has-error' : '' }}">
                    <span>Nomor HP</span>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $managedUser->phone_number) }}" required>
                    @error('phone_number', 'editUser')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
                <label class="form-field {{ $errors->editUser?->has('area_id') ? 'has-error' : '' }}">
                    <span>Area</span>
                    <select name="area_id" required>
                        <option value="" disabled {{ $currentAreaId ? '' : 'selected' }}>Pilih area</option>
                        @foreach ($areaOptions as $areaOption)
                            @php($petugasNames = $areaOption->petugas->pluck('name')->implode(', '))
                            <option value="{{ $areaOption->id }}" @selected((string) $currentAreaId === (string) $areaOption->id)>
                                {{ $areaOption->name }}{{ $petugasNames ? ' — ' . $petugasNames : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('area_id', 'editUser')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
                <label class="form-field">
                    <span>Alamat Singkat</span>
                    <input type="text" name="address_short" value="{{ old('address_short', $managedUser->address_short) }}" placeholder="Contoh: Jl. Melati No.12">
                </label>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>

        <div class="user-detail-card">
            <div class="card-header">
                <div>
                    <p class="eyebrow">Keamanan</p>
                    <h2>Ubah Password</h2>
                </div>
            </div>
            <form action="{{ route('menu.user.password', $managedUser) }}" method="POST" class="user-form__grid">
                @csrf
                @method('PATCH')
                <label class="form-field">
                    <span>Password Baru</span>
                    <input type="password" name="password" required>
                </label>
                <label class="form-field">
                    <span>Konfirmasi Password</span>
                    <input type="password" name="password_confirmation" required>
                </label>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Perbarui Password</button>
                </div>
            </form>
        </div>

        <div class="user-detail-card">
            <div class="card-header">
                <div>
                    <p class="eyebrow">Hak Akses</p>
                    <h2>Role Pengguna</h2>
                </div>
            </div>
            <form action="{{ route('menu.user.role', $managedUser) }}" method="POST" class="role-toggle" data-role-toggle>
                @csrf
                @method('PATCH')
                <input type="hidden" name="role" value="{{ $managedUser->isPetugas() ? \App\Models\User::ROLE_PETUGAS : \App\Models\User::ROLE_USER }}">
                <label>
                    <span>Petugas</span>
                    <input type="checkbox" {{ $managedUser->isPetugas() ? 'checked' : '' }}>
                    <span class="switch"></span>
                </label>
            </form>
        </div>

        <div class="user-detail-card user-detail-card--danger">
            <div>
                <p class="eyebrow">Aksi Berbahaya</p>
                <h2>Hapus User</h2>
                <p class="muted">User yang dihapus tidak dapat dipulihkan. Pastikan data sudah dipindahkan.</p>
            </div>
            <form action="{{ route('menu.user.destroy', $managedUser) }}" method="POST" onsubmit="return confirm('Hapus user ini secara permanen?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">
                    <span class="material-symbols-rounded">delete</span>
                    Hapus User
                </button>
            </form>
        </div>
    </div>
    <x-bottom-nav />
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-role-toggle]').forEach((form) => {
                const checkbox = form.querySelector('input[type="checkbox"]');
                const hidden = form.querySelector('input[name="role"]');
                if (!checkbox || !hidden) return;
                checkbox.addEventListener('change', () => {
                    hidden.value = checkbox.checked ? 'petugas' : 'user';
                    form.submit();
                });
            });
        });
    </script>
@endpush
