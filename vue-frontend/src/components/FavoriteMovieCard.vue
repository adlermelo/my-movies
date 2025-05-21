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
      @click="openModal"
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
          @click.stop="openModal"
        />
      </Card>

      <div class="movie-hover-info" v-show="isHovered">
        <div class="hover-content">
          <p class="hover-overview">{{ truncatedOverview }}</p>
          <div class="movie-meta">
            <span><i class="pi pi-star"></i> {{ movie.vote_average?.toFixed(1) || 'N/A' }}</span>
            <span>{{ releaseDate }}</span>
          </div>
        </div>
      </div>
    </div>

    
    <Dialog
      v-model:visible="showDetails"
      modal
      :style="{ width: '850px', borderRadius: '10px' }"
      :breakpoints="{ '960px': '90vw', '640px': '90vw', '480px': '95vw' }"
      :dismissableMask="true"
      :closeOnEscape="true"
    >
      <template #header>
        <h3>{{ movie.title }}</h3>
      </template>
      <div class="modal-content">
        <img :src="posterUrl" :alt="movie.title" class="modal-poster" />
        <div class="modal-info">
          <p class="modal-overview">{{ movie.overview || 'Descrição não disponível' }}</p>
          <div class="movie-meta">
            <span><i class="pi pi-star"></i> {{ movie.vote_average?.toFixed(1) || 'N/A' }}</span>
            <span>{{ releaseDate }}</span>
          </div>
        </div>
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'

const props = defineProps({
  movie: Object,
  isFavorite: Boolean
})

const emit = defineEmits(['toggle-favorite'])

const isHovered = ref(false)
const showDetails = ref(false)

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

const truncatedOverview = computed(() => {
  if (!props.movie.overview) return 'Descrição não disponível'
  return props.movie.overview.length > 150
    ? props.movie.overview.slice(0, 147) + '...'
    : props.movie.overview
})

const toggleFavorite = () => {
  emit('toggle-favorite', props.movie)
}

const openModal = () => {
  showDetails.value = true
}
</script>

<style scoped>
.movie-card-container {
  position: relative;
  width: 210px;
  margin: 0.5rem;
  height: 100%;
  user-select: none;
}

.favorite-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 2.5rem;
  height: 2.5rem;
  background: rgba(0, 0, 0, 0.7);
  border-radius: 50%;
  color: white;
  z-index: 20;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.favorite-btn.favorited {
  color: #e50914;
  background: rgba(0, 0, 0, 0.9);
}

.favorite-btn:hover {
  background: rgba(229, 9, 20, 0.9);
  transform: scale(1.1);
}

.movie-card-wrapper {
  position: relative;
  height: 100%;
  border-radius: 8px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
}

.movie-card-wrapper:hover {
  transform: scale(1.05);
  box-shadow: 0 0 20px rgba(229, 9, 20, 0.6);
}

.movie-card {
  background-color: #1a1a1a;
  color: white;
  border-radius: 8px;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.poster-container {
  width: 100%;
  aspect-ratio: 2 / 3;
  overflow: hidden;
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
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  border-radius: 8px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
  z-index: 10;
}

.movie-card-wrapper:hover .movie-hover-info {
  opacity: 1;
  pointer-events: auto;
}

.hover-content {
  margin-top: auto;
}

.hover-overview {
  font-size: 0.95rem;
  color: #ddd;
  margin-bottom: 1rem;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 3; 
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.movie-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: #e50914;
}



.modal-content {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  max-height: 75vh; 
  overflow-y: auto;
  align-items: flex-start;
  justify-content: center;
  color: #d6cccc;
  padding-right: 0.5rem; 
}

.modal-poster {
  width: 45%;
  height: auto;
  border-radius: 4px;
  object-fit: contain;
  box-shadow: 0 0 15px rgba(0,0,0,0.3);
  flex-shrink: 0;
}

.modal-info {
  width: 50%;
  max-height: 100%;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

.modal-overview {
  font-size: 1rem;
  line-height: 1.5;
  color: #e9e0e0; 
  margin-bottom: 1rem;
}
</style>
