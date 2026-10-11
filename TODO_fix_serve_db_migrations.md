# Fix: php artisan serve error (migrations table missing)

## Gejala
- Error: `Table 'cctv_final.migrations' doesn't exist`
- Laravel mencoba query table `migrations`, tapi tabel repositori belum ada di database yang dipakai (`cctv_final`).

## Cara cepat (MySQL)
Jalankan di phpMyAdmin / MySQL:

```sql
CREATE TABLE migrations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  migration VARCHAR(255) NOT NULL,
  batch INT NOT NULL
);
```

Setelah itu:
- `php artisan serve`
- atau `php artisan migrate`.

## Alternatif (untuk memastikan DB yang benar)
- cek `.env`:
  - `DB_DATABASE` harus sama dengan database yang tabel-tabel app kamu ada (termasuk `cctv_final`).

## Status
- [ ] Pastikan DB yang dipakai `cctv_final`.
- [ ] Pastikan tabel `migrations` sudah dibuat.
- [ ] Jalankan ulang `php artisan serve` / `php artisan migrate`.

