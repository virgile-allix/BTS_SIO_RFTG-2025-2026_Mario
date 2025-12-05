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
                        <h4 class="mb-0">✏️ MODIFIER LE STOCK</h4>
                        <a href="{{ route('stocks.index') }}" class="retro-btn-cancel px-3 py-2">
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

                    @if ($errors->any())
                        <div class="alert alert-danger" style="border: 3px solid #000; box-shadow: 4px 4px 0px #000; font-weight: bold;">
                            ❌ Erreurs de validation :
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('stocks.update', $inventory['inventoryId'] ?? $inventory['id'] ?? 0) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="inventory_id" class="retro-label mb-2">📦 Inventory ID (lecture seule)</label>
                                <input
                                    type="text"
                                    class="form-control retro-form-control"
                                    id="inventory_id"
                                    value="{{ $inventory['inventoryId'] ?? $inventory['id'] ?? '' }}"
                                    readonly
                                    disabled
                                    style="background: #f8f9fa; color: #6c757d;">
                                <p class="text-muted small mb-0">Identifiant unique de la copie</p>
                            </div>
                            <div class="col-md-6">
                                <label for="film_title" class="retro-label mb-2">🎬 Film (lecture seule)</label>
                                <input
                                    type="text"
                                    class="form-control retro-form-control"
                                    id="film_title"
                                    value="{{ ($inventory['film']['title'] ?? 'Inconnu') . ' (ID: ' . ($inventory['filmId'] ?? 'N/A') . ')' }}"
                                    readonly
                                    disabled
                                    style="background: #f8f9fa; color: #6c757d;">
                                <p class="text-muted small mb-0">Le film ne peut pas être modifié</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div style="background: #f8f9fa; border: 2px solid #2c3e50; padding: 15px; box-shadow: 2px 2px 0px rgba(44, 62, 80, 0.2);">
                                <strong>🏪 Magasin actuel :</strong><br>
                                Boutique #{{ $inventory['storeId'] ?? 'N/A' }}
                                @if(isset($inventory['store']['address']))
                                    - {{ $inventory['store']['address'] }}
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="store_id" class="retro-label mb-2">🏬 Transférer vers le magasin <span class="text-danger">*</span></label>
                            <select
                                class="form-select retro-form-control @error('store_id') is-invalid @enderror"
                                id="store_id"
                                name="store_id"
                                required>
                                <option value="">-- Sélectionner un magasin --</option>
                                @foreach($stores as $store)
                                    @php
                                        $storeId = $store['storeId'] ?? $store['id'] ?? '';
                                        $currentStoreId = $inventory['storeId'] ?? null;
                                    @endphp
                                    @if($storeId != $currentStoreId)
                                        <option
                                            value="{{ $storeId }}"
                                            {{ old('store_id') == $storeId ? 'selected' : '' }}>
                                            Boutique #{{ $storeId }}
                                            @if(isset($store['address']))
                                                - {{ $store['address'] }}
                                            @endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('store_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <p class="text-muted small mb-0">Sélectionnez le nouveau magasin de destination</p>
                        </div>

                        <div class="mb-3">
                            <div class="alert alert-warning" style="border: 2px solid #2c3e50; box-shadow: 2px 2px 0px rgba(44, 62, 80, 0.2); background: #fffbea;">
                                <strong>⚠️ Attention :</strong><br>
                                Le transfert n'est pas possible si le DVD est actuellement loué.<br>
                                Une vérification automatique sera effectuée.
                            </div>
                        </div>

                        <div class="mb-4">
                            <div style="background: #ecf0f1; border: 2px solid #2c3e50; padding: 15px; box-shadow: 2px 2px 0px rgba(44, 62, 80, 0.2);">
                                <strong>ℹ️ Information :</strong><br>
                                Seul le magasin peut être modifié. Le nouveau magasin doit être différent du magasin actuel.
                            </div>
                        </div>

                        <hr style="border: 2px solid #2c3e50;">

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('stocks.index') }}" class="retro-btn-cancel px-4 py-2">
                                ❌ Annuler
                            </a>
                            <button type="submit" class="retro-btn-save px-4 py-2">
                                💾 Mettre à jour
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function(e) {
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Mise à jour...';
        }
    });
});
</script>
@endpush
