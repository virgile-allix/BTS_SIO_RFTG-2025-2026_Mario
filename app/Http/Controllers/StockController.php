<?php

namespace App\Http\Controllers;

use App\Services\ToadInventoryService;
use App\Services\ToadFilmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StockController extends Controller
{
    private ToadInventoryService $inventoryService;
    private ToadFilmService $filmService;

    public function __construct(ToadInventoryService $inventoryService, ToadFilmService $filmService)
    {
        $this->middleware('auth');
        $this->inventoryService = $inventoryService;
        $this->filmService = $filmService;
    }

    public function list(Request $request)
    {
        $filmSearch = trim((string) $request->get('film', ''));
        $storeFilter = $request->get('store_id', '');

        $inventories = $this->inventoryService->getInventories() ?? [];
        $stores = $this->inventoryService->getStores() ?? [];

        // Construire les labels des magasins
        $storeLabels = $this->buildStoreLabels($stores, $inventories);

        // Grouper par film
        $summary = $this->buildStockSummary($inventories, array_keys($storeLabels));

        // Filtrer par film
        if ($filmSearch !== '') {
            $summary = array_filter($summary, function ($item) use ($filmSearch) {
                return stripos($item['title'], $filmSearch) !== false;
            });
        }

        // Filtrer par magasin
        if ($storeFilter !== '') {
            $summary = array_filter($summary, function ($item) use ($storeFilter) {
                return isset($item['stock'][$storeFilter]) && $item['stock'][$storeFilter] > 0;
            });
        }

        // Trier par titre de film
        usort($summary, function ($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });

        return view('stocks.indexStock', [
            'stocks' => array_values($summary),
            'stores' => $storeLabels,
            'rawInventories' => $inventories, // Pour JavaScript modal
        ]);
    }

    public function summary(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $inventories = $this->inventoryService->getInventories() ?? [];
        $stores = $this->inventoryService->getStores() ?? [];

        $storeLabels = $this->buildStoreLabels($stores, $inventories);
        $summary = $this->buildStockSummary($inventories, array_keys($storeLabels));

        if ($search !== '') {
            $summary = array_values(array_filter($summary, static function (array $item) use ($search) {
                return stripos($item['title'], $search) !== false;
            }));
        }

        $totalCopies = array_sum(array_map(static fn ($item) => $item['total'], $summary));
        $lowStockThreshold = 3;
        $lowStockCount = count(array_filter($summary, static fn ($item) => $item['total'] < $lowStockThreshold));

        usort($summary, static function (array $a, array $b) {
            if ($a['total'] === $b['total']) {
                return strcasecmp($a['title'], $b['title']);
            }
            return $b['total'] <=> $a['total'];
        });

        return view('stocks.summary', [
            'stocks' => $summary,
            'stores' => $storeLabels,
            'search' => $search,
            'stats' => [
                'films' => count($summary),
                'copies' => $totalCopies,
                'low_stock' => $lowStockCount,
                'threshold' => $lowStockThreshold,
            ],
        ]);
    }

    private function buildStockSummary(array $inventories, array $storeIds): array
    {
        $summary = [];

        foreach ($inventories as $row) {
            $film = $row['film'] ?? [];
            $filmId = $film['filmId'] ?? $row['filmId'] ?? null;
            $storeId = $row['storeId'] ?? null;

            if (!$filmId) {
                continue;
            }

            if (!isset($summary[$filmId])) {
                $summary[$filmId] = [
                    'filmId' => $filmId,
                    'title' => $film['title'] ?? 'Sans titre',
                    'releaseYear' => $film['releaseYear'] ?? null,
                    'rating' => $film['rating'] ?? null,
                    'description' => $film['description'] ?? null,
                    'stock' => array_fill_keys($storeIds, 0),
                    'total' => 0,
                    'lastUpdate' => $row['lastUpdate'] ?? null,
                ];
            }

            if ($storeId !== null) {
                if (!array_key_exists($storeId, $summary[$filmId]['stock'])) {
                    $summary[$filmId]['stock'][$storeId] = 0;
                }
                $summary[$filmId]['stock'][$storeId]++;
            }

            $summary[$filmId]['total']++;

            $currentUpdate = $summary[$filmId]['lastUpdate'];
            $newUpdate = $row['lastUpdate'] ?? null;
            if ($newUpdate && (!$currentUpdate || $newUpdate > $currentUpdate)) {
                $summary[$filmId]['lastUpdate'] = $newUpdate;
            }
        }

        return array_values($summary);
    }

    private function buildStoreLabels(array $stores, array $inventories): array
    {
        $labels = [];

        foreach ($stores as $store) {
            $storeId = $store['storeId'] ?? null;
            if (!$storeId) {
                continue;
            }

            // Afficher uniquement "Boutique #X" sans le nom du manager
            $labels[$storeId] = 'Boutique #' . $storeId;
        }

        if (empty($labels)) {
            foreach ($inventories as $row) {
                $storeId = $row['storeId'] ?? null;
                if ($storeId && !isset($labels[$storeId])) {
                    $labels[$storeId] = 'Boutique #' . $storeId;
                }
            }
        }

        ksort($labels);

        return $labels;
    }

    public function create()
    {
        $films = $this->filmService->getAllFilms() ?? [];
        $stores = $this->inventoryService->getStores() ?? [];

        return view('stocks.createStock', [
            'films' => $films,
            'stores' => $stores,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'film_id' => 'required|integer',
            'store_id' => 'required|integer',
        ], [
            'film_id.required' => 'Le champ Film est obligatoire',
            'film_id.integer' => 'Le Film doit être un nombre valide',
            'store_id.required' => 'Le champ Magasin est obligatoire',
            'store_id.integer' => 'Le Magasin doit être un nombre valide',
        ]);

        $data = [
            'filmId' => (int) $validated['film_id'],
            'storeId' => (int) $validated['store_id'],
        ];

        Log::info('Création de stock', ['data' => $data]);

        $success = $this->inventoryService->createInventory($data);

        if (!$success) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'ajout du stock',
                ], 500);
            }
            return back()->with('error', 'Erreur lors de l\'ajout du stock')->withInput();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Stock ajouté avec succès !',
                'redirect' => route('stocks.index')
            ], 200);
        }

        return redirect()->route('stocks.index')->with('success', 'Stock ajouté avec succès !');
    }

    public function edit(int $id)
    {
        $inventory = $this->inventoryService->getInventoryById($id);

        if (!$inventory) {
            return redirect()->route('stocks.index')->with('error', 'Stock introuvable');
        }

        $stores = $this->inventoryService->getStores() ?? [];

        return view('stocks.editStock', [
            'inventory' => $inventory,
            'stores' => $stores,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'store_id' => 'required|integer',
        ], [
            'store_id.required' => 'Le champ Magasin est obligatoire',
            'store_id.integer' => 'Le Magasin doit être un nombre valide',
        ]);

        $inventory = $this->inventoryService->getInventoryById($id);

        if (!$inventory) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock introuvable',
                ], 404);
            }
            return redirect()->route('stocks.index')->with('error', 'Stock introuvable');
        }

        $currentStoreId = $inventory['storeId'] ?? null;
        $newStoreId = (int) $validated['store_id'];

        if ($currentStoreId === $newStoreId) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ Le nouveau magasin doit être différent du magasin actuel',
                ], 400);
            }
            return back()->with('error', '⚠️ Le nouveau magasin doit être différent du magasin actuel')->withInput();
        }

        // Utiliser checkIfDVDIsAvailable au lieu de checkActiveRentals (plus rapide)
        $isAvailable = $this->inventoryService->checkIfDVDIsAvailable($id);
        if (!$isAvailable) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ Impossible de transférer : ce DVD est actuellement loué',
                ], 409);
            }
            return back()->with('error', '⚠️ Impossible de transférer : ce DVD est actuellement loué')->withInput();
        }

        $data = [
            'storeId' => $newStoreId,
            'filmId' => $inventory['filmId'] ?? ($inventory['film']['filmId'] ?? null),
        ];

        if (empty($data['filmId'])) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock invalide : film manquant',
                ], 422);
            }
            return back()->with('error', 'Stock invalide : film manquant')->withInput();
        }

        Log::info('Modification de stock', ['inventory_id' => $id, 'data' => $data]);

        $success = $this->inventoryService->updateInventory($id, $data);

        if (!$success) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la modification du stock',
                ], 500);
            }
            return back()->with('error', 'Erreur lors de la modification du stock')->withInput();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Stock transféré avec succès !',
                'redirect' => route('stocks.index')
            ], 200);
        }

        return redirect()->route('stocks.index')->with('success', 'Stock transféré avec succès !');
    }

    public function destroy(Request $request, int $id)
    {
        $inventory = $this->inventoryService->getInventoryById($id);

        if (!$inventory) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock introuvable',
                ], 404);
            }
            return redirect()->route('stocks.index')->with('error', 'Stock introuvable');
        }

        // Utiliser checkIfDVDIsAvailable au lieu de checkActiveRentals (plus rapide)
        $isAvailable = $this->inventoryService->checkIfDVDIsAvailable($id);
        if (!$isAvailable) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '🚫 Ce stock est actuellement loué',
                    'blocked' => true,
                ], 409);
            }
            return redirect()->route('stocks.index')->with('error', '🚫 Ce stock est actuellement loué');
        }

        $rentals = $this->inventoryService->getRentalsByInventoryId($id);
        $historicalCount = is_array($rentals['historical'] ?? null) ? count($rentals['historical']) : 0;
        if ($historicalCount > 0) {
            $message = 'âš ï¸ Impossible de supprimer : ce stock a un historique de locations';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'blocked' => true,
                ], 409);
            }
            return redirect()->route('stocks.index')->with('error', $message);
        }

        $film = $inventory['film'] ?? [];
        $filmTitle = $film['title'] ?? 'Inconnu';
        $storeId = $inventory['storeId'] ?? null;

        Log::info('Suppression de stock', [
            'inventory_id' => $id,
            'film_id' => $inventory['filmId'] ?? null,
            'film_title' => $filmTitle,
            'store_id' => $storeId,
            'user_id' => auth()->user() ? auth()->user()->id : null,
        ]);

        try {
            $this->inventoryService->deleteInventory($id);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock supprimé avec succès !',
                ], 200);
            }

            return redirect()->route('stocks.index')->with('success', 'Stock supprimé avec succès !');

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Déterminer le bon code HTTP selon le message d'erreur
            $httpCode = 500;
            if (str_contains($errorMessage, 'Accès refusé') || str_contains($errorMessage, 'droits')) {
                $httpCode = 403;
            } elseif (str_contains($errorMessage, 'introuvable')) {
                $httpCode = 404;
            } elseif (str_contains($errorMessage, 'Impossible de supprimer') || str_contains($errorMessage, 'locations')) {
                $httpCode = 409;
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], $httpCode);
            }

            return redirect()->route('stocks.index')->with('error', $errorMessage);
        }
    }

    public function getRentals(int $id)
    {
        $rentals = $this->inventoryService->getRentalsByInventoryId($id);

        return response()->json([
            'success' => true,
            'rentals' => $rentals,
            'active_count' => count(array_filter($rentals['active'] ?? [], fn($r) => $r)),
            'historical_count' => count($rentals['historical'] ?? []),
            'last_rental_date' => $rentals['last_rental_date'] ?? null,
        ]);
    }

    public function checkAvailability(int $id)
    {
        $isAvailable = $this->inventoryService->checkIfDVDIsAvailable($id);

        return response()->json([
            'success' => true,
            'available' => $isAvailable,
            'inventory_id' => $id,
        ]);
    }

    /**
     * Vérifier la disponibilité de plusieurs DVDs en une seule requête (BATCH)
     * POST /stocks/availability/batch
     * Body: { "inventory_ids": [1, 2, 3, 4] }
     */
    public function checkAvailabilityBatch(Request $request)
    {
        $validated = $request->validate([
            'inventory_ids' => 'required|array',
            'inventory_ids.*' => 'integer',
        ]);

        $inventoryIds = $validated['inventory_ids'];
        $results = $this->inventoryService->checkMultipleDVDsAvailability($inventoryIds);

        return response()->json([
            'success' => true,
            'results' => $results, // Format: { "1": true, "2": false, "3": true }
        ]);
    }
}
