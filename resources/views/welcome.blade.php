@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
    <div class="container vh-100">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8 text-center">
                <h1 class="display-4 mb-4">
                    <i class="bi bi-chat-heart"></i> Bienvenue sur {{ config('app.name') }}
                </h1>
                <p class="lead mb-4">
                    Une application de messagerie instantanée moderne et intuitive.
                </p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="#" class="btn btn-primary btn-lg px-4 me-md-2">
                        Commencer
                    </a>
                    <a href="#" class="btn btn-outline-secondary btn-lg px-4">
                        Se connecter
                    </a>
                </div>

                <div class="row mt-5">
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <i class="bi bi-lightning-charge fs-1 text-primary"></i>
                                <h5 class="card-title mt-3">Rapide</h5>
                                <p class="card-text">Messages en temps réel</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <i class="bi bi-shield-check fs-1 text-success"></i>
                                <h5 class="card-title mt-3">Sécurisé</h5>
                                <p class="card-text">Vos données protégées</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <i class="bi bi-phone fs-1 text-info"></i>
                                <h5 class="card-title mt-3">Responsive</h5>
                                <p class="card-text">Sur tous vos appareils</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
