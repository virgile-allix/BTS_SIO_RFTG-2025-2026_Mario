
@extends('layouts.app')

@section('content')
<style>
    .retro-90s {
        background: #e8e8e8;
        border: 3px solid #2c3e50;
        box-shadow: 5px 5px 0px #2c3e50;
        font-family: 'Courier New', monospace;
    }

    .retro-header {
        background: repeating-linear-gradient(
            45deg,
            #5e72e4,
            #5e72e4 15px,
            #4c63d2 15px,
            #4c63d2 30px
        );
        border-bottom: 3px solid #2c3e50;
        color: white;
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        padding: 20px;
    }

    .retro-table {
        border: 2px solid #2c3e50;
        background: white;
    }

    .retro-table thead {
        background: repeating-linear-gradient(
            45deg,
            #5e72e4,
            #5e72e4 10px,
            #4c63d2 10px,
            #4c63d2 20px
        );
        border-bottom: 2px solid #2c3e50;
    }

    .retro-table thead th {
        color: white;
        font-weight: bold;
        text-transform: uppercase;
        border-right: 1px solid rgba(255,255,255,0.2) !important;
        padding: 15px 10px;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        font-size: 12px;
    }

    .retro-table tbody tr {
        border-bottom: 1px solid #ddd;
        background: white;
    }

    .retro-table tbody tr:nth-child(odd) {
        background: #f8f9fa;
    }

    .retro-table tbody tr:hover {
        background: #e3f2fd !important;
        transform: translateX(2px);
        box-shadow: 0 2px 8px rgba(94, 114, 228, 0.2);
    }

    .retro-table tbody td {
        border-right: 1px solid #eee;
        padding: 12px 8px;
        vertical-align: middle;
    }

    .retro-btn {
        border: 2px solid #2c3e50 !important;
        font-weight: bold !important;
        box-shadow: 3px 3px 0px #2c3e50 !important;
        transition: all 0.2s;
        padding: 8px 12px !important;
        min-width: 45px !important;
        font-size: 14px !important;
    }

    .retro-btn:hover {
        transform: translate(-1px, -1px);
        box-shadow: 4px 4px 0px #2c3e50 !important;
    }

    .retro-btn:active {
        transform: translate(1px, 1px);
        box-shadow: 2px 2px 0px #2c3e50 !important;
    }

    .retro-btn-view {
        background: #5e72e4 !important;
        color: white !important;
    }

    .retro-btn-edit {
        background: #ffc107 !important;
        color: #2c3e50 !important;
    }

    .retro-btn-delete {
        background: #e74c3c !important;
        color: white !important;
    }

    .retro-btn-add {
        background: #2ecc71 !important;
        color: white !important;
        border: 2px solid #2c3e50 !important;
        box-shadow: 3px 3px 0px #2c3e50 !important;
        font-weight: bold;
        text-transform: uppercase;
        padding: 10px 20px;
    }

    .retro-badge {
        border: 2px solid #2c3e50;
        box-shadow: 2px 2px 0px #2c3e50;
        font-weight: bold;
        padding: 5px 10px;
        font-family: 'Courier New', monospace;
        font-size: 11px;
    }

    .retro-badge-id {
        background: #5e72e4;
        color: white;
    }

    .retro-badge-year {
        background: #ffc107;
        color: #2c3e50;
    }

    .retro-badge-G {
        background: #2ecc71;
        color: white;
    }

    .retro-badge-PG {
        background: #3498db;
        color: white;
    }

    .retro-badge-PG-13 {
        background: #f39c12;
        color: white;
    }

    .retro-badge-R {
        background: #e74c3c;
        color: white;
    }

    .retro-badge-NC-17 {
        background: #2c3e50;
        color: white;
    }

    .retro-title {
        color: #5e72e4;
        font-weight: bold;
        text-decoration: none;
    }

    .retro-alert {
        border: 2px solid #2c3e50;
        box-shadow: 4px 4px 0px rgba(44, 62, 80, 0.2);
        font-weight: bold;
    }
</style>

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

                <div class="p-4" style="background: #fff;">
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
                                                          onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer « {{ $film['title'] ?? 'ce film' }} » ?')">
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