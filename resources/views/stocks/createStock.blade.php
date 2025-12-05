@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="retro-header-form d-flex justify-content-between align-items-center p-3">
                <h2 class="retro-title mb-0">
                    ➕ AJOUTER UN NOUVEAU STOCK
                </h2>
                <a href="{{ route('stocks.index') }}" class="retro-btn-back">
                    ⬅️ Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Formulaire -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="retro-90s-form p-4">
                @if ($errors->any())
                    <div class="alert alert-danger retro-alert mb-4">
                        <strong>⚠️ Erreurs de validation :</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="stockForm" method="POST" action="{{ route('stocks.store') }}" class="needs-validation" novalidate>
                    @csrf

                    <!-- Film -->
                    <div class="form-group mb-4">
                        <label for="film_id" class="retro-form-label">
                            🎬 Film <span class="text-danger">*</span>
                        </label>
                        <select
                            class="form-control retro-form-control @error('film_id') is-invalid @enderror"
                            id="film_id"
                            name="film_id"
                            required>
                            <option value="">-- Sélectionner un film --</option>
                            @foreach($films as $film)
                                <option
                                    value="{{ $film['filmId'] ?? $film['id'] ?? '' }}"
                                    {{ old('film_id') == ($film['filmId'] ?? $film['id'] ?? '') ? 'selected' : '' }}>
                                    {{ $film['title'] ?? 'Sans titre' }}
                                    ({{ $film['releaseYear'] ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @error('film_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted retro-text-muted">
                            Choisissez le film à ajouter en stock
                        </small>
                    </div>

                    <!-- Magasin -->
                    <div class="form-group mb-4">
                        <label for="store_id" class="retro-form-label">
                            🏪 Magasin <span class="text-danger">*</span>
                        </label>
                        <select
                            class="form-control retro-form-control @error('store_id') is-invalid @enderror"
                            id="store_id"
                            name="store_id"
                            required>
                            <option value="">-- Sélectionner un magasin --</option>
                            @foreach($stores as $store)
                                <option
                                    value="{{ $store['storeId'] ?? $store['id'] ?? '' }}"
                                    {{ old('store_id') == ($store['storeId'] ?? $store['id'] ?? '') ? 'selected' : '' }}>
                                    Boutique #{{ $store['storeId'] ?? $store['id'] ?? '' }}
                                    @if(isset($store['address']))
                                        - {{ $store['address'] }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('store_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted retro-text-muted">
                            Sélectionnez le magasin où ajouter la copie
                        </small>
                    </div>

                    <!-- Note obligatoire -->
                    <div class="alert retro-info-box mb-4">
                        <strong>ℹ️ Information :</strong><br>
                        Les champs marqués d'un <span class="text-danger">*</span> sont obligatoires.
                    </div>

                    <!-- Boutons d'action -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="{{ route('stocks.index') }}" class="retro-btn-cancel">
                            ❌ Annuler
                        </a>
                        <button type="submit" class="retro-btn-create px-4 py-2" id="submitBtn">
                            ➕ Créer le stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Loader -->
<div id="loader" class="loader-overlay" style="display: none;">
    <div class="loader-content">
        <div class="retro-loader"></div>
        <p class="mt-3">Ajout du stock en cours...</p>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/stock.js') }}"></script>
@endpush

@endsection
