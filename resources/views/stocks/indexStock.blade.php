@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/mario.css') }}">
    <style>
        .retro-badge-rating {
            display: inline-block;
            padding: 3px 10px;
            border: 2px solid #2c3e50;
            font-size: 12px;
            background: #ffeb3b;
            font-weight: bold;
        }
        .retro-badge-id {
            display: inline-block;
            padding: 4px 8px;
            border: 2px solid #2c3e50;
            font-size: 12px;
            background: #e3f2fd;
            font-weight: bold;
        }
        .film-row {
            transition: background 0.2s ease;
        }
        .film-row:hover {
            background: #e3f2fd !important;
        }
        .expand-icon {
            transition: transform 0.3s ease;
            display: inline-block;
            color: #5e72e4;
            font-weight: bold;
        }
        .film-details td {
            border-top: none !important;
        }
        .film-details .table {
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Messages flash -->
    @if(session('success'))
        <div class="alert alert-success retro-alert alert-dismissible fade show" role="alert">
            <strong>✅ Succès :</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger retro-alert alert-dismissible fade show" role="alert">
            <strong>❌ Erreur :</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12">
            <div class="retro-90s mb-4">
                <div class="retro-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">📦 GESTION DES STOCKS</h3>
                        <div>
                            <a href="{{ route('stocks.create') }}" class="retro-btn-create me-2">
                                ➕ Ajouter un stock
                            </a>
                            <span class="retro-badge">{{ count($stocks) }} films</span>
                        </div>
                    </div>
                </div>

                <div class="p-4" style="background: #ffffff;">
                    <!-- Recherche et filtre en temps réel -->
                    <div class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input
                                    type="text"
                                    id="filmSearchInput"
                                    name="film"
                                    class="form-control retro-form-control"
                                    placeholder="🔍 Rechercher un film..."
                                    value="{{ request('film', '') }}">
                            </div>
                            <div class="col-md-3">
                                <select id="storeFilterSelect" name="store_id" class="form-control retro-form-control">
                                    <option value="">🏪 Tous les magasins</option>
                                    @foreach($stores as $storeId => $storeLabel)
                                        <option
                                            value="{{ $storeId }}"
                                            {{ request('store_id') == $storeId ? 'selected' : '' }}>
                                            {{ $storeLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button id="clearFiltersBtn" type="button" class="btn retro-btn-secondary w-100">
                                    ❌ Effacer filtres
                                </button>
                            </div>
                        </div>
                    </div>

                    @if(empty($stocks))
                        <div class="alert alert-warning retro-alert">
                            🤷 Aucun stock trouvé. Essayez de modifier vos filtres.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table retro-table" id="stocksTable">
                                <thead>
                                    <tr>
                                        <th>Film ID</th>
                                        <th>Titre</th>
                                        <th class="text-center">Année</th>
                                        <th class="text-center">Note</th>
                                        @foreach($stores as $storeId => $storeLabel)
                                            <th class="text-center" title="{{ $storeLabel }}">Magasin #{{ $storeId }}</th>
                                        @endforeach
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="stocksTableBody">
                                    @foreach($stocks as $stock)
                                        {{-- Ligne principale du film (cliquable) --}}
                                        <tr class="film-row" data-film-title="{{ strtolower($stock['title']) }}" data-film-id="{{ $stock['filmId'] }}" onclick="toggleFilmDetails({{ $stock['filmId'] }})" style="cursor: pointer; background: #f8f9fa;">
                                            <td>
                                                <span class="retro-badge-id">#{{ $stock['filmId'] }}</span>
                                            </td>
                                            <td>
                                                <span class="expand-icon" id="icon-{{ $stock['filmId'] }}" style="display: inline-block; width: 20px; transition: transform 0.3s;">▶</span>
                                                <strong>{{ $stock['title'] }}</strong>
                                                @if(!empty($stock['description']))
                                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($stock['description'], 80) }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ $stock['releaseYear'] ?? '—' }}
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($stock['rating']))
                                                    <span class="retro-badge-rating">{{ $stock['rating'] }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            @foreach($stores as $storeId => $storeLabel)
                                                <td class="text-center {{ ($stock['stock'][$storeId] ?? 0) === 0 ? 'text-danger' : '' }}">
                                                    {{ $stock['stock'][$storeId] ?? 0 }}
                                                </td>
                                            @endforeach
                                            <td class="text-center fw-bold">
                                                {{ $stock['total'] }}
                                            </td>
                                            <td class="text-center">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm retro-btn-secondary"
                                                    onclick="event.stopPropagation(); toggleFilmDetails({{ $stock['filmId'] }})"
                                                    title="Voir les DVDs">
                                                    👁️ Détails
                                                </button>
                                            </td>
                                        </tr>
                                        {{-- Ligne des détails (cachée par défaut) --}}
                                        <tr class="film-details" id="details-{{ $stock['filmId'] }}" style="display: none;">
                                            <td colspan="{{ 5 + count($stores) }}" style="padding: 0; background: #fff;">
                                                <div style="padding: 20px; border-left: 5px solid #5e72e4; background: #f0f4ff;">
                                                    <h6 style="margin-bottom: 15px; color: #2c3e50;">📀 DVDs en stock pour "{{ $stock['title'] }}"</h6>
                                                    <table class="table table-sm table-bordered" style="margin-bottom: 0;">
                                                        <thead style="background: #2c3e50; color: white;">
                                                            <tr>
                                                                <th style="width: 120px;">Inventory ID</th>
                                                                <th>Magasin</th>
                                                                <th style="width: 180px;">Dernière MAJ</th>
                                                                <th style="width: 120px; text-align: center;">Statut</th>
                                                                <th style="width: 120px; text-align: center;">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="details-body-{{ $stock['filmId'] }}" style="background: white;">
                                                            <tr>
                                                                <td colspan="5" class="text-center text-muted" style="padding: 20px;">
                                                                    <em>Chargement des DVDs...</em>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(isset($pagination) && $pagination['total'] > $pagination['perPage'])
                            <div class="d-flex justify-content-center mt-4">
                                <nav>
                                    <ul class="pagination retro-pagination">
                                        @if($pagination['currentPage'] > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="?page={{ $pagination['currentPage'] - 1 }}">Précédent</a>
                                            </li>
                                        @endif

                                        @for($i = 1; $i <= $pagination['totalPages']; $i++)
                                            <li class="page-item {{ $i == $pagination['currentPage'] ? 'active' : '' }}">
                                                <a class="page-link" href="?page={{ $i }}">{{ $i }}</a>
                                            </li>
                                        @endfor

                                        @if($pagination['currentPage'] < $pagination['totalPages'])
                                            <li class="page-item">
                                                <a class="page-link" href="?page={{ $pagination['currentPage'] + 1 }}">Suivant</a>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Passer les inventories bruts au JavaScript
    window.rawInventories = @json($rawInventories ?? []);
    window.allStores = @json($stores);
</script>
<script src="{{ asset('js/stock.js') }}"></script>
@endpush

@endsection
