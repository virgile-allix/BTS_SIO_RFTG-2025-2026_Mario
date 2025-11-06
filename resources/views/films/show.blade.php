@extends('layouts.app')

@section('content')
<style>
    .retro-90s-show {
        background: #e8e8e8;
        border: 3px solid #2c3e50;
        box-shadow: 5px 5px 0px #2c3e50;
        font-family: 'Courier New', monospace;
    }

    .retro-header-show {
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

    .retro-card {
        border: 2px solid #2c3e50 !important;
        box-shadow: 4px 4px 0px rgba(44, 62, 80, 0.2);
        background: white;
    }

    .retro-card-header {
        background: #5e72e4 !important;
        color: white !important;
        border-bottom: 2px solid #2c3e50 !important;
        font-weight: bold;
        text-transform: uppercase;
        font-family: 'Courier New', monospace;
    }

    .retro-badge-show {
        border: 2px solid #2c3e50;
        box-shadow: 2px 2px 0px #2c3e50;
        font-weight: bold;
        padding: 8px 15px;
        font-family: 'Courier New', monospace;
    }

    .retro-badge-G {
        background: #2ecc71 !important;
        color: white !important;
    }

    .retro-badge-PG {
        background: #3498db !important;
        color: white !important;
    }

    .retro-badge-PG-13 {
        background: #f39c12 !important;
        color: white !important;
    }

    .retro-badge-R {
        background: #e74c3c !important;
        color: white !important;
    }

    .retro-badge-NC-17 {
        background: #2c3e50 !important;
        color: white !important;
    }

    .retro-info-box {
        background: #f8f9fa;
        border: 2px solid #2c3e50;
        box-shadow: 3px 3px 0px rgba(44, 62, 80, 0.2);
        padding: 15px;
        font-weight: bold;
        font-family: 'Courier New', monospace;
    }

    .retro-feature-badge {
        background: #5e72e4;
        color: white;
        border: 2px solid #2c3e50;
        box-shadow: 2px 2px 0px rgba(44, 62, 80, 0.3);
        padding: 8px 15px;
        font-weight: bold;
        display: inline-block;
        margin: 5px;
    }

    .retro-btn-back {
        background: #95a5a6 !important;
        color: white !important;
        border: 2px solid #2c3e50 !important;
        box-shadow: 3px 3px 0px #2c3e50 !important;
        font-weight: bold;
        text-transform: uppercase;
    }

    .retro-btn-edit {
        background: #ffc107 !important;
        color: #2c3e50 !important;
        border: 2px solid #2c3e50 !important;
        box-shadow: 3px 3px 0px #2c3e50 !important;
        font-weight: bold;
        text-transform: uppercase;
    }

    .retro-btn-delete-show {
        background: #e74c3c !important;
        color: white !important;
        border: 2px solid #2c3e50 !important;
        box-shadow: 3px 3px 0px #2c3e50 !important;
        font-weight: bold;
        text-transform: uppercase;
    }

    .retro-btn-back:hover, .retro-btn-edit:hover, .retro-btn-delete-show:hover {
        transform: translate(-1px, -1px);
        box-shadow: 4px 4px 0px #2c3e50 !important;
    }
</style>

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
                                        <strong>Langue:</strong><br>
                                        ID {{ $film['languageId'] ?? 'N/A' }}
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
                                          onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer « {{ $film['title'] ?? 'ce film' }} » ?')">
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
