@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/mario.css') }}">
@endsection

@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-11">
            <div class="retro-90s-show mb-4">
                <div class="retro-header-show">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">🎬 {{ strtoupper($film['title'] ?? 'Sans titre') }}</h2>
                            <p class="mb-0">
                                📅 {{ $film['releaseYear'] ?? 'N/A' }} • ⏱️ {{ $film['length'] ?? 'N/A' }} minutes
                            </p>
                        </div>
                        <div>
                            @if(isset($film['rating']))
                                <span class="retro-badge-show retro-badge-{{ $film['rating'] }}">{{ $film['rating'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-4" style="background: white;">
                    @if(session('success'))
                        <div class="alert alert-success" style="border: 3px solid #000; box-shadow: 4px 4px 0px #000; font-weight: bold;">
                            ✅ {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger" style="border: 3px solid #000; box-shadow: 4px 4px 0px #000; font-weight: bold;">
                            ❌ {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row g-4 mb-4">
                        <!-- Description -->
                        <div class="col-md-8">
                            <div class="retro-card">
                                <div class="retro-card-header p-3">
                                    📝 Description
                                </div>
                                <div class="p-3">
                                    <p style="font-family: 'Courier New', monospace; font-size: 14px;">
                                        {{ $film['description'] ?? 'Aucune description disponible.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Informations -->
                        <div class="col-md-4">
                            <div class="retro-card">
                                <div class="retro-card-header p-3">
                                    ℹ️ Informations
                                </div>
                                <div class="p-3">
                                    <div class="mb-3">
                                        <strong>ID du film:</strong><br>
                                        <span style="background: magenta; color: white; padding: 5px 10px; border: 2px solid #000; box-shadow: 2px 2px 0px #000; display: inline-block; margin-top: 5px;">
                                            {{ $film['filmId'] ?? $film['id'] ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Langue originale:</strong><br>
                                        {{ $film['originalLanguageName'] ?? ($film['originalLanguageId'] ? 'ID ' . $film['originalLanguageId'] : 'N/A') }}
                                    </div>
                                    <div>
                                        <strong>Coût de remplacement:</strong><br>
                                        <span style="color: #2ecc71; font-size: 24px; font-weight: bold;">{{ $film['replacementCost'] ?? 'N/A' }} €</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <!-- Location -->
                        <div class="col-md-6">
                            <div class="retro-card">
                                <div class="retro-card-header p-3">
                                    🛒 Location
                                </div>
                                <div class="p-3">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="retro-info-box text-center">
                                                <small>DURÉE DE LOCATION</small>
                                                <h3 class="mb-0" style="color: #5e72e4;">{{ $film['rentalDuration'] ?? 'N/A' }}</h3>
                                                <small>JOURS</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="retro-info-box text-center">
                                                <small>TARIF</small>
                                                <h3 class="mb-0" style="color: #2ecc71;">{{ $film['rentalRate'] ?? 'N/A' }} €</h3>
                                                <small>PAR LOCATION</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Caractéristiques -->
                        <div class="col-md-6">
                            <div class="retro-card">
                                <div class="retro-card-header p-3">
                                    ⭐ Caractéristiques spéciales
                                </div>
                                <div class="p-3">
                                    @if(isset($film['specialFeatures']) && $film['specialFeatures'])
                                        @foreach(explode(',', $film['specialFeatures']) as $feature)
                                            <span class="retro-feature-badge">
                                                ✓ {{ strtoupper(trim($feature)) }}
                                            </span>
                                        @endforeach
                                    @else
                                        <p style="font-family: 'Courier New', monospace;">❌ Aucune caractéristique spéciale</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catégories et Acteurs -->
                    <div class="row g-4 mb-4">
                        <!-- Catégories -->
                        <div class="col-md-6">
                            <div class="retro-card">
                                <div class="retro-card-header p-3">
                                    📂 Catégories
                                </div>
                                <div class="p-3">
                                    @if(isset($film['categories']) && count($film['categories']) > 0)
                                        @foreach($film['categories'] as $category)
                                            <span class="retro-feature-badge" style="background: #3498db;">
                                                {{ strtoupper($category['name'] ?? 'N/A') }}
                                            </span>
                                        @endforeach
                                    @else
                                        <p style="font-family: 'Courier New', monospace;">❌ Aucune catégorie</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Acteurs -->
                        <div class="col-md-6">
                            <div class="retro-card">
                                <div class="retro-card-header p-3">
                                    🎭 Acteurs
                                </div>
                                <div class="p-3">
                                    @if(isset($film['actors']) && count($film['actors']) > 0)
                                        @foreach($film['actors'] as $actor)
                                            <span class="retro-feature-badge" style="background: #e74c3c;">
                                                {{ strtoupper(($actor['firstName'] ?? '') . ' ' . ($actor['lastName'] ?? '')) }}
                                            </span>
                                        @endforeach
                                    @else
                                        <p style="font-family: 'Courier New', monospace;">❌ Aucun acteur</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Réalisateurs -->
                    @if(isset($film['directors']) && count($film['directors']) > 0)
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="retro-card">
                                <div class="retro-card-header p-3">
                                    🎬 Réalisateurs
                                </div>
                                <div class="p-3">
                                    @foreach($film['directors'] as $director)
                                        <span class="retro-feature-badge" style="background: #f39c12;">
                                            {{ strtoupper(($director['firstName'] ?? '') . ' ' . ($director['lastName'] ?? '')) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="retro-card">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('films.index') }}" class="retro-btn-back px-4 py-2">
                                    ⬅️ Retour à la liste
                                </a>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('films.edit', $film['filmId'] ?? $film['id']) }}" class="retro-btn-edit px-4 py-2">
                                        ✏️ Modifier
                                    </a>
                                    <form action="{{ route('films.destroy', $film['filmId'] ?? $film['id']) }}"
                                          method="POST"
                                          style="display: inline;"
                                          onsubmit="event.preventDefault(); confirmDelete('{{ addslashes($film['title'] ?? 'ce film') }}', event);">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="retro-btn-delete-show px-4 py-2">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <hr style="border: 2px solid #000; margin: 20px 0;">
                            <p style="font-family: 'Courier New', monospace; font-weight: bold; margin: 0;">
                                🕐 Dernière mise à jour : {{ $film['lastUpdate'] ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/film.js') }}"></script>
@endsection
