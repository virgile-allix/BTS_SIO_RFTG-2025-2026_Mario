
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Gestion du catalogue de films</h5>
                    <a href="#" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Ajouter un film
                    </a>
                </div>

                <div class="card-body">
                    @if (empty($films))
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Aucun film disponible ou erreur lors de la récupération des données de l'API.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Titre</th>
                                        <th>Description</th>
                                        <th>Année</th>
                                        <th>Durée</th>
                                        <th>Note</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($films as $film)
                                        <tr>
                                            <td>{{ $film['filmId'] ?? $film['id'] ?? 'N/A' }}</td>
                                            <td><strong>{{ $film['title'] ?? 'Sans titre' }}</strong></td>
                                            <td>{{ Str::limit($film['description'] ?? 'Aucune description', 80) }}</td>
                                            <td>{{ $film['releaseYear'] ?? 'N/A' }}</td>
                                            <td>{{ $film['length'] ?? 'N/A' }} min</td>
                                            <td>
                                                @if(isset($film['rating']))
                                                    <span class="badge bg-info">{{ $film['rating'] }}</span>
                                                @else
                                                    <span class="badge bg-secondary">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('films.show', $film['filmId'] ?? $film['id']) }}" 
                                                       class="btn btn-sm btn-info" title="Voir">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-warning" title="Modifier">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" title="Supprimer"
                                                            onclick="return confirm('Êtes-vous sûr ?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <p class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Total : <strong>{{ count($films) }}</strong> film(s)
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection