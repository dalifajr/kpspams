@php($golonganData = $golongan ?? null)
@php($existingTariffs = $golonganData?->tariffLevels?->map(fn ($tariff) => [
    'meter_start' => $tariff->meter_start,
    'meter_end' => $tariff->meter_end,
    'price' => $tariff->price,
])->toArray() ?? [])
@php($tariffRows = old('tariffs', $existingTariffs))
@php($tariffRows = is_array($tariffRows) ? array_values($tariffRows) : [])
@php($tariffRows = count($tariffRows) ? $tariffRows : [['meter_start' => '', 'meter_end' => '', 'price' => '']])
<form action="{{ $action }}" method="POST" class="golongan-form user-form__grid">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif
    <label class="form-field {{ $errors->has('code') ? 'has-error' : '' }}">
        <span>Kode *</span>
        <input type="text" name="code" value="{{ old('code', optional($golonganData)->code) }}" maxlength="10" required>
        @error('code')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </label>
    <label class="form-field {{ $errors->has('name') ? 'has-error' : '' }}">
        <span>Nama Golongan *</span>
        <input type="text" name="name" value="{{ old('name', optional($golonganData)->name) }}" required>
        @error('name')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </label>
    <div class="golongan-form__section" data-repeat-root="tariffs" data-repeat-next="{{ count($tariffRows) }}">
        <div class="golongan-form__section-head">
            <div>
                <p class="eyebrow">Tarif Level Meter *</p>
                <p class="muted small">Atur rentang posisi meter dan harga per m³ untuk golongan ini.</p>
            </div>
            <button type="button" class="btn-secondary light" data-repeat-add>
                <span class="material-symbols-rounded">add</span>
                Tambah Level
            </button>
        </div>
        <div class="tariff-config" data-repeat-list>
            @foreach ($tariffRows as $index => $tariff)
                <div class="tariff-config__row" data-repeat-item>
                    <div class="tariff-config__row-header">
                        <span data-repeat-label>Level {{ $loop->iteration }}</span>
                        <button type="button" class="icon-button ghost" data-repeat-remove title="Hapus level">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>
                    <div class="tariff-config__grid">
                        <label class="form-field {{ $errors->has('tariffs.' . $index . '.meter_start') ? 'has-error' : '' }}">
                            <span>Posisi Meter Awal *</span>
                            <input type="number" step="0.1" min="0" name="tariffs[{{ $index }}][meter_start]" value="{{ $tariff['meter_start'] ?? '' }}" required>
                            @error('tariffs.' . $index . '.meter_start')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </label>
                        <label class="form-field {{ $errors->has('tariffs.' . $index . '.meter_end') ? 'has-error' : '' }}">
                            <span>Posisi Meter Akhir</span>
                            <input type="number" step="0.1" min="0" name="tariffs[{{ $index }}][meter_end]" value="{{ $tariff['meter_end'] ?? '' }}">
                            @error('tariffs.' . $index . '.meter_end')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </label>
                        <label class="form-field {{ $errors->has('tariffs.' . $index . '.price') ? 'has-error' : '' }}">
                            <span>Harga per m³ *</span>
                            <input type="number" step="100" min="0" name="tariffs[{{ $index }}][price]" value="{{ $tariff['price'] ?? '' }}" required>
                            @error('tariffs.' . $index . '.price')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
        <template data-repeat-template>
            <div class="tariff-config__row" data-repeat-item>
                <div class="tariff-config__row-header">
                    <span data-repeat-label>Level</span>
                    <button type="button" class="icon-button ghost" data-repeat-remove title="Hapus level">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                <div class="tariff-config__grid">
                    <label class="form-field">
                        <span>Posisi Meter Awal *</span>
                        <input type="number" step="0.1" min="0" data-repeat-name="tariffs[__INDEX__][meter_start]" data-repeat-field="meter_start" required>
                    </label>
                    <label class="form-field">
                        <span>Posisi Meter Akhir</span>
                        <input type="number" step="0.1" min="0" data-repeat-name="tariffs[__INDEX__][meter_end]" data-repeat-field="meter_end">
                    </label>
                    <label class="form-field">
                        <span>Harga per m³ *</span>
                        <input type="number" step="100" min="0" data-repeat-name="tariffs[__INDEX__][price]" data-repeat-field="price" required>
                    </label>
                </div>
            </div>
        </template>
        @error('tariffs')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-actions">
        <a href="{{ url()->previous() }}" class="btn-secondary light">Batal</a>
        <button type="submit" class="btn-primary">Simpan</button>
    </div>
</form>
