@php($customerData = $customer ?? null)
@php($golonganList = $golongans ?? collect())
@php($defaultCodeValue = $defaultCode ?? null)
@php($currentCodeValue = old('customer_code', optional($customerData)->customer_code ?? $defaultCodeValue))
<form action="{{ $action }}" method="POST" class="customer-form user-form__grid">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif
    <label class="form-field {{ $errors->has('customer_code') ? 'has-error' : '' }}">
        <span>NOPEL *</span>
        <input type="text" name="customer_code" value="{{ $currentCodeValue }}" required maxlength="20">
        @error('customer_code')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </label>
    <label class="form-field {{ $errors->has('name') ? 'has-error' : '' }}">
        <span>Nama Pelanggan *</span>
        <input type="text" name="name" value="{{ old('name', optional($customerData)->name) }}" required>
        @error('name')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </label>
    <label class="form-field {{ $errors->has('address_short') ? 'has-error' : '' }}">
        <span>Alamat Singkat *</span>
        <input type="text" name="address_short" value="{{ old('address_short', optional($customerData)->address_short) }}" required>
        @error('address_short')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </label>
    <label class="form-field {{ $errors->has('phone_number') ? 'has-error' : '' }}">
        <span>Nomor HP</span>
        <input type="text" name="phone_number" value="{{ old('phone_number', optional($customerData)->phone_number) }}" placeholder="Opsional">
        @error('phone_number')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </label>
    <label class="form-field {{ $errors->has('family_members') ? 'has-error' : '' }}">
        <span>Anggota Keluarga *</span>
        <input type="number" name="family_members" min="0" max="999" value="{{ old('family_members', optional($customerData)->family_members ?? 0) }}" required>
        @error('family_members')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </label>
    <label class="form-field {{ $errors->has('area_id') ? 'has-error' : '' }}">
        <span>Area *</span>
        <select name="area_id" required>
            <option value="" disabled {{ old('area_id', optional($customerData)->area_id) ? '' : 'selected' }}>Pilih area</option>
            @foreach ($areas as $area)
                <option value="{{ $area->id }}" @selected((string) old('area_id', optional($customerData)->area_id) === (string) $area->id)>{{ $area->name }}</option>
            @endforeach
        </select>
        @error('area_id')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </label>
    <label class="form-field {{ $errors->has('golongan_id') ? 'has-error' : '' }}">
        <span>Golongan *</span>
        <select name="golongan_id" required>
            <option value="" disabled {{ old('golongan_id', optional($customerData)->golongan_id) ? '' : 'selected' }}>Pilih golongan</option>
            @foreach ($golonganList as $golonganOption)
                <option value="{{ $golonganOption->id }}" @selected((string) old('golongan_id', optional($customerData)->golongan_id) === (string) $golonganOption->id)>
                    {{ $golonganOption->code }} — {{ $golonganOption->name }}
                </option>
            @endforeach
        </select>
        @error('golongan_id')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </label>
    <div class="form-actions">
        <a href="{{ url()->previous() }}" class="btn-secondary light">Batal</a>
        <button type="submit" class="btn-primary">Simpan</button>
    </div>
</form>
