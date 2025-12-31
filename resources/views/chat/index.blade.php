@extends('layouts.app')

@section('title', 'Messages')

@section('content')
    <div class="container-fluid h-100">
        <div class="row h-100">
            <!-- Sidebar - List of conversations -->
            <div class="col-md-4 col-lg-3 px-0 border-end bg-white">
                <div class="d-flex flex-column h-100">
                    <!-- Header -->
                    <div class="p-3 border-bottom">
                        <h5 class="mb-3"><i class="bi bi-chat-dots"></i> Messages</h5>

                        <!-- Search users button -->
                        <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#searchUsersModal">
                            <i class="bi bi-plus-circle"></i> Nouvelle conversation
                        </button>
                    </div>

                    <!-- Conversations list -->
                    <div class="overflow-auto flex-grow-1">
                        @forelse($conversations as $conv)
                            <a href="{{ route('chat.show', $conv['id']) }}"
                               class="text-decoration-none">
                                <div class="conversation-item p-3 border-bottom d-flex align-items-center">
                                    <!-- Avatar -->
                                    <div class="position-relative me-3">
                                        <img src="{{ $conv['avatar'] }}"
                                             alt="{{ $conv['name'] }}"
                                             class="rounded-circle"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        @if($conv['is_online'])
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle"></span>
                                        @endif
                                    </div>

                                    <!-- Conversation info -->
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 text-truncate">{{ $conv['name'] }}</h6>
                                            <small class="text-muted">{{ $conv['last_message_time'] }}</small>
                                        </div>
                                        <p class="mb-0 text-muted text-truncate small">
                                            {{ $conv['last_message'] ?? 'Aucun message' }}
                                        </p>
                                    </div>

                                    <!-- Unread badge -->
                                    @if($conv['unread_count'] > 0)
                                        <span class="badge bg-primary rounded-pill ms-2">
                                        {{ $conv['unread_count'] }}
                                    </span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-5">
                                <i class="bi bi-chat-left-text text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Aucune conversation</p>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#searchUsersModal">
                                    Démarrer une conversation
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Main content - Empty state -->
            <div class="col-md-8 col-lg-9 d-flex align-items-center justify-content-center bg-light">
                <div class="text-center">
                    <i class="bi bi-chat-dots display-1 text-muted"></i>
                    <h4 class="mt-4 text-muted">Sélectionnez une conversation</h4>
                    <p class="text-muted">Choisissez une conversation dans la liste pour commencer à discuter</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Users Modal -->
    <div class="modal fade" id="searchUsersModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-search"></i> Rechercher un utilisateur
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Search input -->
                    <div class="mb-3">
                        <input type="text"
                               class="form-control"
                               id="userSearch"
                               placeholder="Rechercher par nom ou email...">
                    </div>

                    <!-- Users list -->
                    <div id="usersList">
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-search"></i>
                            <p class="mb-0">Recherchez un utilisateur pour démarrer une conversation</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .conversation-item {
            transition: background-color 0.2s;
            cursor: pointer;
        }

        .conversation-item:hover {
            background-color: #f8f9fa;
        }

        .user-search-item {
            transition: background-color 0.2s;
            cursor: pointer;
        }

        .user-search-item:hover {
            background-color: #f8f9fa;
        }
    </style>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('userSearch');
            const usersList = document.getElementById('usersList');
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);

                const search = this.value.trim();

                if (search.length === 0) {
                    usersList.innerHTML = `
                <div class="text-center text-muted py-3">
                    <i class="bi bi-search"></i>
                    <p class="mb-0">Recherchez un utilisateur pour démarrer une conversation</p>
                </div>
            `;
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(`{{ route('chat.users.search') }}?search=${encodeURIComponent(search)}`)
                        .then(response => response.json())
                        .then(users => {
                            if (users.length === 0) {
                                usersList.innerHTML = `
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-person-x"></i>
                                <p class="mb-0">Aucun utilisateur trouvé</p>
                            </div>
                        `;
                                return;
                            }

                            usersList.innerHTML = users.map(user => `
                        <form method="POST" action="{{ route('chat.start') }}">
                            @csrf
                            <input type="hidden" name="user_id" value="${user.id}">
                            <button type="submit" class="w-100 border-0 bg-white text-start user-search-item p-3 border-bottom d-flex align-items-center">
                                <div class="position-relative me-3">
                                    <img src="${user.avatar}"
                                         alt="${user.name}"
                                         class="rounded-circle"
                                         style="width: 45px; height: 45px; object-fit: cover;">
                                    ${user.is_online ? '<span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle"></span>' : ''}
                                </div>
                                <div>
                                    <h6 class="mb-0">${user.name}</h6>
                                    <small class="text-muted">${user.email}</small>
                                </div>
                            </button>
                        </form>
                    `).join('');
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            usersList.innerHTML = `
                        <div class="alert alert-danger">
                            Une erreur est survenue lors de la recherche.
                        </div>
                    `;
                        });
                }, 300);
            });
        });
    </script>
@endpush
