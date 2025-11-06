@extends('layouts.app')

@section('content')
<style>
    .retro-90s-form {
        background: #e8e8e8;
        border: 3px solid #2c3e50;
        box-shadow: 5px 5px 0px #2c3e50;
        font-family: 'Courier New', monospace;
    }

    .retro-header-form {
        background: repeating-linear-gradient(
            90deg,
            #5e72e4,
            #5e72e4 20px,
            #4c63d2 20px,
            #4c63d2 40px
        );
        border-bottom: 3px solid #2c3e50;
        color: white;
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        padding: 20px;
    }

    .retro-form-control {
        border: 2px solid #2c3e50 !important;
        box-shadow: 2px 2px 0px rgba(44, 62, 80, 0.2) !important;
        font-family: 'Courier New', monospace;
        font-weight: bold;
        background: white;
    }

    .retro-form-control:focus {
        background: #e3f2fd !important;
        border: 2px solid #5e72e4 !important;
        box-shadow: 3px 3px 0px rgba(94, 114, 228, 0.3) !important;
    }

    .retro-label {
        font-weight: bold;
        text-transform: uppercase;
        color: #2c3e50;
        font-family: 'Courier New', monospace;
        font-size: 12px;
    }

    .retro-btn-cancel {
        background: #95a5a6 !important;
        color: white !important;
        border: 2px solid #2c3e50 !important;
        box-shadow: 3px 3px 0px #2c3e50 !important;
        font-weight: bold;
        text-transform: uppercase;
    }

    .retro-btn-save {
        background: #2ecc71 !important;
        color: white !important;
        border: 2px solid #2c3e50 !important;
        box-shadow: 3px 3px 0px #2c3e50 !important;
        font-weight: bold;
        text-transform: uppercase;
    }

    .retro-btn-cancel:hover, .retro-btn-save:hover {
        transform: translate(-1px, -1px);
        box-shadow: 4px 4px 0px #2c3e50 !important;
    }

    .retro-checkbox {
        width: 18px;
        height: 18px;
        border: 2px solid #2c3e50;
    }

    .retro-search-filter {
        background: white;
        border: 2px solid #5e72e4;
        padding: 8px;
        margin-bottom: 10px;
        font-family: 'Courier New', monospace;
        font-weight: bold;
    }

    .retro-search-filter:focus {
        outline: none;
        border-color: #2ecc71;
        box-shadow: 0 0 5px rgba(46, 204, 113, 0.5);
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="retro-90s-form">
                <div class="retro-header-form">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">✏️ MODIFIER LE FILM</h4>
                        <a href="{{ route('films.show', $film['filmId'] ?? $film['id']) }}" class="retro-btn-cancel px-3 py-2">
                            ⬅️ Retour
                        </a>
                    </div>
                </div>

                <div class="p-4" style="background: white;">
                    @if(session('error'))
                        <div class="alert alert-danger" style="border: 3px solid #000; box-shadow: 4px 4px 0px #000; font-weight: bold;">
                            ❌ {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('films.update', $film['filmId'] ?? $film['id']) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="title" class="retro-label mb-2">Titre *</label>
                                <input type="text"
                                       class="form-control retro-form-control @error('title') is-invalid @enderror"
                                       id="title"
                                       name="title"
                                       value="{{ old('title', $film['title'] ?? '') }}"
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="releaseYear" class="retro-label mb-2">Année</label>
                                <input type="number"
                                       class="form-control retro-form-control @error('releaseYear') is-invalid @enderror"
                                       id="releaseYear"
                                       name="releaseYear"
                                       value="{{ old('releaseYear', $film['releaseYear'] ?? '') }}"
                                       min="1900"
                                       max="{{ date('Y') + 5 }}">
                                @error('releaseYear')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="retro-label mb-2">Description</label>
                            <textarea class="form-control retro-form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="4">{{ old('description', $film['description'] ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="languageId" class="retro-label mb-2">Langue *</label>
                                <select class="form-select retro-form-control @error('languageId') is-invalid @enderror"
                                        id="languageId"
                                        name="languageId"
                                        required>
                                    <option value="">-- Sélectionner une langue --</option>
                                    @foreach($languages as $language)
                                        <option value="{{ $language['languageId'] ?? $language['id'] }}"
                                            {{ old('languageId', $film['languageId'] ?? '') == ($language['languageId'] ?? $language['id']) ? 'selected' : '' }}>
                                            {{ $language['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('languageId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="originalLanguageId" class="retro-label mb-2">Langue originale</label>
                                <select class="form-select retro-form-control @error('originalLanguageId') is-invalid @enderror"
                                        id="originalLanguageId"
                                        name="originalLanguageId">
                                    <option value="">-- Aucune --</option>
                                    @foreach($languages as $language)
                                        <option value="{{ $language['languageId'] ?? $language['id'] }}"
                                            {{ old('originalLanguageId', $film['originalLanguageId'] ?? '') == ($language['languageId'] ?? $language['id']) ? 'selected' : '' }}>
                                            {{ $language['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('originalLanguageId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="length" class="retro-label mb-2">Durée (minutes)</label>
                                <input type="number"
                                       class="form-control retro-form-control @error('length') is-invalid @enderror"
                                       id="length"
                                       name="length"
                                       value="{{ old('length', $film['length'] ?? '') }}"
                                       min="1">
                                @error('length')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="rentalDuration" class="retro-label mb-2">Durée location (jours) *</label>
                                <input type="number"
                                       class="form-control retro-form-control @error('rentalDuration') is-invalid @enderror"
                                       id="rentalDuration"
                                       name="rentalDuration"
                                       value="{{ old('rentalDuration', $film['rentalDuration'] ?? 3) }}"
                                       min="1"
                                       required>
                                @error('rentalDuration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="rentalRate" class="retro-label mb-2">Tarif (€) *</label>
                                <input type="number"
                                       class="form-control retro-form-control @error('rentalRate') is-invalid @enderror"
                                       id="rentalRate"
                                       name="rentalRate"
                                       value="{{ old('rentalRate', $film['rentalRate'] ?? '4.99') }}"
                                       step="0.01"
                                       min="0"
                                       required>
                                @error('rentalRate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="rating" class="retro-label mb-2">Classification</label>
                                <select class="form-select retro-form-control @error('rating') is-invalid @enderror"
                                        id="rating"
                                        name="rating">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="G" {{ old('rating', $film['rating'] ?? '') == 'G' ? 'selected' : '' }}>G - Tous publics</option>
                                    <option value="PG" {{ old('rating', $film['rating'] ?? '') == 'PG' ? 'selected' : '' }}>PG - Accord parental</option>
                                    <option value="PG-13" {{ old('rating', $film['rating'] ?? '') == 'PG-13' ? 'selected' : '' }}>PG-13 - Déconseillé -13 ans</option>
                                    <option value="R" {{ old('rating', $film['rating'] ?? '') == 'R' ? 'selected' : '' }}>R - Interdit -17 ans</option>
                                    <option value="NC-17" {{ old('rating', $film['rating'] ?? '') == 'NC-17' ? 'selected' : '' }}>NC-17 - Interdit -18 ans</option>
                                </select>
                                @error('rating')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="replacementCost" class="retro-label mb-2">Coût remplacement (€)</label>
                                <input type="number"
                                       class="form-control retro-form-control @error('replacementCost') is-invalid @enderror"
                                       id="replacementCost"
                                       name="replacementCost"
                                       value="{{ old('replacementCost', $film['replacementCost'] ?? '19.99') }}"
                                       step="0.01"
                                       min="0">
                                @error('replacementCost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="retro-label mb-2">Caractéristiques spéciales</label>
                                @php
                                    $currentFeatures = old('specialFeatures', isset($film['specialFeatures']) ? explode(',', $film['specialFeatures']) : []);
                                @endphp
                                <div style="background: #f8f9fa; border: 2px solid #2c3e50; padding: 15px; box-shadow: 2px 2px 0px rgba(44, 62, 80, 0.2);">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input retro-checkbox" type="checkbox" name="specialFeatures[]" value="Trailers" id="feat1" {{ in_array('Trailers', $currentFeatures) ? 'checked' : '' }}>
                                        <label class="form-check-label retro-label" for="feat1">Trailers</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input retro-checkbox" type="checkbox" name="specialFeatures[]" value="Commentaries" id="feat2" {{ in_array('Commentaries', $currentFeatures) ? 'checked' : '' }}>
                                        <label class="form-check-label retro-label" for="feat2">Commentaries</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input retro-checkbox" type="checkbox" name="specialFeatures[]" value="Deleted Scenes" id="feat3" {{ in_array('Deleted Scenes', $currentFeatures) ? 'checked' : '' }}>
                                        <label class="form-check-label retro-label" for="feat3">Deleted Scenes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input retro-checkbox" type="checkbox" name="specialFeatures[]" value="Behind the Scenes" id="feat4" {{ in_array('Behind the Scenes', $currentFeatures) ? 'checked' : '' }}>
                                        <label class="form-check-label retro-label" for="feat4">Behind the Scenes</label>
                                    </div>
                                </div>
                                @error('specialFeatures')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="retro-label mb-2">Catégories</label>
                                <input type="text" class="retro-search-filter w-100" id="categorySearch" placeholder="🔍 Rechercher une catégorie...">
                                @php
                                    $currentCategories = old('categories', isset($film['categories']) ? array_column($film['categories'], 'categoryId') : []);
                                @endphp
                                <div id="categoryList" style="background: #f8f9fa; border: 2px solid #2c3e50; padding: 15px; box-shadow: 2px 2px 0px rgba(44, 62, 80, 0.2); max-height: 200px; overflow-y: auto;">
                                    @if(count($categories) > 0)
                                        @foreach($categories as $category)
                                            <div class="form-check mb-2 category-item" data-name="{{ strtolower($category['name']) }}">
                                                <input class="form-check-input retro-checkbox"
                                                       type="checkbox"
                                                       name="categories[]"
                                                       value="{{ $category['categoryId'] ?? $category['id'] }}"
                                                       id="cat{{ $category['categoryId'] ?? $category['id'] }}"
                                                       {{ in_array($category['categoryId'] ?? $category['id'], $currentCategories) ? 'checked' : '' }}>
                                                <label class="form-check-label retro-label" for="cat{{ $category['categoryId'] ?? $category['id'] }}">
                                                    {{ $category['name'] }}
                                                </label>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">Aucune catégorie disponible</p>
                                    @endif
                                </div>
                                @error('categories')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="retro-label mb-2">Acteurs</label>
                                <input type="text" class="retro-search-filter w-100" id="actorSearch" placeholder="🔍 Rechercher un acteur...">
                                @php
                                    $currentActors = old('actors', isset($film['actors']) ? array_column($film['actors'], 'actorId') : []);
                                @endphp
                                <div id="actorList" style="background: #f8f9fa; border: 2px solid #2c3e50; padding: 15px; box-shadow: 2px 2px 0px rgba(44, 62, 80, 0.2); max-height: 200px; overflow-y: auto;">
                                    @if(count($actors) > 0)
                                        @foreach($actors as $actor)
                                            <div class="form-check mb-2 actor-item" data-name="{{ strtolower(($actor['firstName'] ?? $actor['first_name'] ?? '') . ' ' . ($actor['lastName'] ?? $actor['last_name'] ?? '')) }}">
                                                <input class="form-check-input retro-checkbox"
                                                       type="checkbox"
                                                       name="actors[]"
                                                       value="{{ $actor['actorId'] ?? $actor['id'] }}"
                                                       id="act{{ $actor['actorId'] ?? $actor['id'] }}"
                                                       {{ in_array($actor['actorId'] ?? $actor['id'], $currentActors) ? 'checked' : '' }}>
                                                <label class="form-check-label retro-label" for="act{{ $actor['actorId'] ?? $actor['id'] }}">
                                                    {{ $actor['firstName'] ?? $actor['first_name'] ?? '' }} {{ $actor['lastName'] ?? $actor['last_name'] ?? '' }}
                                                </label>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">Aucun acteur disponible</p>
                                    @endif
                                </div>
                                @error('actors')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr style="border: 2px solid #2c3e50;">

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('films.show', $film['filmId'] ?? $film['id']) }}" class="retro-btn-cancel px-4 py-2">
                                ❌ Annuler
                            </a>
                            <button type="submit" class="retro-btn-save px-4 py-2">
                                💾 Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Filtre de recherche pour les catégories
    document.getElementById('categorySearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const items = document.querySelectorAll('.category-item');

        items.forEach(item => {
            const name = item.getAttribute('data-name');
            if (name.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Filtre de recherche pour les acteurs
    document.getElementById('actorSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const items = document.querySelectorAll('.actor-item');

        items.forEach(item => {
            const name = item.getAttribute('data-name');
            if (name.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
</script>
@endsection
