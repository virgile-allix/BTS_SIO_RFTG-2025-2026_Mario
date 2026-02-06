
@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/mario.css') }}">
@endsection

@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="retro-90s mb-4">
                <div class="retro-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">🎬 GESTION DU CATALOGUE DE FILMS 🎬</h3>
                        <a href="{{ route('films.create') }}" class="retro-btn-add">
                            ➕ Ajouter un film
                        </a>
                    </div>
                </div>

                <div class="p-4" style="background: #ffffffff;">
                    @if(session('success'))
                        <div class="alert alert-success retro-alert" role="alert">
                            ✅ {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger retro-alert" role="alert">
                            ❌ {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (empty($films))
                        <div class="alert alert-warning retro-alert">
                            ⚠️ Aucun film disponible ou erreur lors de la récupération des données de l'API.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table retro-table">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 60px;">ID</th>
                                        <th>Titre</th>
                                        <th>Description</th>
                                        <th class="text-center" style="width: 80px;">Année</th>
                                        <th class="text-center" style="width: 100px;">Durée</th>
                                        <th class="text-center" style="width: 100px;">Note</th>
                                        <th class="text-center" style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($films as $film)
                                        <tr>
                                            <td class="text-center">
                                                <span class="retro-badge retro-badge-id">{{ $film['filmId'] ?? $film['id'] ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="retro-title">{{ $film['title'] ?? 'Sans titre' }}</span>
                                            </td>
                                            <td>
                                                <small style="font-family: 'Courier New', monospace;">{{ Str::limit($film['description'] ?? 'Aucune description', 100) }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="retro-badge retro-badge-year">{{ $film['releaseYear'] ?? 'N/A' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <strong>⏱️ {{ $film['length'] ?? 'N/A' }} min</strong>
                                            </td>
                                            <td class="text-center">
                                                @if(isset($film['rating']))
                                                    <span class="retro-badge retro-badge-{{ $film['rating'] }}">{{ $film['rating'] }}</span>
                                                @else
                                                    <span class="retro-badge">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="{{ route('films.show', $film['filmId'] ?? $film['id']) }}"
                                                       class="retro-btn retro-btn-view"
                                                       title="Voir les détails">
                                                       👁️
                                                    </a>
                                                    <a href="{{ route('films.edit', $film['filmId'] ?? $film['id']) }}"
                                                       class="retro-btn retro-btn-edit"
                                                       title="Modifier">
                                                       ✏️
                                                    </a>
                                                    <form action="{{ route('films.destroy', $film['filmId'] ?? $film['id']) }}"
                                                          method="POST"
                                                          style="display: inline;"
                                                          onsubmit="event.preventDefault(); confirmDelete('{{ addslashes($film['title'] ?? 'ce film') }}', event);">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="retro-btn retro-btn-delete"
                                                                title="Supprimer">
                                                            🗑️
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 p-3" style="background: #ffff00; border: 3px solid #000; box-shadow: 4px 4px 0px #000;">
                            <p class="mb-0" style="font-weight: bold; font-family: 'Courier New', monospace;">
                                📊 TOTAL : <span style="color: #ff00ff;">{{ count($films) }}</span> FILM(S)
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/film.js') }}"></script>
@endpush