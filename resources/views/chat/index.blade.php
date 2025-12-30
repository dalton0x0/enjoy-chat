@extends('layouts.app')

@section('title', 'Messages')

@section('content')
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8 text-center">
                <div class="card shadow-sm">
                    <div class="card-body py-5">
                        <i class="bi bi-chat-dots display-1 text-primary"></i>
                        <h2 class="mt-4">Interface de chat</h2>
                        <p class="text-muted">Cette section sera développée dans les prochaines étapes</p>
                        <p>Bienvenue <strong>{{ Auth::user()->name }}</strong> !</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
