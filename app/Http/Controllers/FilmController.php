<?php

namespace App\Http\Controllers;

use App\Services\ToadFilmService;
use Illuminate\Http\Request;

class FilmController extends Controller
{
    private ToadFilmService $filmService;

    public function __construct(ToadFilmService $filmService)
    {
        $this->middleware('auth');
        $this->filmService = $filmService;
    }

    public function index()
    {
        $films = $this->filmService->getAllFilms();

        return view('films.index', [
            'films' => $films ?? []
        ]);
    }

    public function show($id)
    {
        $film = $this->filmService->getFilmById($id);

        if (!$film) {
            abort(404, 'Film non trouvé');
        }

        return view('films.show', [
            'film' => $film
        ]);
    }

    public function create()
    {
        $languages = $this->filmService->getAllLanguages() ?? [];
        $categories = $this->filmService->getAllCategories() ?? [];
        $actors = $this->filmService->getAllActors() ?? [];
        $directors = $this->filmService->getAllDirectors() ?? [];

        return view('films.create', [
            'languages' => $languages,
            'categories' => $categories,
            'actors' => $actors,
            'directors' => $directors
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'releaseYear' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'languageId' => 'required|integer',
            'originalLanguageId' => 'nullable|integer',
            'rentalDuration' => 'required|integer|min:1',
            'rentalRate' => 'required|numeric|min:0',
            'length' => 'nullable|integer|min:1',
            'rating' => 'nullable|string|in:G,PG,PG-13,R,NC-17',
            'replacementCost' => 'nullable|numeric|min:0',
            'specialFeatures' => 'nullable|array',
            'specialFeatures.*' => 'string|in:Trailers,Commentaries,Deleted Scenes,Behind the Scenes',
            'categories' => 'nullable|array',
            'categories.*' => 'integer',
            'actors' => 'nullable|array',
            'actors.*' => 'integer',
            'directors' => 'nullable|array',
            'directors.*' => 'integer'
        ]);

        // Convertir le tableau de specialFeatures en chaîne séparée par des virgules
        if (isset($validated['specialFeatures']) && is_array($validated['specialFeatures'])) {
            $validated['specialFeatures'] = implode(',', $validated['specialFeatures']);
        }

        $categoryIds = $validated['categories'] ?? [];
        $categoryIds = array_filter($categoryIds, static fn ($value) => $value !== null && $value !== '');
        $validated['categoryIds'] = array_map('intval', $categoryIds);
        unset($validated['categories']);

        $actorIds = $validated['actors'] ?? [];
        $actorIds = array_filter($actorIds, static fn ($value) => $value !== null && $value !== '');
        $validated['actorIds'] = array_map('intval', $actorIds);
        unset($validated['actors']);

        $directorIds = $validated['directors'] ?? [];
        $directorIds = array_filter($directorIds, static fn ($value) => $value !== null && $value !== '');
        $validated['directorIds'] = array_map('intval', $directorIds);
        unset($validated['directors']);

        $result = $this->filmService->createFilm($validated);

        if ($result) {
            // Si c'est une requête AJAX, retourner du JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Film créé avec succès !',
                    'film_id' => $result['filmId'] ?? $result['id'] ?? null,
                    'redirect' => route('films.index')
                ], 200);
            }

            return redirect()->route('films.index')
                ->with('success', 'Film créé avec succès !');
        }

        // Si échec
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du film.'
            ], 500);
        }

        return back()->withInput()
            ->with('error', 'Erreur lors de la création du film.');
    }

    public function edit($id)
    {
        $film = $this->filmService->getFilmById($id);

        if (!$film) {
            abort(404, 'Film non trouvé');
        }

        $languages = $this->filmService->getAllLanguages() ?? [];
        $categories = $this->filmService->getAllCategories() ?? [];
        $actors = $this->filmService->getAllActors() ?? [];
        $directors = $this->filmService->getAllDirectors() ?? [];

        return view('films.edit', [
            'film' => $film,
            'languages' => $languages,
            'categories' => $categories,
            'actors' => $actors,
            'directors' => $directors
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'releaseYear' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'languageId' => 'required|integer',
            'originalLanguageId' => 'nullable|integer',
            'rentalDuration' => 'required|integer|min:1',
            'rentalRate' => 'required|numeric|min:0',
            'length' => 'nullable|integer|min:1',
            'rating' => 'nullable|string|in:G,PG,PG-13,R,NC-17',
            'replacementCost' => 'nullable|numeric|min:0',
            'specialFeatures' => 'nullable|array',
            'specialFeatures.*' => 'string|in:Trailers,Commentaries,Deleted Scenes,Behind the Scenes',
            'categories' => 'nullable|array',
            'categories.*' => 'integer',
            'actors' => 'nullable|array',
            'actors.*' => 'integer',
            'directors' => 'nullable|array',
            'directors.*' => 'integer'
        ]);

        // Convertir le tableau de specialFeatures en chaîne séparée par des virgules
        if (isset($validated['specialFeatures']) && is_array($validated['specialFeatures'])) {
            $validated['specialFeatures'] = implode(',', $validated['specialFeatures']);
        }

        $categoryIds = $validated['categories'] ?? [];
        $categoryIds = array_filter($categoryIds, static fn ($value) => $value !== null && $value !== '');
        $validated['categoryIds'] = array_map('intval', $categoryIds);
        unset($validated['categories']);

        $actorIds = $validated['actors'] ?? [];
        $actorIds = array_filter($actorIds, static fn ($value) => $value !== null && $value !== '');
        $validated['actorIds'] = array_map('intval', $actorIds);
        unset($validated['actors']);

        $directorIds = $validated['directors'] ?? [];
        $directorIds = array_filter($directorIds, static fn ($value) => $value !== null && $value !== '');
        $validated['directorIds'] = array_map('intval', $directorIds);
        unset($validated['directors']);

        $result = $this->filmService->updateFilm($id, $validated);

        if ($result) {
            // Si c'est une requête AJAX, retourner du JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Film modifié avec succès !',
                    'film_id' => $id,
                    'redirect' => route('films.show', $id)
                ], 200);
            }

            return redirect()->route('films.show', $id)
                ->with('success', 'Film modifié avec succès !');
        }

        // Si échec
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification du film.'
            ], 500);
        }

        return back()->withInput()
            ->with('error', 'Erreur lors de la modification du film.');
    }

    public function destroy(Request $request, $id)
    {
        $result = $this->filmService->deleteFilm($id);

        if ($result) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Film supprimé avec succès !',
                    'redirect' => route('films.index')
                ], 200);
            }

            return redirect()->route('films.index')
                ->with('success', 'Film supprimé avec succès !');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du film.'
            ], 500);
        }

        return back()->with('error', 'Erreur lors de la suppression du film.');
    }
}
