@php($title = 'Detail Pelanggan')
@php($bodyClass = 'page-customer-admin')
@extends('layouts.app')

@section('content')
    <div class="customer-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('menu.customers.index') }}" class="chip-back" aria-label="Kembali ke data pelanggan">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>{{ $customer->name }}</h1>
                    </div>
                </div>
                <div class="toolbar-actions">
                    <a href="{{ route('menu.customers.edit', $customer) }}" class="icon-button ghost" title="Edit">
                        <span class="material-symbols-rounded">edit</span>
                    </a>
                    <form action="{{ route('menu.customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Hapus data pelanggan ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="icon-button ghost" title="Hapus">
                            <span class="material-symbols-rounded">delete</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="alert-success">
                <span class="material-symbols-rounded">check_circle</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        @if (session('generated_credentials'))
            @php($credentials = session('generated_credentials'))
            <div class="alert-success">
                <span class="material-symbols-rounded">key</span>
                <div>
                    <p>Akun pelanggan siap digunakan. Berikan data login berikut kepada pelanggan dan ingatkan untuk segera mengganti kata sandi.</p>
                    <div class="credential-note">
                        <p><strong>Nomor HP</strong>: <code>{{ $credentials['phone_number'] }}</code></p>
                        <p><strong>Password</strong>: <code>{{ $credentials['password'] }}</code></p>
                    </div>
                </div>
            </div>
        @endif

        <div class="customer-detail-card">
            <div class="customer-detail__header">
                <div>
                    <p class="eyebrow">ID Pelanggan</p>
                    <h2>{{ $customer->customer_code }}</h2>
                    <p class="muted">Golongan {{ optional($customer->golongan)->code ?? 'Belum diisi' }} • Area {{ $customer->area->name ?? '-' }}</p>
                </div>
                <div class="customer-detail__badge">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($customer->area->name ?? '?', 0, 1)) }}</div>
            </div>
            <dl class="customer-detail__grid">
                <div>
                    <dt>Nama Lengkap</dt>
                    <dd>{{ $customer->name }}</dd>
                </div>
                <div>
                    <dt>Alamat Singkat</dt>
                    <dd>{{ $customer->address_short }}</dd>
                </div>
                <div>
                    <dt>Nomor HP</dt>
                    <dd>
                        @if ($customer->phone_number)
                            <a href="tel:{{ $customer->phone_number }}">{{ $customer->phone_number }}</a>
                        @else
                            <span class="muted">Belum diisi</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Anggota Keluarga</dt>
                    <dd>{{ $customer->family_members }} Orang</dd>
                </div>
            </dl>
        </div>

        <div class="customer-detail-card">
            <div class="card-header">
                <div>
                    <p class="eyebrow">Integrasi Akun</p>
                    <h2>User Terkait</h2>
                </div>
            </div>
            <div class="linked-users">
                @forelse ($customer->users as $user)
                    <div class="linked-user">
                        <div>
                            <p class="linked-user__name">{{ $user->name }}</p>
                            <p class="linked-user__meta">{{ $user->email }} • {{ $user->role }}</p>
                        </div>
                        <span class="status-chip {{ $user->isApproved() ? 'is-approved' : 'is-pending' }}">{{ $user->isApproved() ? 'Aktif' : 'Pending' }}</span>
                    </div>
                @empty
                    <p class="muted">Belum ada user yang dikaitkan dengan pelanggan ini.</p>
                @endforelse
            </div>
            @php($hasCustomerAccount = $customer->users->contains(fn ($user) => $user->role === \App\Models\User::ROLE_USER))
            <div class="account-actions">
                <div>
                    <p class="eyebrow">Buatkan Akun</p>
                    <p class="muted">Nomor HP pelanggan akan digunakan sebagai username dan password awal digenerate otomatis.</p>
                </div>
                @if (! $hasCustomerAccount)
                    <form action="{{ route('menu.customers.account.create', $customer) }}" method="POST" class="quick-form">
                        @csrf
                        @php($shouldShowPhoneField = $errors->has('phone_number') || ! $customer->phone_number)
                        @if ($shouldShowPhoneField)
                            <label class="form-field {{ $errors->has('phone_number') ? 'has-error' : '' }}">
                                <span>Nomor HP Login</span>
                                @php($phoneRequired = ! $customer->phone_number || $errors->has('phone_number'))
                                <input type="text" name="phone_number" value="{{ old('phone_number', $customer->phone_number) }}" placeholder="08xxxxxxxxxx" {{ $phoneRequired ? 'required' : '' }}>
                            </label>
                        @else
                            <input type="hidden" name="phone_number" value="{{ $customer->phone_number }}">
                            <p class="muted">Nomor HP login: <strong>{{ $customer->phone_number }}</strong></p>
                        @endif
                        @error('phone_number')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        @error('account')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="btn-primary">
                            <span class="material-symbols-rounded">person_add</span>
                            <span>Buatkan Akun</span>
                        </button>
                    </form>
                @else
                    <p class="muted">Akun pelanggan sudah tersedia.</p>
                @endif
            </div>
        </div>
    </div>

    <x-bottom-nav />
@endsection
