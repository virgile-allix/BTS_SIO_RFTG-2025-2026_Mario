@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/mario.css') }}">
@endsection

@section('content')

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

                    <form id="filmForm" action="{{ route('films.update', $film['filmId'] ?? $film['id']) }}" method="POST">
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

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">🎬 Réalisateurs</label>
                                <input type="text" id="directorSearch" class="form-control mb-2" placeholder="Rechercher un réalisateur...">
                                <div style="max-height: 200px; overflow-y: auto; border: 2px solid #2c3e50; padding: 10px; background: #ecf0f1;">
                                    @if(isset($directors) && count($directors) > 0)
                                        @php
                                            $currentDirectors = array_map(function($d) {
                                                return $d['directorId'] ?? $d['id'] ?? null;
                                            }, $film['directors'] ?? []);
                                        @endphp
                                        @foreach($directors as $director)
                                            <div class="director-item" data-name="{{ strtolower(($director['firstName'] ?? '') . ' ' . ($director['lastName'] ?? '')) }}">
                                                <input class="form-check-input me-2" type="checkbox"
                                                       value="{{ $director['directorId'] ?? $director['id'] }}"
                                                       name="directors[]"
                                                       id="dir{{ $director['directorId'] ?? $director['id'] }}"
                                                       {{ in_array($director['directorId'] ?? $director['id'], $currentDirectors) ? 'checked' : '' }}>
                                                <label class="form-check-label retro-label" for="dir{{ $director['directorId'] ?? $director['id'] }}">
                                                    {{ $director['firstName'] ?? $director['first_name'] ?? '' }} {{ $director['lastName'] ?? $director['last_name'] ?? '' }}
                                                </label>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">Aucun réalisateur disponible</p>
                                    @endif
                                </div>
                                @error('directors')
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
@endsection

@push('scripts')
<script src="{{ asset('js/film.js') }}"></script>
@endpush

