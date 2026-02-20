# Mobile Development Map berdasarkan Tabel Migrasi

Dokumen ini memetakan tabel Laravel (`database/migrations`) ke modul mobile KPSPAMS.

## Modul yang Sudah Dipakai Mobile

- `users`, `personal_access_tokens`
  - Auth login/logout/me, profile, force update password.
- `areas`
  - Filter akses petugas dan data master area.
- `customers`
  - List pelanggan dan detail dasar.
- `meter_periods`, `meter_readings`, `meter_assignments`
  - Periode catat meter, daftar baca meter, input stand.
- `bills`, `payments`
  - Publish/unpublish tagihan, daftar tagihan aktif, pembayaran.
- `golongans`, `golongan_tariffs`, `golongan_non_air_fees`
  - Data master golongan (list + count tarif/non-air fees).

## Fitur Mobile yang Ditambahkan dari Audit Migrasi

- Route kompatibilitas API:
  - Prefix `api/v1/auth/*` (existing)
  - Prefix `api/v1/*` (baru, backward compatibility)
- Endpoint data master golongan:
  - `GET /api/v1/auth/golongans`
  - `GET /api/v1/golongans`
- Menu mobile baru:
  - `Data Master` (Area + Golongan)

## Backlog Berikutnya (berbasis tabel)

1. `change_logs`
   - Buat layar audit log dan aksi undo (admin).
2. `golongan_tariffs` + `golongan_non_air_fees`
   - Detail golongan per level tarif dan biaya non-air di mobile.
3. `meter_assignments`
   - Tampilkan assignment petugas per area/periode.
4. `payments`
   - Riwayat pembayaran per pelanggan + filter periode.
5. `bills`
   - Rekap tunggakan dan lunas per area/golongan.
