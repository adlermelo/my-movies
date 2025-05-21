<template>
  <div class="movie-card" @click="openDetails">
    <img 
      :src="posterUrl" 
      :alt="movie.title"
      class="movie-card__poster"
    />
    <div class="movie-card__overlay">
      <Button 
        icon="pi pi-heart" 
        :class="{ 'p-button-success': isFavorite }"
        @click.stop="toggleFavorite"
      />
      <h3>{{ movie.title }}</h3>
      <div class="movie-card__meta">
        <span>{{ movie.release_date?.substring(0, 4) }}</span>
        <span><i class="pi pi-star"></i> {{ movie.vote_average?.toFixed(1) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useMovieStore } from '@/stores/movieStore'
import Button from 'primevue/button'

const props = defineProps({
  movie: {
    type: Object,
    required: true
  }
})

const router = useRouter()
const movieStore = useMovieStore()

const posterUrl = computed(() => 
  props.movie.poster_path 
    ? `https://image.tmdb.org/t/p/w500${props.movie.poster_path}`
    : '/placeholder-movie.png'
)

const isFavorite = computed(() => 
  movieStore.isFavorite(props.movie.id)
)

const toggleFavorite = () => {
  movieStore.toggleFavorite(props.movie)
}

const openDetails = () => {
  router.push({ name: 'movie-detail', params: { id: props.movie.id } })
}
</script>

<style scoped>
.movie-card {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.3s;
}

.movie-card:hover {
  transform: scale(1.05);
}

.movie-card__poster {
  width: 100%;
  aspect-ratio: 2/3;
  object-fit: cover;
}

.movie-card__overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
  padding: 1rem;
  color: white;
}

.movie-card__overlay h3 {
  margin: 0.5rem 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.movie-card__meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
}

.p-button {
  position: absolute;
  top: 10px;
  right: 10px;
}
</style>