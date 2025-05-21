<template>
  <div class="movie-card-container">
    
    <Button 
      :icon="isFavorite ? 'pi pi-heart-fill' : 'pi pi-heart'"
      class="favorite-btn"
      @click.stop="toggleFavorite"
      :class="{ 'favorited': isFavorite }"
    />
    
    <div 
      class="movie-card-wrapper" 
      @mouseenter="isHovered = true" 
      @mouseleave="isHovered = false"
    >
      <Card class="movie-card">
        <template #header>
          <div class="poster-container">
            <img 
              :src="posterUrl" 
              :alt="movie.title" 
              class="movie-poster"
            />
          </div>
        </template>
        <h3 class="movie-title">{{ movie.title }}</h3>
        <Button 
          label="Detalhes" 
          icon="pi pi-info-circle" 
          class="details-btn" 
        />
      </Card>

      <!-- Overlay de informações -->
      <div class="movie-hover-info" v-show="isHovered">
        <div class="hover-content">
          <p>{{ movie.overview || 'Descrição não disponível' }}</p>
          <div class="movie-meta">
            <span><i class="pi pi-star"></i> {{ movie.vote_average?.toFixed(1) || 'N/A' }}</span>
            <span>{{ releaseDate }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'

const props = defineProps({
  movie: Object,
  isFavorite: Boolean
})

const emit = defineEmits(['toggle-favorite'])

const isHovered = ref(false)

const posterUrl = computed(() => 
  props.movie.poster_path 
    ? `https://image.tmdb.org/t/p/w500${props.movie.poster_path}`
    : 'https://via.placeholder.com/500x750?text=Poster+Indispon%C3%ADvel'
)

const releaseDate = computed(() => 
  props.movie.release_date
    ? new Date(props.movie.release_date).toLocaleDateString('pt-BR')
    : 'Data desconhecida'
)

const toggleFavorite = () => {
  emit('toggle-favorite', props.movie)
}
</script>

<style scoped>
.movie-card-container {
  position: relative;
  height: 100%;
}


.favorite-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 2.5rem;
  height: 2.5rem;
  background: rgba(0, 0, 0, 0.7);
  border: none;
  border-radius: 50%;
  color: white;
  z-index: 20; 
  transition: all 0.3s ease;
  cursor: pointer;
}

.favorite-btn.favorited {
  color: #e50914;
  background: rgba(0, 0, 0, 0.9);
}

.favorite-btn:hover {
  background: rgba(229, 9, 20, 0.9) !important;
  transform: scale(1.1);
}

.movie-card-wrapper {
  position: relative;
  height: 100%;
}

.movie-card {
  background-color: #1a1a1a;
  color: white;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.movie-card:hover {
  transform: scale(1.05);
  box-shadow: 0 0 20px rgba(229, 9, 20, 0.5);
}

.poster-container {
  position: relative;
  width: 100%;
  aspect-ratio: 2/3;
}

.movie-poster {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.movie-title {
  padding: 0.5rem 1rem;
  margin: 0;
  font-size: 1rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.details-btn {
  margin: 0.5rem 1rem 1rem;
  color: #e50914 !important;
  justify-self: flex-end;
}

.movie-hover-info {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.85);
  padding: 1rem;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
  z-index: 10; 
  border-radius: 8px;
}

.movie-card-wrapper:hover .movie-hover-info {
  opacity: 1;
}

.hover-content {
  margin-top: auto;
}

.movie-hover-info p {
  font-size: 0.9rem;
  color: #ddd;
  margin-bottom: 1rem;
  line-height: 1.4;
}

.movie-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: #e50914;
}
</style>