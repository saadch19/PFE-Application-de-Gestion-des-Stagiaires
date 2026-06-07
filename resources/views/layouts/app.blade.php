<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestion des Stagiaires')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-gradient-start: #eef3f7;
            --bg-gradient-end: #dce7df;
            --surface: #ffffff;
            --surface-muted: #f7faf9;
            --border-soft: #d9e2e7;
            --text-main: #1f2933;
            --text-muted: #667085;
            --brand: #1f6f64;
            --brand-dark: #164e63;
            --brand-alt: #c65d3a;
            --focus-ring: rgba(31, 111, 100, 0.18);
        }

        body {
            min-height: 100vh;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.78), rgba(255, 255, 255, 0.38)),
                linear-gradient(135deg, var(--bg-gradient-start), var(--bg-gradient-end));
            background-attachment: fixed;
            color: var(--text-main);
            overflow-x: hidden;
            position: relative;
            font-size: 0.96rem;
        }

        .app-navbar {
            background: linear-gradient(120deg, #163b45, #205f69 55%, #2f5b85);
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        }

        .app-navbar > .container,
        main.container {
            max-width: min(100% - 2rem, 1500px);
        }

        .app-navbar .navbar-brand {
            min-width: 0;
            white-space: nowrap;
            font-size: 1rem;
            margin-right: 1.5rem;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
        }

        .app-navbar .navbar-brand img {
            height: clamp(34px, 3.5vw, 44px) !important;
            flex: 0 0 auto;
            filter: drop-shadow(0 5px 10px rgba(0, 0, 0, 0.16));
            transform: scale(1.28);
            transform-origin: center;
        }

        .app-navbar .navbar-brand span {
            line-height: 1.15;
            letter-spacing: 0;
            font-weight: 700;
        }

        .app-navbar .nav-link {
            display: inline-flex;
            align-items: center;
            border-radius: 0.4rem;
            color: rgba(255, 255, 255, 0.76);
            font-weight: 600;
            padding: 0.42rem 0.58rem;
            font-size: 0.92rem;
            transition: color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
        }

        .app-navbar .nav-link:hover,
        .app-navbar .nav-link.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.14);
        }

        .app-navbar .nav-link.active {
            box-shadow: inset 0 -2px 0 #e3ed73;
            background: rgba(255, 255, 255, 0.16);
        }

        .app-navbar .btn {
            border-radius: 0.45rem;
            font-weight: 600;
            padding: 0.5rem 0.7rem;
        }

        .app-navbar .dropdown-menu {
            border: 1px solid var(--border-soft);
            border-radius: 0.65rem;
            box-shadow: 0 18px 36px rgba(31, 41, 51, 0.12);
            padding: 0.45rem;
        }

        .app-navbar .dropdown-item {
            border-radius: 0.45rem;
            font-weight: 600;
            padding: 0.55rem 0.75rem;
        }

        .app-navbar .dropdown-item.active,
        .app-navbar .dropdown-item:active {
            background: var(--brand);
        }

        .navbar-user-actions {
            flex-shrink: 0;
        }

        .navbar-profile-btn {
            max-width: 15rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .card-soft {
            border: 1px solid rgba(217, 226, 231, 0.9);
            border-radius: 0.75rem;
            box-shadow: 0 16px 40px rgba(31, 41, 51, 0.08);
            background: var(--surface);
        }

        .card-soft .card-body {
            padding: clamp(1rem, 1.8vw, 1.5rem);
        }

        .brand-mark {
            width: 4.5rem;
            height: 4.5rem;
            object-fit: contain;
            padding: 0.35rem;
            border-radius: 0.65rem;
            background: #ffffff;
            border: 1px solid var(--border-soft);
            box-shadow: 0 10px 24px rgba(31, 41, 51, 0.08);
        }

        .page-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: var(--brand-dark);
            font-size: 0.78rem;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: 0;
            margin-bottom: 0.35rem;
        }

        .dashboard-hero {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(217, 226, 231, 0.9);
            border-radius: 0.8rem;
            background:
                linear-gradient(120deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 249, 0.94)),
                linear-gradient(120deg, rgba(31, 111, 100, 0.08), rgba(47, 91, 133, 0.08));
            box-shadow: 0 16px 40px rgba(31, 41, 51, 0.08);
        }

        .dashboard-hero::after {
            content: "";
            position: absolute;
            inset: auto -2rem -4rem auto;
            width: 15rem;
            height: 15rem;
            border-radius: 999px;
            background: rgba(31, 111, 100, 0.08);
            pointer-events: none;
        }

        .module-icon {
            width: 2.65rem;
            height: 2.65rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            border-radius: 0.65rem;
            color: var(--brand);
            background: #eef8f6;
            border: 1px solid #d8ebe7;
            font-size: 1.2rem;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .section-title .module-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.55rem;
            font-size: 1.05rem;
        }

        .weekly-chart {
            display: grid;
            grid-template-columns: repeat(6, minmax(2.5rem, 1fr));
            gap: 0.6rem;
            min-height: 8.75rem;
            align-items: end;
            padding: 0.8rem;
            border: 1px solid var(--border-soft);
            border-radius: 0.65rem;
            background: linear-gradient(180deg, #ffffff, #f8fbfa);
        }

        .weekly-chart-bar {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: end;
            gap: 0.4rem;
            height: 6.75rem;
            min-width: 0;
        }

        .weekly-chart-fill {
            width: min(100%, 2.4rem);
            min-height: 0.4rem;
            border-radius: 0.45rem 0.45rem 0.2rem 0.2rem;
            background: linear-gradient(180deg, #2f7d73, #1f6f64);
            box-shadow: 0 8px 18px rgba(31, 111, 100, 0.18);
        }

        .weekly-chart-value {
            color: #17212b;
            font-size: 0.85rem;
            font-weight: 750;
        }

        .weekly-chart-label {
            color: var(--text-muted);
            font-size: 0.78rem;
            white-space: nowrap;
        }

        .score-donut {
            --score: 0;
            --score-color: var(--brand);
            width: 8.4rem;
            aspect-ratio: 1;
            display: grid;
            place-items: center;
            margin-inline: auto;
            border-radius: 50%;
            background:
                radial-gradient(circle closest-side, #ffffff 58%, transparent 60%),
                conic-gradient(var(--score-color) calc(var(--score) * 1%), #e9eef1 0);
            box-shadow: inset 0 0 0 1px rgba(217, 226, 231, 0.85), 0 14px 30px rgba(31, 41, 51, 0.08);
        }

        .score-donut-value {
            color: #17212b;
            font-size: 1.65rem;
            font-weight: 800;
            line-height: 1;
        }

        .score-donut-caption {
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 650;
        }

        .score-metric {
            border: 1px solid var(--border-soft);
            border-radius: 0.65rem;
            background: #f8fbfa;
            padding: 0.75rem;
        }

        .empty-state {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            padding: 1rem;
            border: 1px dashed #cfd8df;
            border-radius: 0.75rem;
            background: #f8fbfa;
            color: var(--text-muted);
        }

        .empty-state-icon {
            width: 2.5rem;
            height: 2.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            border-radius: 999px;
            color: var(--brand);
            background: #eef8f6;
            border: 1px solid #d8ebe7;
            font-size: 1.1rem;
        }

        .table-responsive {
            border: 1px solid var(--border-soft);
            border-radius: 0.65rem;
            background: #ffffff;
        }

        .table {
            min-width: 640px;
            margin-bottom: 0;
            color: var(--text-main);
        }

        .table > :not(caption) > * > * {
            padding: 0.85rem 0.75rem;
            border-bottom-color: #e6edf1;
            vertical-align: middle;
        }

        .table thead th {
            background: var(--surface-muted);
            color: #17212b;
            font-size: 0.86rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0;
            border-bottom: 1px solid var(--border-soft);
        }

        .table tbody tr {
            transition: background-color 0.16s ease;
        }

        .table-hover tbody tr:hover {
            background-color: #f3f8f7;
        }

        .form-label {
            color: #344054;
            font-weight: 650;
            margin-bottom: 0.4rem;
        }

        .btn,
        .badge,
        .form-control,
        .form-select,
        .select2-container {
            max-width: 100%;
        }

        .form-control,
        .form-select {
            border-color: #cfd8df;
            border-radius: 0.5rem;
            color: var(--text-main);
            min-height: 2.45rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 0.2rem var(--focus-ring);
        }

        .btn {
            border-radius: 0.5rem;
            font-weight: 650;
        }

        .btn-success {
            background-color: var(--brand);
            border-color: var(--brand);
        }

        .btn-success:hover,
        .btn-success:focus {
            background-color: #185b52;
            border-color: #185b52;
        }

        .btn-outline-success {
            color: var(--brand);
            border-color: var(--brand);
        }

        .btn-outline-success:hover,
        .btn-outline-success:focus {
            background-color: var(--brand);
            border-color: var(--brand);
        }

        .badge {
            border-radius: 0.4rem;
            padding: 0.42em 0.62em;
            font-weight: 700;
        }

        .alert {
            border: 1px solid rgba(217, 226, 231, 0.85);
            border-radius: 0.65rem;
            box-shadow: 0 10px 24px rgba(31, 41, 51, 0.06);
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            border-left: 4px solid var(--brand);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .stat-card::after {
            content: "\F2EE";
            font-family: "bootstrap-icons";
            position: absolute;
            right: 0.75rem;
            bottom: 0.65rem;
            color: rgba(31, 111, 100, 0.07);
            font-size: 1.75rem;
            line-height: 1;
            pointer-events: none;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(31, 41, 51, 0.1);
        }

        .auth-logo {
            width: 5.25rem;
            height: 5.25rem;
            object-fit: contain;
            margin-bottom: 1rem;
            padding: 0.35rem;
            border-radius: 0.75rem;
            background: #ffffff;
            border: 1px solid var(--border-soft);
            box-shadow: 0 12px 28px rgba(31, 41, 51, 0.1);
        }

        .attestation-timeline {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 0.75rem;
            align-items: center;
        }

        .attestation-timeline-step {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: #6c757d;
            font-size: 0.78rem;
            white-space: nowrap;
        }

        .attestation-timeline-dot {
            width: 0.7rem;
            height: 0.7rem;
            border-radius: 999px;
            background: #dee2e6;
            box-shadow: 0 0 0 3px #f1f3f5;
        }

        .attestation-timeline-step.is-done {
            color: #198754;
            font-weight: 600;
        }

        .attestation-timeline-step.is-done .attestation-timeline-dot {
            background: #198754;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.15);
        }

        .fade-in {
            animation: fadeInUp 0.28s ease both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1199.98px) {
            .app-navbar .navbar-collapse {
                padding-top: 1rem;
            }

            .app-navbar .nav-link {
                margin-bottom: 0.25rem;
            }

            .app-navbar .navbar-collapse > .d-flex {
                align-items: stretch !important;
                flex-wrap: wrap;
                gap: 0.5rem !important;
                padding-top: 0.5rem;
            }

            .app-navbar .navbar-collapse > .d-flex .btn,
            .app-navbar .navbar-collapse > .d-flex form,
            .app-navbar .navbar-collapse > .d-flex button {
                width: 100%;
            }
        }

        @media (max-width: 767.98px) {
            body {
                background-attachment: scroll;
            }

            .app-navbar > .container,
            main.container {
                max-width: calc(100% - 1rem);
            }

            main.container {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }

            .app-navbar .navbar-brand {
                max-width: calc(100% - 4rem);
                font-size: 0.95rem;
            }

            .card-soft {
                border-radius: 0.75rem;
            }

            .display-5 {
                font-size: 2rem;
            }

            .display-6 {
                font-size: 1.75rem;
            }

            .table {
                min-width: 560px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    @auth
        @php
            $authUser = auth()->user();
            $gestionActive = request()->routeIs(
                'users.*',
                'interns.*',
                'internships.*',
                'supervisor.interns',
                'supervisor.interns.show',
                'supervisor.internships',
                'supervisor.internships.show',
                'absences.*',
                'rh.attestations.*',
                'rh.archives.*',
                'rh.profile'
            );
            $suiviActive = request()->routeIs('tasks.*', 'requests.*', 'daily-log.*');
        @endphp
        <nav class="navbar navbar-expand-xl navbar-dark app-navbar shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-semibold d-flex align-items-center" href="{{ route('dashboard') }}">
                    <img src="{{ asset('images/ALTEN-Logo.wine.png') }}" alt="Alten Logo" style="height: 44px; margin-right: 10px;">
                    <span>Gestion des Stagiaires</span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>

                        @if($authUser->hasRole('Administrateur', 'Responsable RH', 'Responsable de competence', 'Encadrant'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ $gestionActive ? 'active' : '' }}" href="#" id="gestionDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Gestion</a>
                            <ul class="dropdown-menu" aria-labelledby="gestionDropdown">
                                @if($authUser->hasRole('Administrateur'))
                                    <li><a class="dropdown-item {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Utilisateurs</a></li>
                                @endif
                                @if($authUser->hasRole('Administrateur', 'Responsable RH', 'Responsable de competence'))
                                    <li><a class="dropdown-item {{ request()->routeIs('interns.*') ? 'active' : '' }}" href="{{ route('interns.index') }}">Stagiaires</a></li>
                                @endif
                                @if($authUser->hasRole('Administrateur', 'Responsable de competence'))
                                    <li><a class="dropdown-item {{ request()->routeIs('internships.*') ? 'active' : '' }}" href="{{ route('internships.index') }}">Stages</a></li>
                                @endif
                                @if($authUser->hasRole('Encadrant'))
                                    <li><a class="dropdown-item {{ request()->routeIs('supervisor.interns', 'supervisor.interns.show') ? 'active' : '' }}" href="{{ route('supervisor.interns') }}">Mes stagiaires</a></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('supervisor.internships', 'supervisor.internships.show') ? 'active' : '' }}" href="{{ route('supervisor.internships') }}">Mes stages</a></li>
                                @endif
                                @if($authUser->hasRole('Administrateur', 'Responsable RH', 'Responsable de competence'))
                                    <li><a class="dropdown-item {{ request()->routeIs('absences.*') ? 'active' : '' }}" href="{{ route('absences.index') }}">Absences</a></li>
                                @endif
                                @if($authUser->hasRole('Responsable RH'))
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('rh.attestations.*') ? 'active' : '' }}" href="{{ route('rh.attestations.index') }}">Attestations</a></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('rh.archives.*') ? 'active' : '' }}" href="{{ route('rh.archives.index') }}">Archives</a></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('rh.profile') ? 'active' : '' }}" href="{{ route('rh.profile') }}">Profil RH</a></li>
                                @endif
                            </ul>
                        </li>
                        @endif

                        @if(! $authUser->hasRole('Responsable RH'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ $suiviActive ? 'active' : '' }}" href="#" id="suiviDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Suivi</a>
                            <ul class="dropdown-menu" aria-labelledby="suiviDropdown">
                                @if(! $authUser->hasRole('Responsable de competence', 'Responsable RH'))
                                    <li><a class="dropdown-item {{ request()->routeIs('tasks.*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">Tâches</a></li>
                                @endif
                                @if($authUser->hasRole('Stagiaire'))
                                    <li><a class="dropdown-item {{ request()->routeIs('daily-log.*') ? 'active' : '' }}" href="{{ route('daily-log.index') }}">📓 Mon Journal</a></li>
                                @endif
                                @if($authUser->hasRole('Administrateur', 'Responsable de competence', 'Encadrant', 'Stagiaire'))
                                    <li><a class="dropdown-item {{ request()->routeIs('requests.*') ? 'active' : '' }}" href="{{ route('requests.index') }}">Demandes</a></li>
                                @endif
                            </ul>
                        </li>
                        @endif

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}" href="{{ route('messages.index') }}">Messages</a>
                        </li>
                    </ul>

                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-none">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>

                        @if($authUser->hasRole('Administrateur'))
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Utilisateurs</a></li>
                        @endif

                        @if($authUser->hasRole('Administrateur', 'Responsable RH', 'Responsable de competence'))
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('interns.*') ? 'active' : '' }}" href="{{ route('interns.index') }}">Stagiaires</a></li>
                        @endif

                        @if($authUser->hasRole('Administrateur', 'Responsable de competence'))
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('internships.*') ? 'active' : '' }}" href="{{ route('internships.index') }}">Stages</a></li>
                        @endif

                        @if($authUser->hasRole('Administrateur', 'Responsable RH', 'Responsable de competence'))
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('absences.*') ? 'active' : '' }}" href="{{ route('absences.index') }}">Absences</a></li>
                        @endif

                        @if($authUser->hasRole('Responsable RH'))
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('rh.attestations.*') ? 'active' : '' }}" href="{{ route('rh.attestations.index') }}">Attestations</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('rh.archives.*') ? 'active' : '' }}" href="{{ route('rh.archives.index') }}">Archives</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('rh.profile') ? 'active' : '' }}" href="{{ route('rh.profile') }}">Profil RH</a></li>
                        @endif

                        @if($authUser->hasRole('Encadrant'))
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('supervisor.interns', 'supervisor.interns.show') ? 'active' : '' }}" href="{{ route('supervisor.interns') }}">Stagiaires</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('supervisor.internships', 'supervisor.internships.show') ? 'active' : '' }}" href="{{ route('supervisor.internships') }}">Stages</a></li>
                        @endif

                        @if(! $authUser->hasRole('Responsable de competence', 'Responsable RH'))
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">Tâches</a></li>
                        @endif
                        @if($authUser->hasRole('Administrateur', 'Responsable de competence', 'Encadrant', 'Stagiaire'))
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}" href="{{ route('requests.index') }}">Demandes</a></li>
                        @endif
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}" href="{{ route('messages.index') }}">Messages</a></li>
                    </ul>

                    <div class="navbar-user-actions d-flex align-items-center gap-2 text-white">
                        @php
                            $profileLabel = $authUser->full_name . ' (' . ($authUser->role?->name ?? '-') . ')';
                            if ($authUser->hasRole('Stagiaire') && $authUser->intern !== null) {
                                $isActiveIntern = $authUser->intern
                                    ->internships()
                                    ->where('status', 'en_cours')
                                    ->exists();
                                $profileLabel = $authUser->full_name
                                    . ' (' . ($authUser->role?->name ?? '-') . '/' . ($isActiveIntern ? 'actif' : 'inactif') . ')';
                            }
                        @endphp
                        <a class="btn btn-sm btn-outline-light navbar-profile-btn" href="{{ route('profile.edit') }}" title="{{ $profileLabel }}">
                            {{ $profileLabel }}
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-light text-nowrap" type="submit">Déconnexion</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    @endauth

    <main class="container py-4">
        @include('partials.alerts')
        @yield('content')
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.flatpickr) {
                flatpickr('.js-date', {
                    dateFormat: 'd/m/Y',
                    allowInput: true,
                    locale: 'fr'
                });
            }
        });
    </script>
    @stack('scripts')

    {{-- ══════════════════════════════════════════════════════════════════════
         AI Chat Popup — visible to all authenticated users
    ══════════════════════════════════════════════════════════════════════ --}}
    @auth
    <div id="ai-chat-fab" title="Assistant IA">
        <span class="ai-chat-fab-icon">🤖</span>
        <span class="ai-chat-fab-badge" id="chatUnreadBadge" style="display:none"></span>
    </div>

    <div id="ai-chat-panel" class="ai-chat-hidden">
        {{-- Header --}}
        <div class="ai-chat-header">
            <div class="d-flex align-items-center gap-2">
                <span style="font-size:1.2rem">🤖</span>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;line-height:1.2">Assistant IA</div>
                    <div style="font-size:.7rem;opacity:.75">Posez vos questions</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button class="ai-chat-header-btn" id="chatHistoryBtn" title="Historique des conversations">
                    <i class="bi bi-list"></i>
                </button>
                <button class="ai-chat-header-btn" id="chatClearBtn" title="Nouvelle conversation">
                    <i class="bi bi-plus-lg"></i>
                </button>
                <button class="ai-chat-header-btn" id="chatCloseBtn" title="Fermer">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        {{-- Sessions Overlay --}}
        <div id="ai-chat-history-overlay" class="ai-chat-hidden" style="position:absolute;top:3.5rem;left:0;width:100%;height:calc(100% - 3.5rem);background:#fff;z-index:10;display:flex;flex-direction:column;transition:transform 0.2s;transform:translateX(100%);">
            <div style="padding:0.75rem 1rem;background:#f7faf9;border-bottom:1px solid #e6edf1;display:flex;justify-content:space-between;align-items:center;">
                <h6 style="margin:0;font-size:0.85rem;color:#1f2933;font-weight:600;">Conversations récentes</h6>
                <button id="chatHistoryCloseBtn" style="background:none;border:none;color:#667085;cursor:pointer;"><i class="bi bi-x-lg"></i></button>
            </div>
            <div id="chatHistoryList" style="flex:1;overflow-y:auto;padding:0.5rem 0;">
                <div style="padding:1rem;text-align:center;font-size:0.8rem;color:#667085;">Chargement...</div>
            </div>
        </div>

        {{-- Model selector --}}
        <div class="ai-chat-model-bar">
            <label for="chatModelSelect" style="font-size:.72rem;font-weight:600;color:#667085;white-space:nowrap">Modèle :</label>
            <select id="chatModelSelect" class="ai-chat-model-select">
                <option value="">Chargement…</option>
            </select>
        </div>

        {{-- Messages area --}}
        <div class="ai-chat-messages" id="chatMessages">
            <div class="ai-chat-msg ai-chat-msg-bot">
                <div class="ai-chat-msg-content">
                    👋 Bonjour <strong>{{ auth()->user()->full_name }}</strong> ! Je suis votre assistant IA.<br>
                    Posez-moi des questions sur vos stagiaires, stages, tâches, absences…
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="ai-chat-input-bar">
            <textarea id="chatInput" class="ai-chat-input" rows="1"
                      placeholder="Écrire un message…" maxlength="2000"></textarea>
            <button id="chatSendBtn" class="ai-chat-send-btn" title="Envoyer" disabled>
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </div>

    <style>
        /* ── FAB button ──────────────────────────────────────── */
        #ai-chat-fab {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #1f6f64, #2f5b85);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 6px 24px rgba(31,111,100,.35);
            z-index: 9999;
            transition: transform .2s, box-shadow .2s;
        }
        #ai-chat-fab:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 32px rgba(31,111,100,.45);
        }
        .ai-chat-fab-icon { font-size: 1.5rem; }
        .ai-chat-fab-badge {
            position: absolute;
            top: -.2rem; right: -.2rem;
            width: 1rem; height: 1rem;
            border-radius: 50%;
            background: #dc3545;
            border: 2px solid #fff;
        }

        /* ── Panel ───────────────────────────────────────────── */
        #ai-chat-panel {
            position: fixed;
            bottom: 5.5rem;
            right: 1.5rem;
            width: min(420px, calc(100vw - 2rem));
            height: min(580px, calc(100vh - 8rem));
            border-radius: .85rem;
            background: #fff;
            border: 1px solid rgba(217,226,231,.9);
            box-shadow: 0 20px 60px rgba(31,41,51,.18);
            z-index: 9998;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: opacity .22s, transform .22s;
        }
        #ai-chat-panel.ai-chat-hidden {
            opacity: 0;
            transform: translateY(20px) scale(.96);
            pointer-events: none;
        }

        /* ── Header ──────────────────────────────────────────── */
        .ai-chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .65rem .85rem;
            background: linear-gradient(120deg, #163b45, #205f69 55%, #2f5b85);
            color: #fff;
            flex-shrink: 0;
        }
        .ai-chat-header-btn {
            background: rgba(255,255,255,.15);
            border: none;
            color: #fff;
            width: 1.8rem; height: 1.8rem;
            border-radius: .4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: .85rem;
            transition: background .15s;
        }
        .ai-chat-header-btn:hover { background: rgba(255,255,255,.28); }

        /* ── Model bar ───────────────────────────────────────── */
        .ai-chat-model-bar {
            display: flex;
            align-items: center;
            gap: .45rem;
            padding: .35rem .85rem;
            background: #f7faf9;
            border-bottom: 1px solid #e6edf1;
            flex-shrink: 0;
        }
        .ai-chat-model-select {
            flex: 1;
            font-size: .75rem;
            border: 1px solid #cfd8df;
            border-radius: .35rem;
            padding: .2rem .4rem;
            background: #fff;
            color: #1f2933;
            outline: none;
        }
        .ai-chat-model-select:focus { border-color: #1f6f64; }

        /* ── Messages ────────────────────────────────────────── */
        .ai-chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: .75rem .85rem;
            display: flex;
            flex-direction: column;
            gap: .55rem;
        }
        .ai-chat-msg { display: flex; max-width: 88%; }
        .ai-chat-msg-user { align-self: flex-end; }
        .ai-chat-msg-bot  { align-self: flex-start; }
        .ai-chat-msg-content {
            padding: .55rem .75rem;
            border-radius: .65rem;
            font-size: .84rem;
            line-height: 1.45;
            word-break: break-word;
        }
        .ai-chat-msg-user .ai-chat-msg-content {
            background: linear-gradient(135deg, #1f6f64, #2f5b85);
            color: #fff;
            border-bottom-right-radius: .2rem;
        }
        .ai-chat-msg-bot .ai-chat-msg-content {
            background: #f0f4f8;
            color: #1f2933;
            border-bottom-left-radius: .2rem;
        }
        .ai-chat-msg-bot .ai-chat-msg-content strong { color: #164e63; }
        .ai-chat-msg-bot .ai-chat-msg-content ul,
        .ai-chat-msg-bot .ai-chat-msg-content ol {
            margin: .3rem 0 .1rem 1rem;
            padding: 0;
        }

        /* ── Typing indicator ────────────────────────────────── */
        .ai-chat-typing {
            display: flex; gap: .25rem; padding: .5rem .75rem;
        }
        .ai-chat-typing-dot {
            width: .45rem; height: .45rem; border-radius: 50%;
            background: #1f6f64; opacity: .4;
            animation: chatBounce .9s infinite;
        }
        .ai-chat-typing-dot:nth-child(2) { animation-delay: .15s; }
        .ai-chat-typing-dot:nth-child(3) { animation-delay: .3s; }
        @keyframes chatBounce {
            0%, 80%, 100% { transform: translateY(0); opacity: .4; }
            40% { transform: translateY(-6px); opacity: 1; }
        }

        /* ── Input bar ───────────────────────────────────────── */
        .ai-chat-input-bar {
            display: flex;
            align-items: flex-end;
            gap: .45rem;
            padding: .55rem .75rem;
            border-top: 1px solid #e6edf1;
            background: #fafcfb;
            flex-shrink: 0;
        }
        .ai-chat-input {
            flex: 1;
            border: 1px solid #cfd8df;
            border-radius: .5rem;
            padding: .45rem .65rem;
            font-size: .84rem;
            resize: none;
            max-height: 5rem;
            line-height: 1.4;
            outline: none;
            font-family: inherit;
        }
        .ai-chat-input:focus { border-color: #1f6f64; box-shadow: 0 0 0 .15rem rgba(31,111,100,.12); }
        .ai-chat-send-btn {
            width: 2.2rem; height: 2.2rem;
            border-radius: .5rem;
            border: none;
            background: linear-gradient(135deg, #1f6f64, #2f5b85);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: opacity .15s, transform .15s;
        }
        .ai-chat-send-btn:disabled { opacity: .4; cursor: default; }
        .ai-chat-send-btn:not(:disabled):hover { transform: scale(1.08); }

        /* ── History Overlay ─────────────────────────────────── */
        #ai-chat-history-overlay.overlay-open {
            transform: translateX(0) !important;
        }
        .chat-history-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f0f4f8;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.15s;
        }
        .chat-history-item:hover {
            background: #f8fafc;
        }
        .chat-history-item.active {
            background: #e6f0f9;
            border-left: 3px solid #2f5b85;
        }
        .chat-history-item-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #1f2933;
            margin-bottom: 0.2rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .chat-history-item-meta {
            font-size: 0.7rem;
            color: #7b8794;
        }

        /* ── Responsive ─────────────────────────────────────── */
        @media (max-width: 576px) {
            #ai-chat-panel {
                right: 0; bottom: 0;
                width: 100%; height: 100vh;
                border-radius: 0;
            }
            #ai-chat-fab { bottom: 1rem; right: 1rem; }
        }
    </style>

    <script>
    $(function () {
        const CSRF       = $('meta[name="csrf-token"]').attr('content');
        const CHAT_URL   = '{{ route("chat.send") }}';
        const MODELS_URL = '{{ route("chat.models") }}';
        const CLEAR_URL  = '{{ route("chat.clear") }}';
        const HIST_URL   = '{{ route("chat.history") }}';
        const SESSIONS_URL = '{{ route("chat.sessions") }}';
        const RENAME_URL = '{{ route("chat.rename") }}';
        const USER_ID    = '{{ auth()->id() }}';
        
        const LS_SESSION = 'ai_chat_session_' + USER_ID;
        const LS_MODEL   = 'ai_chat_model_' + USER_ID;

        let sessionId    = localStorage.getItem(LS_SESSION);
        let isSending    = false;

        const $fab      = $('#ai-chat-fab');
        const $panel    = $('#ai-chat-panel');
        const $messages = $('#chatMessages');
        const $input    = $('#chatInput');
        const $sendBtn  = $('#chatSendBtn');
        const $modelSel = $('#chatModelSelect');
        const $overlay  = $('#ai-chat-history-overlay');
        const $histList = $('#chatHistoryList');

        // ── Toggle panel ────────────────────────────────────
        $fab.on('click', function () {
            $panel.toggleClass('ai-chat-hidden');
            if (!$panel.hasClass('ai-chat-hidden')) {
                $input.trigger('focus');
                scrollToBottom();
            }
        });
        $('#chatCloseBtn').on('click', () => $panel.addClass('ai-chat-hidden'));

        // ── History Overlay ─────────────────────────────────
        $('#chatHistoryBtn').on('click', function() {
            $overlay.addClass('overlay-open');
            loadSessionsList();
        });
        $('#chatHistoryCloseBtn').on('click', function() {
            $overlay.removeClass('overlay-open');
        });

        function loadSessionsList() {
            $histList.html('<div style="padding:1rem;text-align:center;font-size:0.8rem;color:#667085;">Chargement...</div>');
            $.getJSON(SESSIONS_URL, function(data) {
                $histList.empty();
                if (!data.sessions || data.sessions.length === 0) {
                    $histList.html('<div style="padding:1rem;text-align:center;font-size:0.8rem;color:#667085;">Aucune conversation trouvée.</div>');
                    return;
                }
                
                data.sessions.forEach(function(sess) {
                    const isActive = parseInt(sessionId) === sess.id;
                    const date = new Date(sess.updated_at).toLocaleString('fr-FR', {
                        day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
                    });
                    
                    const $item = $(`
                        <div class="chat-history-item ${isActive ? 'active' : ''}" data-id="${sess.id}">
                            <div style="flex:1;min-width:0;">
                                <div class="chat-history-item-title">${escapeHtml(sess.title || 'Nouvelle conversation')}</div>
                                <div class="chat-history-item-meta">${date} • ${sess.message_count} msg • ${sess.model ? sess.model.split('/').pop() : 'Default'}</div>
                            </div>
                            <div style="display:flex;align-items:center;">
                                <button class="btn btn-sm btn-link text-secondary p-0 rename-session-btn" title="Renommer" style="margin-left:5px;">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-link text-danger p-0 delete-session-btn" title="Supprimer" style="margin-left:10px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    `);
                    
                    // Click to resume
                    $item.on('click', function(e) {
                        if ($(e.target).closest('.delete-session-btn, .rename-session-btn').length > 0) return;
                        resumeSession(sess.id, sess.model);
                    });
                    
                    // Click to delete
                    $item.find('.delete-session-btn').on('click', function(e) {
                        e.stopPropagation();
                        if (confirm('Voulez-vous vraiment supprimer cette conversation ?')) {
                            $.post(CLEAR_URL, { _token: CSRF, session_id: sess.id }, function() {
                                if (parseInt(sessionId) === sess.id) {
                                    startNewSession();
                                }
                                loadSessionsList();
                            });
                        }
                    });

                    // Click to rename
                    $item.find('.rename-session-btn').on('click', function(e) {
                        e.stopPropagation();
                        const newTitle = prompt('Nouveau nom de la conversation :', sess.title || 'Nouvelle conversation');
                        if (newTitle && newTitle.trim() !== '') {
                            $.ajax({
                                url: RENAME_URL,
                                method: 'PUT',
                                data: { _token: CSRF, session_id: sess.id, title: newTitle.trim() }
                            }).done(function() {
                                loadSessionsList();
                            });
                        }
                    });
                    
                    $histList.append($item);
                });
            });
        }

        // ── Load models ─────────────────────────────────────
        $.getJSON(MODELS_URL, function (data) {
            $modelSel.empty();
            let savedModel = localStorage.getItem(LS_MODEL);
            
            // If the saved model is not in the available models list, fallback to default
            if (!savedModel || (data.models && !data.models.includes(savedModel))) {
                savedModel = data.default;
            }
            
            (data.models || []).forEach(function (m) {
                const label = m.split('/').pop();
                const isSelected = m === savedModel;
                $modelSel.append(`<option value="${m}" ${isSelected ? 'selected' : ''}>${label}</option>`);
            });
            
            // Update localStorage just in case it was invalid
            localStorage.setItem(LS_MODEL, savedModel);
        });

        // Save model choice when changed
        $modelSel.on('change', function() {
            localStorage.setItem(LS_MODEL, $(this).val());
        });

        // ── Load history for active session ─────────────────
        function loadActiveSessionMessages() {
            if (sessionId) {
                $.getJSON(HIST_URL, { session_id: sessionId })
                 .done(function(data) {
                     if (data.messages && data.messages.length > 0) {
                         // Keep the initial greeting message
                         const greeting = $messages.children().first().clone();
                         $messages.empty().append(greeting);
                         
                         data.messages.forEach(function(msg) {
                             const type = msg.role === 'user' ? 'user' : 'bot';
                             const content = type === 'user' ? escapeHtml(msg.content) : formatMarkdown(msg.content);
                             appendMsg(type, content);
                         });
                         scrollToBottom();
                     } else {
                         startNewSession();
                     }
                 })
                 .fail(function() {
                     startNewSession();
                 });
            }
        }
        
        loadActiveSessionMessages();

        function resumeSession(id, model) {
            sessionId = id;
            localStorage.setItem(LS_SESSION, id);
            
            if (model) {
                $modelSel.val(model);
                localStorage.setItem(LS_MODEL, model);
            }
            
            // Clear current messages and load new ones
            const greeting = $messages.children().first().clone();
            $messages.empty().append(greeting).append('<div style="text-align:center;padding:10px;font-size:0.8rem;color:#888;">Chargement...</div>');
            
            $overlay.removeClass('overlay-open');
            loadActiveSessionMessages();
        }

        function startNewSession() {
            sessionId = null;
            localStorage.removeItem(LS_SESSION);
            const greeting = $messages.children().first().clone();
            $messages.empty().append(greeting);
            $input.val('').trigger('input');
        }

        // ── Input handling ──────────────────────────────────
        $input.on('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 80) + 'px';
            $sendBtn.prop('disabled', !this.value.trim() || isSending);
        });

        $input.on('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!$sendBtn.prop('disabled')) sendMessage();
            }
        });

        $sendBtn.on('click', sendMessage);

        // ── Send message ────────────────────────────────────
        function sendMessage() {
            const text = $input.val().trim();
            if (!text || isSending) return;

            isSending = true;
            $sendBtn.prop('disabled', true);
            $input.val('').trigger('input');

            // User bubble
            appendMsg('user', escapeHtml(text));
            // Typing indicator
            const $typing = $(`
                <div class="ai-chat-msg ai-chat-msg-bot" id="chatTyping">
                    <div class="ai-chat-msg-content ai-chat-typing">
                        <span class="ai-chat-typing-dot"></span>
                        <span class="ai-chat-typing-dot"></span>
                        <span class="ai-chat-typing-dot"></span>
                    </div>
                </div>`);
            $messages.append($typing);
            scrollToBottom();

            $.ajax({
                url: CHAT_URL,
                method: 'POST',
                data: {
                    _token:     CSRF,
                    message:    text,
                    session_id: sessionId,
                    model:      $modelSel.val(),
                },
                timeout: 120000,
            })
            .done(function (resp) {
                $('#chatTyping').remove();
                if (resp.success && resp.reply) {
                    sessionId = resp.session_id;
                    localStorage.setItem(LS_SESSION, sessionId);
                    appendMsg('bot', formatMarkdown(resp.reply));
                } else {
                    appendMsg('bot', '⚠️ ' + (resp.error || 'Erreur inconnue.'));
                }
            })
            .fail(function (xhr) {
                $('#chatTyping').remove();
                let msg = 'Le service IA est indisponible.';
                if (xhr.responseJSON?.error) msg = xhr.responseJSON.error;
                appendMsg('bot', '⚠️ ' + msg);
            })
            .always(function () {
                isSending = false;
                $sendBtn.prop('disabled', !$input.val().trim());
                scrollToBottom();
            });
        }

        // ── Clear session (Start new) ───────────────────────
        $('#chatClearBtn').on('click', function () {
            startNewSession();
            $overlay.removeClass('overlay-open');
        });

        // ── Helpers ─────────────────────────────────────────
        function appendMsg(type, html) {
            const cls = type === 'user' ? 'ai-chat-msg-user' : 'ai-chat-msg-bot';
            $messages.append(`<div class="ai-chat-msg ${cls}"><div class="ai-chat-msg-content">${html}</div></div>`);
            scrollToBottom();
        }

        function scrollToBottom() {
            const el = $messages[0];
            setTimeout(() => el.scrollTop = el.scrollHeight, 50);
        }

        function escapeHtml(s) {
            return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function formatMarkdown(text) {
            // Very lightweight markdown → HTML (bold, lists, line breaks)
            let html = escapeHtml(text);
            // Bold
            html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            // Unordered list
            html = html.replace(/^[-•]\s+(.+)$/gm, '<li>$1</li>');
            html = html.replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>');
            // Numbered list
            html = html.replace(/^\d+\.\s+(.+)$/gm, '<li>$1</li>');
            // Line breaks
            html = html.replace(/\n/g, '<br>');
            // Clean up nested <ul> artifacts
            html = html.replace(/<\/ul>\s*<br>\s*<ul>/g, '');
            return html;
        }
    });
    </script>
    @endauth
</body>
</html>
