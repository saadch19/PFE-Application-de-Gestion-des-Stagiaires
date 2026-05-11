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

    <style>
        :root {
            --bg-gradient-start: #eef9f1;
            --bg-gradient-end: #cfe9d8;
            --surface: #ffffff;
            --text-main: #21313d;
            --brand: #2e6f5f;
            --brand-alt: #dd6b4d;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at 15% 10%, rgba(77, 184, 112, 0.32), transparent 28rem),
                radial-gradient(circle at 85% 20%, rgba(45, 125, 87, 0.26), transparent 30rem),
                radial-gradient(circle at 70% 90%, rgba(151, 216, 170, 0.32), transparent 26rem),
                linear-gradient(135deg, var(--bg-gradient-start), var(--bg-gradient-end));
            background-size: 135% 135%, 130% 130%, 140% 140%, 100% 100%;
            background-attachment: fixed;
            color: var(--text-main);
            overflow-x: hidden;
            position: relative;
            animation: softGreenBackground 12s ease-in-out infinite alternate;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            z-index: -1;
            border-radius: 50%;
            pointer-events: none;
        }

        body::before {
            top: 8rem;
            left: -7rem;
            width: 22rem;
            height: 22rem;
            background: rgba(112, 204, 135, 0.18);
            filter: blur(1rem);
            animation: floatingGreenHalo 10s ease-in-out infinite;
        }

        body::after {
            right: -8rem;
            bottom: -7rem;
            width: 26rem;
            height: 26rem;
            background: rgba(45, 125, 87, 0.14);
            filter: blur(1.2rem);
            animation: floatingGreenHalo 14s ease-in-out infinite reverse;
        }

        .app-navbar {
            background: linear-gradient(120deg, #1f4d43, #335f8f);
        }

        .app-navbar .nav-link {
            border-radius: 0.5rem;
            color: rgba(255, 255, 255, 0.78);
            font-weight: 500;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            transition: color 0.2s ease, background-color 0.2s ease;
        }

        .app-navbar .nav-link:hover,
        .app-navbar .nav-link.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.18);
        }

        .app-navbar .nav-link.active {
            box-shadow: inset 0 -3px 0 #ddf36a;
        }

        .card-soft {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 15px 45px rgba(33, 49, 61, 0.08);
            background: var(--surface);
        }

        .stat-card {
            border-left: 5px solid var(--brand);
            transition: transform 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .fade-in {
            animation: fadeInUp 0.5s ease both;
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

        @keyframes softGreenBackground {
            from {
                background-position: 0% 0%, 100% 10%, 70% 100%, 0 0;
            }
            to {
                background-position: 18% 12%, 78% 28%, 88% 76%, 0 0;
            }
        }

        @keyframes floatingGreenHalo {
            0% {
                transform: translate3d(0, 0, 0) scale(1);
            }
            50% {
                transform: translate3d(2rem, 1.5rem, 0) scale(1.08);
            }
            100% {
                transform: translate3d(0.5rem, -1rem, 0) scale(0.96);
            }
        }
    </style>
</head>
<body>
    @auth
        @php $authUser = auth()->user(); @endphp
        <nav class="navbar navbar-expand-lg navbar-dark app-navbar shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-semibold d-flex align-items-center" href="{{ route('dashboard') }}">
                    <img src="{{ asset('images/ALTEN-Logo.wine.png') }}" alt="Alten Logo" style="height: 68px; margin-right: 15px;">
                    <span><i class="bi bi-people-fill me-1"></i> Gestion des Stagiaires</span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>

                        @if($authUser->hasRole('Administrateur'))
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Utilisateurs</a></li>
                        @endif

                        @if($authUser->hasRole('Administrateur', 'Responsable de competence'))
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('interns.*') ? 'active' : '' }}" href="{{ route('interns.index') }}">Stagiaires</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('internships.*') ? 'active' : '' }}" href="{{ route('internships.index') }}">Stages</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('absences.*') ? 'active' : '' }}" href="{{ route('absences.index') }}">Absences</a></li>
                        @endif

                        @if($authUser->hasRole('Encadrant'))
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('supervisor.interns') ? 'active' : '' }}" href="{{ route('supervisor.interns') }}">Stagiaires</a></li>
                        @endif

                        @if(! $authUser->hasRole('Responsable de competence'))
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">Taches</a></li>
                        @endif
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}" href="{{ route('requests.index') }}">Demandes</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}" href="{{ route('messages.index') }}">Messages</a></li>
                    </ul>

                    <div class="d-flex align-items-center gap-3 text-white">
                        <a class="btn btn-sm btn-outline-light" href="{{ route('profile.edit') }}">
                            {{ $authUser->full_name }} ({{ $authUser->role?->name }})
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-light" type="submit">Se deconnecter</button>
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
</body>
</html>
