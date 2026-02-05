<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToadInventoryService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.toad.url', 'http://localhost:8180'), '/');
    }

    public function getInventories(): ?array
    {
        $url = $this->baseUrl . '/inventories';

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Inventories', ['url' => $url, 'has_token' => !empty($token)]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Inventories API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Inventories', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function getInventoryById(int $id): ?array
    {
        $url = $this->baseUrl . '/inventories/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Inventory API KO', ['status' => $response->status(), 'id' => $id]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Inventory', ['msg' => $e->getMessage(), 'id' => $id]);
            return null;
        }
    }

    public function createInventory(array $data): bool
    {
        $url = $this->baseUrl . '/inventories';

        try {
            $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Création inventory', ['url' => $url, 'data' => $data]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($url, $data);

            if ($response->successful()) {
                Log::info('Inventory créé avec succès', ['status' => $response->status()]);
                return true;
            }

            Log::warning('Création inventory KO', ['status' => $response->status(), 'body' => $response->body()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Erreur création inventory', ['msg' => $e->getMessage()]);
            return false;
        }
    }

    public function updateInventory(int $id, array $data): bool
    {
        $url = $this->baseUrl . '/inventories/' . $id;

        try {
            $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Modification inventory', ['url' => $url, 'data' => $data]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->put($url, $data);

            if ($response->successful()) {
                Log::info('Inventory modifié avec succès', ['status' => $response->status()]);
                return true;
            }

            Log::warning('Modification inventory KO', ['status' => $response->status(), 'body' => $response->body()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Erreur modification inventory', ['msg' => $e->getMessage()]);
            return false;
        }
    }

    public function deleteInventory(int $id): bool
    {
        $inventoryUrl = $this->baseUrl . '/inventories/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            // Supprimer directement l'inventory - l'API doit gérer la cascade elle-même
            Log::info('Suppression inventory', ['url' => $inventoryUrl, 'inventory_id' => $id]);

            $response = Http::withHeaders($headers)
                ->timeout(30) // Timeout plus long pour laisser le temps à l'API de gérer la cascade
                ->delete($inventoryUrl);

            if ($response->successful()) {
                Log::info('Inventory supprimé avec succès', ['status' => $response->status(), 'inventory_id' => $id]);
                return true;
            }

            // Erreur API - logger et throw exception avec message détaillé
            $errorBody = $response->body();
            $status = $response->status();

            Log::warning('Suppression inventory KO', [
                'status' => $status,
                'body' => $errorBody,
                'inventory_id' => $id
            ]);

            // Gérer les différents codes d'erreur
            $errorMessage = 'Erreur lors de la suppression du stock';

            if ($status === 403) {
                $errorMessage = '🚫 Accès refusé : vous n\'avez pas les droits pour supprimer ce stock';
            } elseif ($status === 404) {
                $errorMessage = '❌ Stock introuvable (déjà supprimé ?)';
            } elseif ($status === 409 || $status === 400) {
                // L'API backend a probablement une erreur de contrainte ou conflit
                if (str_contains($errorBody, 'foreign key') || str_contains($errorBody, 'constraint') || str_contains($errorBody, 'rental')) {
                    $errorMessage = '⚠️ Impossible de supprimer : ce DVD a des locations actives ou un historique';
                } else {
                    $errorMessage = '⚠️ Impossible de supprimer : ' . ($errorBody ?: 'conflit détecté');
                }
            } elseif ($status === 500) {
                $errorMessage = '💥 Erreur serveur API lors de la suppression';
            }

            throw new \Exception($errorMessage);

        } catch (\Throwable $e) {
            Log::error('Erreur suppression inventory', ['msg' => $e->getMessage()]);
            throw $e; // Re-throw pour que le controller puisse gérer
        }
    }

    public function checkActiveRentals(int $inventoryId): int
    {
        $url = $this->baseUrl . '/rentals?inventory_id=' . $inventoryId . '&status=active';

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Vérification rentals actifs', ['url' => $url, 'inventory_id' => $inventoryId]);

            $response = Http::withHeaders($headers)
                ->timeout(5)
                ->get($url);

            if ($response->successful()) {
                $rentals = $response->json();

                // L'API ne filtre pas correctement par status=active
                // On doit filtrer manuellement les rentals avec statusId = 3 (en cours)
                if (is_array($rentals)) {
                    $activeRentals = array_filter($rentals, function ($rental) {
                        $statusId = $rental['status']['statusId'] ?? $rental['statusId'] ?? null;
                        $statusLabel = $rental['status']['statusLabel'] ?? '';

                        // statusId = 3 = "en cours" (location active)
                        return $statusId === 3 || strtolower($statusLabel) === 'en cours';
                    });

                    $activeCount = count($activeRentals);

                    Log::info('Rentals actifs filtrés', [
                        'inventory_id' => $inventoryId,
                        'total_rentals' => count($rentals),
                        'active_count' => $activeCount,
                        'active_rentals' => array_values($activeRentals)
                    ]);

                    return $activeCount;
                }

                return 0;
            }

            Log::warning('Rentals actifs API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return 0;
        } catch (\Throwable $e) {
            Log::error('Erreur vérification rentals actifs', ['msg' => $e->getMessage(), 'inventory_id' => $inventoryId]);
            return 0;
        }
    }

    public function getRentalsByInventoryId(int $inventoryId): array
    {
        $urlActive = $this->baseUrl . '/rentals?inventory_id=' . $inventoryId . '&status=active';
        $urlAll = $this->baseUrl . '/rentals?inventory_id=' . $inventoryId;

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $activeResponse = Http::withHeaders($headers)->timeout(10)->get($urlActive);
            $allResponse = Http::withHeaders($headers)->timeout(10)->get($urlAll);

            $activeRentals = $activeResponse->successful() ? $activeResponse->json() : [];
            $allRentals = $allResponse->successful() ? $allResponse->json() : [];

            $activeRentals = $this->filterRentalsByInventoryId($activeRentals, $inventoryId);
            $allRentals = $this->filterRentalsByInventoryId($allRentals, $inventoryId);

            $historicalRentals = array_filter($allRentals, function ($rental) use ($activeRentals) {
                $rentalId = $rental['rentalId'] ?? null;
                foreach ($activeRentals as $active) {
                    if (($active['rentalId'] ?? null) === $rentalId) {
                        return false;
                    }
                }
                return true;
            });

            $lastRentalDate = null;
            if (!empty($allRentals)) {
                usort($allRentals, function ($a, $b) {
                    return ($b['rentalDate'] ?? '') <=> ($a['rentalDate'] ?? '');
                });
                $lastRentalDate = $allRentals[0]['rentalDate'] ?? null;
            }

            return [
                'active' => is_array($activeRentals) ? $activeRentals : [],
                'historical' => is_array($historicalRentals) ? array_values($historicalRentals) : [],
                'last_rental_date' => $lastRentalDate,
            ];
        } catch (\Throwable $e) {
            Log::error('Erreur récupération rentals', ['msg' => $e->getMessage()]);
            return [
                'active' => [],
                'historical' => [],
                'last_rental_date' => null,
            ];
        }
    }

    public function getStores(): ?array
    {
        $url = $this->baseUrl . '/stores';

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Stores', ['url' => $url, 'has_token' => !empty($token)]);

            $response = Http::withHeaders($headers)
                ->timeout(5)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Stores API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Stores', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function checkIfDVDIsAvailable(int $inventoryId): bool
    {
        $url = $this->baseUrl . '/inventories/checkIfDVDIsAvailable/' . $inventoryId;

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)
                ->timeout(3)
                ->get($url);

            if ($response->successful()) {
                $result = $response->json();

                Log::info('DVD Availability Check', [
                    'inventory_id' => $inventoryId,
                    'raw_response' => $result,
                    'response_type' => gettype($result)
                ]);

                // Si l'API retourne un objet/tableau comme {"available": true}
                if (is_array($result) && isset($result['available'])) {
                    return (bool) $result['available'];
                }

                // Si l'API retourne directement true/false
                return (bool) $result;
            }

            // En cas d'erreur, considérer comme non disponible par sécurité
            return false;
        } catch (\Throwable $e) {
            Log::error('Erreur vérification disponibilité DVD', ['msg' => $e->getMessage(), 'inventory_id' => $inventoryId]);
            return false;
        }
    }

    /**
     * Vérifier la disponibilité de plusieurs DVDs en parallèle (BATCH)
     * Retourne un tableau [inventoryId => isAvailable]
     */
    public function checkMultipleDVDsAvailability(array $inventoryIds): array
    {
        $results = [];

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            // Utiliser Promise pour faire les appels en parallèle
            $promises = [];
            foreach ($inventoryIds as $inventoryId) {
                $url = $this->baseUrl . '/inventories/checkIfDVDIsAvailable/' . $inventoryId;
                $promises[$inventoryId] = Http::withHeaders($headers)->timeout(5)->async()->get($url);
            }

            // Attendre toutes les réponses
            foreach ($promises as $inventoryId => $promise) {
                try {
                    $response = $promise->wait();
                    if ($response->successful()) {
                        $result = $response->json();

                        // Si l'API retourne un objet/tableau comme {"available": true}
                        if (is_array($result) && isset($result['available'])) {
                            $results[$inventoryId] = (bool) $result['available'];
                        } else {
                            // Si l'API retourne directement true/false
                            $results[$inventoryId] = (bool) $result;
                        }
                    } else {
                        $results[$inventoryId] = false;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Erreur vérification DVD batch', ['inventory_id' => $inventoryId, 'msg' => $e->getMessage()]);
                    $results[$inventoryId] = false;
                }
            }

            return $results;
        } catch (\Throwable $e) {
            Log::error('Erreur vérification batch DVDs', ['msg' => $e->getMessage()]);
            // Retourner false pour tous les IDs en cas d'erreur
            foreach ($inventoryIds as $id) {
                $results[$id] = false;
            }
            return $results;
        }
    }

    private function getUserToken(): ?string
    {
        $userData = session('toad_user');
        return $userData['token'] ?? null;
    }

    private function filterRentalsByInventoryId($rentals, int $inventoryId): array
    {
        if (!is_array($rentals)) {
            return [];
        }

        return array_values(array_filter($rentals, function ($rental) use ($inventoryId) {
            if (!is_array($rental)) {
                return false;
            }
            $rentalInventoryId = $rental['inventoryId']
                ?? ($rental['inventory']['inventoryId'] ?? null);

            return $rentalInventoryId !== null && (int) $rentalInventoryId === $inventoryId;
        }));
    }
}
