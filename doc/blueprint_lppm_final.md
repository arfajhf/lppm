# 📘 BLUEPRINT SISTEM LPPM
## Universitas Mayasari Bakti (Final Summary)

---

# 1. 🎯 LATAR BELAKANG
Sistem LPPM dibangun untuk mengelola kegiatan:
- Penelitian
- Pengabdian kepada masyarakat

Permasalahan:
- Data tersebar
- Tidak terintegrasi
- Sulit monitoring

Solusi:
👉 Sistem terpusat berbasis web + SSO

---

# 2. 🧠 KONSEP SISTEM

## Prinsip:
- Modular
- Scalable
- API-first
- RBAC (Role Based Access Control)
- UUID primary key

---

# 3. 👥 PENGGUNA SISTEM

- Admin LPPM
- Reviewer
- Dosen
- Mahasiswa
- Mitra

---

# 4. 🧩 RUANG LINGKUP

## 4.1 Kegiatan
- Penelitian Mandiri
- Penelitian Hibah
- Pengabdian Mandiri
- Pengabdian Hibah

## 4.2 Fitur
- SSO Login
- Manajemen Proposal
- Upload Dokumen
- Review & Approval
- Pelaporan
- Notifikasi

---

# 5. 🏗️ ARSITEKTUR

Frontend → API → Service → Repository → Database

Tech Stack:
- Laravel
- MySQL
- REST API

---

# 6. 🧩 ERD (TEXTUAL)

users
 ├── proposals
 │     ├── proposal_members
 │     ├── documents
 │     ├── reviews
 │     ├── reports
 │     └── partners
 └── notifications

---

# 7. 🗄️ DATABASE DESIGN

## proposals
- proposal_id (UUID)
- title
- type
- scheme
- user_id
- status
- year

## proposal_members
- member_id
- proposal_id
- user_id
- role

## reviews
- review_id
- proposal_id
- reviewer_id
- score
- comment

## documents
- document_id
- proposal_id
- file_path

## reports
- report_id
- proposal_id
- progress

## partners
- partner_id
- name

## notifications
- notification_id
- user_id
- message

---

# 8. 🔄 WORKFLOW

draft → submitted → review → revision → approved → completed

---

# 9. ⚙️ MIGRATION (UUID)

```php
$table->uuid('proposal_id')->primary();
$table->foreignUuid('user_id')->constrained('users','user_id');
```

---

# 10. 🎨 UX DESIGN

## Bootstrap + Blade

### Layout:
```blade
@extends('layouts.app')

@section('content')
@endsection
```

### Struktur:
- layouts/
- components/
- pages/

---

# 11. 🔐 SECURITY

- SSO OAuth
- Sanctum
- RBAC
- Logging

---

# 12. 🔌 API

GET /api/proposals  
POST /api/proposals  
POST /api/reviews  

---

# 13. 🚀 ROADMAP

Phase 1:
- Auth
- Proposal

Phase 2:
- Review
- Notifikasi

Phase 3:
- Reporting
- Analytics

---

# 14. 📌 KESIMPULAN

Sistem LPPM:
- Terintegrasi
- Berbasis role
- Mendukung seluruh lifecycle penelitian & pengabdian

Siap dikembangkan ke tahap implementasi Laravel + Codex.
