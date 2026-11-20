@extends('layouts.base')

@php
    $navigation = \App\Support\LppmAccess::navigationFor(auth()->user());
    $roleBadges = auth()->user()->getSsoRoleNames();
    $ssoBaseUrl = rtrim((string) \App\Services\SSOConfigService::getUrl(), '/');
    $ssoProfileUrl = $ssoBaseUrl !== ''
        ? $ssoBaseUrl . '/profile?' . http_build_query([
            'client_id' => \App\Services\SSOConfigService::getClientId(),
            'redirect_uri' => \App\Services\SSOConfigService::getRedirect(),
            'redirect_ui' => request()->fullUrl(),
        ])
        : null;
@endphp

@section('body_class', 'lppm-app-page')

@section('body')
    <div class="app-shell">
        <aside class="sidebar">
            <div class="sidebar__brand">
                <img src="{{ url('images/logos/logo_lppm.jpg') }}" alt="Logo UMB LPPM">
                <div class="sidebar__brand-copy">
                    <span>Research & Community Service</span>
                </div>
            </div>

            <details class="sidebar__menu" id="sidebar-menu" open>
                <summary class="sidebar__toggle">Menu navigasi <span aria-hidden="true">⌄</span></summary>
                <div class="sidebar__section">
                    <p class="sidebar__caption">Navigasi</p>
                    <nav class="sidebar__nav" aria-label="Navigasi utama">
                        @foreach ($navigation as $item)
                            <a href="{{ route($item['route']) }}" class="sidebar__link {{ request()->routeIs($item['route']) ? 'is-active' : '' }}">
                                <span class="icon-chip icon-chip--{{ $item['icon'] }}"></span>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="sidebar__card">
                    <p class="sidebar__caption">Workflow</p>
                    <ul class="timeline-list">
                        <li>Draft</li>
                        <li>Submitted</li>
                        <li>Review</li>
                        <li>Revision</li>
                        <li>Approved</li>
                        <li>Completed</li>
                    </ul>
                </div>
            </details>
        </aside>

        <div class="app-main">
            <header class="topbar">
                <div>
                    <p class="eyebrow">@yield('eyebrow', 'Panel Operasional')</p>
                    <h1 class="page-title">@yield('page_title', 'Dashboard')</h1>
                </div>
                <div class="topbar__actions">
                    <a href="{{ $ssoProfileUrl ?? '#' }}" class="user-badge{{ $ssoProfileUrl ? '' : ' is-disabled' }}">
                        <img src="{{ url('images/logos/logo_circle_128.png') }}" alt="Logo Universitas">
                        <div>
                            <strong>{{ auth()->user()->name }}</strong>
                            <span>
                                {{ $roleBadges !== [] ? implode(', ', $roleBadges) : str_replace('_', ' ', auth()->user()->role) }}
                            </span>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn--ghost">Keluar</button>
                    </form>
                </div>
            </header>

            @if (session('status'))
                <div class="alert alert--success">{{ session('status') }}</div>
            @endif

            @yield('content')
        </div>
    </div>
@endsection
