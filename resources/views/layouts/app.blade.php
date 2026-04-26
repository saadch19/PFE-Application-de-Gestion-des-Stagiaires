<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestion des Stagiaires')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --bg-gradient-start: #f6f6f0;
            --bg-gradient-end: #dbeee3;
            --surface: #ffffff;
            --text-main: #21313d;
            --brand: #2e6f5f;
            --brand-alt: #dd6b4d;
        }

        body {
            min-height: 100vh;
            background: radial-gradient(circle at 10% 0%, var(--bg-gradient-end), var(--bg-gradient-start));
            color: var(--text-main);
        }

        .app-navbar {
            background: linear-gradient(120deg, #1f4d43, #335f8f);
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
    </style>
</head>
<body>
    @auth
        @php $authUser = auth()->user(); @endphp
        <nav class="navbar navbar-expand-lg navbar-dark app-navbar shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">
                    <i class="bi bi-people-fill me-1"></i> Gestion des Stagiaires
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>

                        @if($authUser->hasRole('Administrateur'))
                            <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Utilisateurs</a></li>
                        @endif

                        @if($authUser->hasRole('Administrateur', 'Responsable de competence'))
                            <li class="nav-item"><a class="nav-link" href="{{ route('interns.index') }}">Stagiaires</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('internships.index') }}">Stages</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('absences.index') }}">Absences</a></li>
                        @endif

                        <li class="nav-item"><a class="nav-link" href="{{ route('tasks.index') }}">Taches</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('requests.index') }}">Demandes</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('messages.index') }}">Messages</a></li>
                    </ul>

                    <div class="d-flex align-items-center gap-3 text-white">
                        <small>{{ $authUser->full_name }} ({{ $authUser->role?->name }})</small>
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
    @stack('scripts')
</body>
</html>
