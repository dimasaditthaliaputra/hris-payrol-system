# 📋 Rencana Implementasi: Sistem Payroll & HR Management (HRIS)

> **Tech Stack**: Laravel 11 · MySQL · Bootstrap 5 / AdminLTE · Breeze · PHPSpreadsheet · DomPDF  
> **Pola Arsitektur**: Repository-Service Pattern  
> **Status Terakhir Diperbarui**: 2026-05-22

---

## ✅ FASE 0: Project Setup & Fondasi Arsitektur

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 0.1 | Install Laravel 11 | ✅ Done | v11.53.1 |
| 0.2 | Konfigurasi `.env` (DB, Timezone, App Name) | ✅ Done | DB: `hris`, TZ: `Asia/Jakarta` |
| 0.3 | Install Laravel Breeze (Blade stack) | ✅ Done | v2.4.2 |
| 0.4 | Install PHPSpreadsheet | ✅ Done | v5.7.0 |
| 0.5 | Install barryvdh/laravel-dompdf | ✅ Done | v3.1.2 |
| 0.6 | Buat Migration tabel `roles` | ✅ Done | + softDeletes |
| 0.7 | Buat Migration alter tabel `users` | ✅ Done | + `role_id`, `phone`, `is_active`, `deleted_at` |
| 0.8 | Buat Model `Role` (+ konstanta, relasi) | ✅ Done | `app/Models/Role.php` |
| 0.9 | Update Model `User` (+ relasi, helper methods) | ✅ Done | `isSuperAdmin()`, `isHrd()`, dll |
| 0.10 | Buat `RoleSeeder` (4 roles default) | ✅ Done | super_admin, hrd, finance, karyawan |
| 0.11 | Buat `UserSeeder` (Super Admin default) | ✅ Done | admin@hris.local / password |
| 0.12 | Jalankan `migrate` & `db:seed` | ✅ Done | Semua tabel & data awal siap |
| 0.13 | Buat struktur `Repositories/Contracts/` | ✅ Done | Interface: Base, Role, User |
| 0.14 | Buat `BaseRepository`, `RoleRepository`, `UserRepository` | ✅ Done | Concrete implementations |
| 0.15 | Buat `RepositoryServiceProvider` & daftarkan | ✅ Done | `bootstrap/providers.php` |
| 0.16 | Buat placeholder Services (Payroll, CashAdvance, Attendance) | ✅ Done | `app/Services/` |

---

## 🔲 FASE 1: Autentikasi & Manajemen Role/Permission

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 1.1 | Kustomisasi halaman Login Breeze dengan tampilan HRIS | ✅ Done | |
| 1.2 | Buat Middleware `CheckRole` untuk otorisasi per-role | ✅ Done | |
| 1.3 | Daftarkan middleware di `bootstrap/app.php` | ✅ Done | |
| 1.4 | Buat `RoleController` (CRUD Roles - Super Admin only) | ✅ Done | Gunakan `RoleRepositoryInterface` & return JSON |
| 1.5 | Buat FormRequest `StoreRoleRequest` & `UpdateRoleRequest` | ✅ Done | + Pesan validasi Bahasa Indonesia |
| 1.6 | Buat view CRUD Role (AdminLTE) | ✅ Done | Yajra DataTables, Bootstrap Modal, jQuery AJAX |
| 1.7 | Buat redirect setelah login berdasarkan role | ✅ Done | Dashboard berbeda per role |
| 1.8 | Buat `ActivityLogService` & middleware logging | ✅ Done | Tabel `activity_logs` |
| 1.9 | Buat Modul Manajemen User (CRUD) | ✅ Done | Khusus Super Admin, terintegrasi ActivityLogService |

---

## 🔲 FASE 2: Master Data — Departemen, Jabatan, Tipe Tunjangan/Potongan

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 2.1 | Migration: `departments` | ✅ Done | + softDeletes |
| 2.2 | Migration: `positions` | ✅ Done | + softDeletes, FK ke departments |
| 2.3 | Migration: `allowance_types` | ✅ Done | Tipe tunjangan (Transport, Makan, dll) |
| 2.4 | Migration: `deduction_types` | ✅ Done | Tipe potongan |
| 2.5 | Model + Repository + Interface untuk semua di atas | ✅ Done | Dept, Position, Allowance, Deduction |
| 2.6 | CRUD Controller (skinny) + FormRequest untuk semua | ✅ Done | + Pesan validasi Bahasa Indonesia |
| 2.7 | Blade views (AdminLTE) + DataTables | ✅ Done | Dept, Position, Allowance, Deduction |

---

## 🔲 FASE 3: Master Data — Karyawan (Employees)

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 3.1 | Migration: `employees` | ✅ Done | NIK, nama, dept, posisi, gaji pokok, BPJS, dll |
| 3.2 | Migration: `employee_allowances` | ✅ Done | Pivot: karyawan ↔ tipe tunjangan |
| 3.3 | Migration: `employee_deductions` | ✅ Done | Pivot: karyawan ↔ tipe potongan |
| 3.4 | Model `Employee` + Repository + Interface | ✅ Done | |
| 3.5 | `EmployeeService` untuk logika bisnis karyawan | ✅ Done | |
| 3.6 | CRUD Employee (+ upload foto, dokumen) | ✅ Done | + Pesan validasi Bahasa Indonesia lengkap |
| 3.7 | Form untuk kelola Tunjangan & Potongan per karyawan | ✅ Done | |
| 3.8 | Import Karyawan dari Excel (PHPSpreadsheet) | ✅ Done | Template download, validasi per baris, ImportLog, SweetAlert2 error report |

---

## 🔲 FASE 4: Absensi (Attendance)

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 4.1 | Migration: `attendances` | ✅ Done | tanggal, hadir, sakit, izin, alpha |
| 4.2 | Migration: `overtime` | ✅ Done | jam lembur per karyawan |
| 4.3 | Model, Repository, Interface untuk absensi & lembur | ✅ Done | |
| 4.4 | `AttendanceService` implementasi penuh | ✅ Done | |
| 4.5 | Import Absensi dari Excel + validasi | ✅ Done | PHPSpreadsheet, catat ke `import_logs` |
| 4.6 | View rekap absensi bulanan | ✅ Done | |
| 4.7 | Locking absensi jika periode payroll sudah terkunci | ✅ Done | **Critical logic** |

---

## 🔲 FASE 5: Insentif & THR

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 5.1 | Migration: `incentives` | ✅ Done | |
| 5.2 | Migration: `thr_payrolls` | ✅ Done | |
| 5.3 | CRUD Insentif (per karyawan, per periode) | ✅ Done | |
| 5.4 | Kalkulasi & generate THR | ✅ Done | |
| 5.5 | Locking insentif jika payroll terkunci | ✅ Done | **Critical logic** |

---

## 🔲 FASE 6: Cash Advance (Kasbon)

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 6.1 | Migration: `cash_advances` | ✅ Done | |
| 6.2 | Migration: `cash_advance_installments` | ✅ Done | Jadwal cicilan |
| 6.3 | Model, Repository, Interface | ✅ Done | |
| 6.4 | `CashAdvanceService` implementasi penuh | ✅ Done | Auto-generate jadwal cicilan |
| 6.5 | CRUD Cash Advance + approval flow | ✅ Done | |
| 6.6 | Integrasi cicilan ke kalkulasi payroll | ⬜ Todo | **Critical logic** (Di Fase 7) |

---

## 🔲 FASE 7: Payroll — Inti Sistem

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 7.1 | Migration: `payrolls` | ⬜ Todo | periode, status (draft/locked), total |
| 7.2 | Migration: `payroll_details` | ⬜ Todo | Detail per karyawan |
| 7.3 | Model, Repository, Interface | ⬜ Todo | |
| 7.4 | `PayrollService` implementasi penuh | ⬜ Todo | **Core business logic** |
| 7.5 | Validasi: tidak bisa generate tanpa absensi | ⬜ Todo | **Critical validation** |
| 7.6 | Validasi: tidak bisa duplikat periode | ⬜ Todo | **Critical validation** |
| 7.7 | Generate Payroll (batch per periode) | ⬜ Todo | |
| 7.8 | **Fitur Lock Payroll** | ⬜ Todo | Status locked → freeze semua data terkait |
| 7.9 | **Fitur Unlock Payroll** (Super Admin only) | ⬜ Todo | |
| 7.10 | Approval flow (Finance) | ⬜ Todo | |

---

## 🔲 FASE 8: Slip Gaji & Laporan

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 8.1 | Generate Slip Gaji PDF (DomPDF) | ⬜ Todo | Template profesional |
| 8.2 | View slip gaji untuk Karyawan | ⬜ Todo | |
| 8.3 | Download slip gaji PDF (self-service) | ⬜ Todo | |
| 8.4 | Laporan Payroll per periode | ⬜ Todo | |
| 8.5 | Export laporan ke Excel (PHPSpreadsheet) | ⬜ Todo | |
| 8.6 | Laporan rekap absensi | ⬜ Todo | |
| 8.7 | Dashboard ringkasan (charts) | ⬜ Todo | Per role |

---

## 🔲 FASE 9: Polish & Production Readiness

| # | Task | Status | Catatan |
|---|------|--------|---------|
| 9.1 | Activity Log lengkap di semua aksi penting | ⬜ Todo | |
| 9.2 | Import Log untuk semua proses import | ⬜ Todo | |
| 9.3 | Unit tests untuk Services (PayrollService, CashAdvanceService) | ⬜ Todo | |
| 9.4 | Pengujian end-to-end payroll flow | ⬜ Todo | |
| 9.5 | Hardening keamanan (CSRF, policy, gate) | ⬜ Todo | |
| 9.6 | Optimasi query (eager loading, indexing) | ⬜ Todo | |

---

## 📁 Struktur Direktori Arsitektur

```
app/
├── Http/
│   ├── Controllers/      ← Skinny controllers (HTTP only)
│   ├── Requests/         ← FormRequests (validation)
│   └── Middleware/       ← CheckRole, ActivityLog
├── Models/               ← Eloquent models
├── Repositories/
│   ├── Contracts/        ← Repository Interfaces
│   ├── BaseRepository.php
│   ├── RoleRepository.php
│   └── UserRepository.php
├── Services/             ← Business logic layer
│   ├── PayrollService.php
│   ├── CashAdvanceService.php
│   └── AttendanceService.php
└── Providers/
    └── RepositoryServiceProvider.php  ← Binding Interface → Implementation
```

---

## 🔑 Kredensial Default

| Field | Value |
|-------|-------|
| Email | `admin@hris.local` |
| Password | `password` |
| Role | Super Admin |

> [!CAUTION]
> **Segera ganti password default** setelah login pertama kali di lingkungan production.

---

## ⚡ Critical Business Rules (Jangan Sampai Salah Implementasi)

> [!IMPORTANT]
> 1. **Formula Payroll**: `Gaji Bersih = Gaji Pokok + Tunjangan + Lembur + Insentif - Potongan - BPJS - PPh21 - Cicilan Cash Advance`
> 2. **Payroll Lock**: Setelah di-lock, semua data terkait (absensi, lembur, insentif, potongan, kasbon) TIDAK BISA diubah. Hanya Super Admin yang bisa unlock.
> 3. **Generate Payroll**: Wajib ada data absensi, dan tidak boleh duplikat untuk periode yang sama.
> 4. **Cash Advance**: Cicilan harus otomatis terpotong saat generate payroll sesuai jadwal.
