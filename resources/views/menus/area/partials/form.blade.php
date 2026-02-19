@php($areaData = $area ?? null)
<form action="{{ $action }}" method="POST" class="area-form">
    @csrf
    @isset($method)
        @method($method)
    @endisset
    <label class="form-field {{ $errors->has('name') ? 'has-error' : '' }}">
        <span>Nama Area</span>
        <input type="text" name="name" value="{{ old('name', optional($areaData)->name) }}" required>
        @error('name')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </label>
    <label class="form-field {{ $errors->has('customer_count') ? 'has-error' : '' }}">
        <span>Jumlah Pelanggan</span>
        <input type="number" name="customer_count" min="0" value="{{ old('customer_count', optional($areaData)->customer_count ?? 0) }}" required>
        @error('customer_count')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </label>
    <label class="form-field {{ $errors->has('notes') ? 'has-error' : '' }}">
        <span>Catatan</span>
        <textarea name="notes" rows="3" placeholder="Opsional">{{ old('notes', optional($areaData)->notes) }}</textarea>
        @error('notes')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </label>
    <div class="form-actions">
        <a href="{{ route('menu.area') }}" class="btn-secondary light">Batal</a>
        <button type="submit" class="btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
