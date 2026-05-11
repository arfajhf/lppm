@extends('layouts.app')

@section('title', 'Matriks Akses | UMB LPPM')
@section('eyebrow', 'RBAC')
@section('page_title', 'Matriks Akses LPPM')

@section('content')
    <section class="surface-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">{{ $module['eyebrow'] }}</p>
                <h2>{{ $module['title'] }}</h2>
            </div>
        </div>
        <p class="surface-copy">{{ $module['description'] }}</p>
        <div class="module-focus-list">
            @foreach (($module['focus'] ?? []) as $focus)
                <div class="module-focus-item">{{ $focus }}</div>
            @endforeach
        </div>
    </section>

    <section class="content-grid">
        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Akses Pengguna Saat Ini</p>
                    <h2>Hasil sinkron role & permission</h2>
                </div>
            </div>
            <div class="detail-grid">
                <div>
                    <strong>Role dari SSO</strong>
                    <span>{{ $user->getSsoRoleNames() !== [] ? implode(', ', $user->getSsoRoleNames()) : 'Belum terbaca dari payload SSO' }}</span>
                </div>
                <div>
                    <strong>Permission dari SSO</strong>
                    <span>{{ $user->getSsoPermissionNames() !== [] ? implode(', ', $user->getSsoPermissionNames()) : 'Belum dikirim eksplisit oleh payload SSO' }}</span>
                </div>
                <div>
                    <strong>Permission efektif di LPPM</strong>
                    <span>{{ $effectivePermissions !== [] ? implode(', ', $effectivePermissions) : 'Belum ada permission efektif' }}</span>
                </div>
                <div>
                    <strong>Sumber fallback</strong>
                    <span>{{ $user->getSsoPermissionNames() !== [] ? 'Payload permission dari SSO' : 'Fallback matriks blueprint berdasarkan role yang tersedia' }}</span>
                </div>
            </div>
        </article>

        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Gap di UMB SSO</p>
                    <h2>Apa yang belum lengkap untuk blueprint LPPM</h2>
                </div>
            </div>
            <div class="list-stack">
                <div class="list-item">
                    <span class="list-item__dot"></span>
                    <p>Role yang belum ada di SSO saat ini: {{ $missingRoles !== [] ? implode(', ', $missingRoles) : 'Tidak ada' }}.</p>
                </div>
                <div class="list-item">
                    <span class="list-item__dot"></span>
                    <p>Permission `lppm.*` yang belum ada di SSO saat ini: {{ $missingPermissions !== [] ? implode(', ', $missingPermissions) : 'Tidak ada' }}.</p>
                </div>
            </div>
        </article>
    </section>

    <section class="content-grid">
        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Role Blueprint</p>
                    <h2>Role yang dibutuhkan LPPM</h2>
                </div>
            </div>
            <div class="review-stack">
                @foreach ($requiredRoles as $role => $description)
                    <article class="review-card">
                        <strong>{{ $role }}</strong>
                        <p>{{ $description }}</p>
                    </article>
                @endforeach
            </div>
        </article>

        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Permission Blueprint</p>
                    <h2>Daftar permission `lppm.*` yang dianjurkan</h2>
                </div>
            </div>
            <div class="review-stack">
                @foreach ($requiredPermissions as $permission => $description)
                    <article class="review-card">
                        <strong>{{ $permission }}</strong>
                        <p>{{ $description }}</p>
                    </article>
                @endforeach
            </div>
        </article>
    </section>

    <section class="surface-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Snapshot SSO Saat Ini</p>
                <h2>Role dan permission yang terdeteksi di `umb-sso`</h2>
            </div>
        </div>
        <div class="proposal-grid sso-role-grid">
            @foreach ($currentSso['roles'] as $role)
                <article class="proposal-card surface-card">
                    <div class="proposal-card__head">
                        <span class="badge">{{ $role['name'] }}</span>
                        <small class="role-permission-count">{{ count($role['permissions']) }} permission</small>
                    </div>
                    <div class="permission-list">
                        @foreach ($role['permissions'] as $permission)
                            <span class="permission-pill">{{ $permission }}</span>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
