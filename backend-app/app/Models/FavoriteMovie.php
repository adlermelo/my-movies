<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteMovie extends Model
{
    protected $fillable = [
        'tmdb_id',
        'title',
        'overview',
        'release_date',
        'poster_path',
        'backdrop_path',
        'genres',
        'vote_average',
    ];

    protected $casts = [
        'genres' => 'array',
        'release_date' => 'date',
    ];

    // Removido o relacionamento com User
}
