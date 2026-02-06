<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToadFilmService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.toad.url', 'http://localhost:8180'), '/');
    }

    public function getAllFilms(): ?array
    {
        $url = $this->baseUrl . '/films';

        try {
            $headers = ['Accept' => 'application/json'];
            
            // Récupère le token JWT depuis la session
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Films', ['url' => $url, 'has_token' => !empty($token)]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Films API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Films', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function getFilmById(int $id): ?array
    {
        $url = $this->baseUrl . '/films/' . $id;

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
                $film = $response->json();
                $filmId = $film['filmId'] ?? $film['id'] ?? null;

                // Enrichir avec le nom de la langue
                if (isset($film['languageId'])) {
                    $languages = $this->getAllLanguages();
                    foreach ($languages as $language) {
                        if ($language['languageId'] == $film['languageId']) {
                            $film['languageName'] = $language['name'];
                            break;
                        }
                    }
                }

                // Enrichir avec le nom de la langue originale
                if (isset($film['originalLanguageId'])) {
                    $languages = $this->getAllLanguages();
                    foreach ($languages as $language) {
                        if ($language['languageId'] == $film['originalLanguageId']) {
                            $film['originalLanguageName'] = $language['name'];
                            break;
                        }
                    }
                }

                // Note: L'enrichissement des acteurs/catégories/directors est désactivé car:
                // 1. L'API retourne déjà ces champs (même vides)
                // 2. Cela ralentit énormément l'application (3 appels API supplémentaires à chaque affichage)
                // 3. L'API ne gère pas les relations many-to-many donc l'enrichissement ne fonctionne pas

                // Si l'API était correctement configurée, on pourrait faire:
                // if ($filmId && empty($film['actors'])) {
                //     $film['actors'] = $this->getActorsByFilmId($filmId);
                // }

                return $film;
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Film', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function createFilm(array $data): bool
    {
        $url = $this->baseUrl . '/films';

        try {
            $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Création film', ['url' => $url, 'data' => $data]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($url, $data);

            if ($response->successful()) {
                Log::info('Film créé avec succès', ['status' => $response->status()]);
                return true;
            }

            Log::warning('Création film KO', ['status' => $response->status(), 'body' => $response->body()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Erreur création film', ['msg' => $e->getMessage()]);
            return false;
        }
    }

    public function updateFilm(int $id, array $data): bool
    {
        $url = $this->baseUrl . '/films/' . $id;

        try {
            $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Modification film', ['url' => $url, 'data' => $data]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->put($url, $data);

            if ($response->successful()) {
                Log::info('Film modifié avec succès', ['status' => $response->status()]);
                return true;
            }

            Log::warning('Modification film KO', ['status' => $response->status(), 'body' => $response->body()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Erreur modification film', ['msg' => $e->getMessage()]);
            return false;
        }
    }

    public function deleteFilm(int $id): array
    {
        $url = $this->baseUrl . '/films/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Suppression film', ['url' => $url]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->delete($url);

            if ($response->successful()) {
                Log::info('Film supprimé avec succès', ['status' => $response->status()]);
                return ['success' => true, 'message' => 'Film supprimé avec succès !'];
            }

            Log::warning('Suppression film KO', ['status' => $response->status(), 'body' => $response->body()]);

            $message = 'Erreur lors de la suppression du film.';
            if ($response->status() === 500 && str_contains($response->body(), 'foreign key constraint')) {
                $message = 'Impossible de supprimer ce film car il possède encore des stocks en inventaire. Supprimez d\'abord les stocks associés.';
            }

            return ['success' => false, 'message' => $message];
        } catch (\Throwable $e) {
            Log::error('Erreur suppression film', ['msg' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur lors de la suppression du film.'];
        }
    }

    public function getAllLanguages(): ?array
    {
        // Données statiques car l'endpoint /languages n'existe pas dans l'API
        // Basé sur la base de données Peach standard
        return [
            ['languageId' => 1, 'name' => 'English'],
            ['languageId' => 2, 'name' => 'Italian'],
            ['languageId' => 3, 'name' => 'Japanese'],
            ['languageId' => 4, 'name' => 'Mandarin'],
            ['languageId' => 5, 'name' => 'French'],
            ['languageId' => 6, 'name' => 'German'],
        ];
    }

    public function getAllCategories(): ?array
    {
        $url = $this->baseUrl . '/categories';

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Categories', ['url' => $url, 'has_token' => !empty($token)]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Categories API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Categories', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function getAllActors(): ?array
    {
        $url = $this->baseUrl . '/actors';

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Actors', ['url' => $url, 'has_token' => !empty($token)]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Actors API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Actors', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function getAllDirectors(): ?array
    {
        $url = $this->baseUrl . '/directors';

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            Log::info('Appel API Directors', ['url' => $url, 'has_token' => !empty($token)]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Directors API KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Directors', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Récupère les acteurs d'un film spécifique
     */
    private function getActorsByFilmId(int $filmId): array
    {
        $allActors = $this->getAllActors();

        if (!$allActors) {
            return [];
        }

        $filmActors = [];
        foreach ($allActors as $actor) {
            if (isset($actor['films']) && is_array($actor['films'])) {
                foreach ($actor['films'] as $film) {
                    if (($film['filmId'] ?? null) == $filmId) {
                        // Ajouter l'acteur sans ses films pour éviter trop de données
                        $filmActors[] = [
                            'actorId' => $actor['actorId'] ?? null,
                            'firstName' => $actor['firstName'] ?? '',
                            'lastName' => $actor['lastName'] ?? '',
                            'lastUpdate' => $actor['lastUpdate'] ?? null
                        ];
                        break; // Pas besoin de continuer à chercher dans les films de cet acteur
                    }
                }
            }
        }

        return $filmActors;
    }

    /**
     * Récupère les catégories d'un film spécifique
     */
    private function getCategoriesByFilmId(int $filmId): array
    {
        $allCategories = $this->getAllCategories();

        if (!$allCategories) {
            return [];
        }

        $filmCategories = [];
        foreach ($allCategories as $category) {
            if (isset($category['films']) && is_array($category['films'])) {
                foreach ($category['films'] as $film) {
                    if (($film['filmId'] ?? null) == $filmId) {
                        // Ajouter la catégorie sans ses films
                        $filmCategories[] = [
                            'categoryId' => $category['categoryId'] ?? null,
                            'name' => $category['name'] ?? '',
                            'lastUpdate' => $category['lastUpdate'] ?? null
                        ];
                        break;
                    }
                }
            }
        }

        return $filmCategories;
    }

    /**
     * Récupère les réalisateurs d'un film spécifique
     */
    private function getDirectorsByFilmId(int $filmId): array
    {
        $allDirectors = $this->getAllDirectors();

        if (!$allDirectors) {
            return [];
        }

        $filmDirectors = [];
        foreach ($allDirectors as $director) {
            if (isset($director['films']) && is_array($director['films'])) {
                foreach ($director['films'] as $film) {
                    if (($film['filmId'] ?? null) == $filmId) {
                        // Ajouter le réalisateur sans ses films
                        $filmDirectors[] = [
                            'directorId' => $director['directorId'] ?? null,
                            'firstName' => $director['firstName'] ?? '',
                            'lastName' => $director['lastName'] ?? '',
                            'lastUpdate' => $director['lastUpdate'] ?? null
                        ];
                        break;
                    }
                }
            }
        }

        return $filmDirectors;
    }

    /**
     * Récupère le token JWT depuis la session utilisateur
     */
    private function getUserToken(): ?string
    {
        $userData = session('toad_user');
        Log::info('Récupération token utilisateur', ['userData' => $userData]);

        return $userData['token'] ?? null;
    }
}