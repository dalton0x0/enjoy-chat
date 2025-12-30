@extends('layouts.app')

@section('title', 'Mon Profil')

@section('content')
    <div class="container">
        <!-- Section Avatar -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-4">
                        <img src="{{ Auth::user()->avatar_url }}"
                             alt="{{ Auth::user()->name }}"
                             class="rounded-circle mb-3"
                             style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #4f46e5;">
                        <h3>{{ Auth::user()->name }}</h3>
                        <p class="text-muted">{{ Auth::user()->email }}</p>

                        @if(Auth::user()->isOnline())
                            <span class="badge bg-success">
                            <i class="bi bi-circle-fill"></i> En ligne
                        </span>
                        @else
                            <span class="badge bg-secondary">
                            <i class="bi bi-circle"></i> {{ Auth::user()->status }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mt-4">
            <!-- Informations du profil -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-circle"></i> Informations du profil</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label">Nom</label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', Auth::user()->name) }}"
                                       required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', Auth::user()->email) }}"
                                       required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Mettre à jour
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Changer le mot de passe -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-key"></i> Changer le mot de passe</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.password.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mot de passe actuel</label>
                                <input type="password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       id="current_password"
                                       name="current_password"
                                       required>
                                @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Nouveau mot de passe</label>
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       required>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                                <input type="password"
                                       class="form-control"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-secondary">
                                    <i class="bi bi-shield-check"></i> Changer le mot de passe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations du compte -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informations du compte</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Membre depuis :</strong> {{ Auth::user()->created_at->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Dernière activité :</strong> {{ Auth::user()->status }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
