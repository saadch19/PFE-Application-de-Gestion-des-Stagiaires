@extends('layouts.app')

@section('title', 'Utilisateurs')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 mb-0">Gestion des utilisateurs</h1>
            <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">Nouvel utilisateur</a>
        </div>

        <form method="GET" id="usersSearchForm" class="row g-2 mb-3" onsubmit="event.preventDefault();">
            <div class="col-sm-10 col-md-7">
                <input type="text" name="search" id="usersSearchInput" value="{{ $search }}" class="form-control" placeholder="Rechercher (nom, email, role)" autocomplete="off">
            </div>
        </form>
        @push('scripts')
        <script>
        (function() {
            let timer;
            const input = document.getElementById('usersSearchInput');
            const form  = document.getElementById('usersSearchForm');
            const container = document.getElementById('usersTableContainer');
            if (!input || !form || !container) return;

            function loadTable(url) {
                container.style.opacity = '0.5';
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContainer = doc.getElementById('usersTableContainer');
                    if (newContainer) {
                        container.innerHTML = newContainer.innerHTML;
                        // Re-initialize tooltips inside the updated container
                        document.querySelectorAll('#usersTableContainer [title]').forEach(function (el) {
                            new bootstrap.Tooltip(el, { trigger: 'hover' });
                        });
                    }
                    container.style.opacity = '1';
                })
                .catch(err => {
                    console.error(err);
                    container.style.opacity = '1';
                });
            }

            input.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    const val = input.value;
                    const url = new URL(window.location.href);
                    url.searchParams.set('search', val);
                    url.searchParams.delete('page');
                    
                    loadTable(url.toString());
                    history.pushState({ search: val }, '', url.toString());
                }, 400);
            });

            container.addEventListener('click', function(e) {
                const link = e.target.closest('.pagination a');
                if (link) {
                    e.preventDefault();
                    const url = link.getAttribute('href');
                    loadTable(url);
                    history.pushState(null, '', url);
                }
            });

            window.addEventListener('popstate', function() {
                const url = new URL(window.location.href);
                input.value = url.searchParams.get('search') || '';
                loadTable(window.location.href);
            });
        })();
        </script>
        @endpush

        <div id="usersTableContainer">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>État</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->full_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role?->name }}</td>
                                <td>
                                    @statusBadge($user->is_active ? 'valide' : 'archive')
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Supprimer" data-confirm="Supprimer cet utilisateur ?"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Aucun utilisateur.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
