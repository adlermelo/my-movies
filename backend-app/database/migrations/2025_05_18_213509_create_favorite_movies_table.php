<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorite_movies', function (Blueprint $table) {
            $table->id();
            // Removido o user_id
            $table->unsignedBigInteger('tmdb_id');
            $table->string('title');
            $table->text('overview')->nullable();
            $table->date('release_date')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->json('genres')->nullable();
            $table->float('vote_average')->nullable();
            $table->timestamps();

            // Alterado para garantir tmdb_id único globalmente
            $table->unique('tmdb_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorite_movies');
    }
};
