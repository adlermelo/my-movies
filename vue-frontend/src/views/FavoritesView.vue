<template>
  <div class="favorites-page">
    <h1 class="page-title">Meus Filmes Favoritos</h1>

    
    <Dropdown 
      v-model="selectedGenre" 
      :options="genreOptions" 
      optionLabel="name" 
      optionValue="id"
      placeholder="Filtrar por gênero"
      class="genre-filter"
      inputClass="fixed-dropdown-input"
      panelClass="fixed-dropdown-panel"
    />

    <div v-if="filteredFavorites.length > 0" class="favorites-grid">
      <div 
        v-for="movie in filteredFavorites" 
        :key="movie.id" 
        class="movie-card"
        @click="openModal(movie)"
      >
        <img 
          :src="getPosterUrl(movie.poster_path)" 
          :alt="movie.title" 
          class="movie-poster"
        />
        <div class="movie-info">
          <h3>{{ movie.title }}</h3>
          <p>{{ truncateText(movie.overview, 100) }}</p>
          <div class="movie-meta">
            <span><i class="pi pi-star"></i> {{ movie.vote_average?.toFixed(1) || 'N/A' }}</span>
            <span>{{ formatDate(movie.release_date) }}</span>
          </div>
          <Button 
            icon="pi pi-trash" 
            label="Remover" 
            class="p-button-danger remove-btn" 
            @click.stop="removeFavorite(movie.id)"
          />
        </div>
      </div>
    </div>

    <div v-else class="empty-message">
      <p v-if="selectedGenre === null && favorites.length === 0">Você não tem filmes favoritos ainda.</p>
      <p v-else>Nenhum filme favorito encontrado para o gênero selecionado.</p>
    </div>

    <Dialog 
      v-model:visible="displayModal" 
      :style="{width: '50vw'}" 
      :breakpoints="{'960px': '75vw', '640px': '90vw'}"
      header="Detalhes do Filme"
    >
      <div v-if="selectedMovie" class="modal-content">
        <div class="modal-poster">
          <img 
            :src="getPosterUrl(selectedMovie.poster_path)" 
            :alt="selectedMovie.title"
            class="modal-poster-img"
          />
        </div>
        <div class="modal-details">
          <h3>{{ selectedMovie.title }}</h3>
          <p><strong>Data de Lançamento:</strong> {{ formatDate(selectedMovie.release_date) }}</p>
          <p><strong>Avaliação:</strong> {{ selectedMovie.vote_average?.toFixed(1) || 'N/A' }}</p>
          <p><strong>Gêneros:</strong> {{ getGenresNames(selectedMovie.genres).join(', ') }}</p>
          <p><strong>Sinopse:</strong> {{ selectedMovie.overview || 'Sinopse não disponível' }}</p>
        </div>
      </div>
      <template #footer>
        <Button 
          icon="pi pi-trash" 
          label="Remover dos Favoritos" 
          class="p-button-danger" 
          @click="removeFavorite(selectedMovie.id); displayModal = false"
        />
        <Button 
          label="Fechar" 
          class="p-button-text" 
          @click="displayModal = false"
        />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import Dropdown from 'primevue/dropdown'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'

// Dados reativos
const favorites = ref([])
const selectedGenre = ref(null)
const displayModal = ref(false)
const selectedMovie = ref(null)

// Opções de gêneros
const genreOptions = ref([
  { id: null, name: 'Todos' },
  { id: 28, name: 'Ação' },
  { id: 12, name: 'Aventura' },
  { id: 16, name: 'Animação' },
  { id: 35, name: 'Comédia' },
  { id: 18, name: 'Drama' },
  { id: 27, name: 'Terror' },
  { id: 10751, name: 'Família' },
  { id: 14, name: 'Fantasia' }
])

// Filtra os filmes favoritos
const filteredFavorites = computed(() => {
  if (!Array.isArray(favorites.value)) return []
  if (selectedGenre.value === null) return favorites.value
  return favorites.value.filter(movie => 
    movie.genres && movie.genres.includes(selectedGenre.value)
  )
})

// Funções auxiliares
const getPosterUrl = (path) => 
  path ? `https://image.tmdb.org/t/p/w500${path}` : 'https://via.placeholder.com/500x750?text=Poster+Indispon%C3%ADvel'

const truncateText = (text, length) => 
  text?.length > length ? `${text.substring(0, length)}...` : text || 'Descrição não disponível'

const formatDate = (date) => 
  date ? new Date(date).toLocaleDateString('pt-BR') : 'Data desconhecida'

const getGenresNames = (genreIds) => {
  return genreIds?.map(id => 
    genreOptions.value.find(g => g.id === id)?.name || ''
  ).filter(Boolean) || []
}

// Abre o modal com detalhes do filme
const openModal = (movie) => {
  selectedMovie.value = movie
  displayModal.value = true
}

// Carrega os favoritos da API
const fetchFavorites = async () => {
  try {
    const response = await axios.get('http://localhost/api/movies/favorites')
    favorites.value = response.data
  } catch (error) {
    console.error('Erro ao buscar favoritos:', error)
  }
}

// Remove um favorito
const removeFavorite = async (movieId) => {
  try {
    await axios.delete(`http://localhost/api/movies/favorites/${movieId}`)
    favorites.value = favorites.value.filter(m => m.id !== movieId)
  } catch (error) {
    console.error('Erro ao remover favorito:', error)
  }
}

// Carrega os dados quando o componente é montado
onMounted(fetchFavorites)
</script>

<style scoped>
.favorites-page {
  padding: 2rem;
  margin-top: 60px;
  color: #fff;
}

.page-title {
  font-size: 2rem;
  margin-bottom: 1.5rem;
  text-align: center;
}


.genre-filter {
  margin: 0 auto 2rem;
  display: block;
  width: 250px;
}

.favorites-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
  padding: 1rem;
}

.movie-card {
  background: #1a1a1a;
  border-radius: 8px;
  overflow: hidden;
  transition: transform 0.3s;
  cursor: pointer;
  position: relative;
}

.movie-card:hover {
  transform: translateY(-5px);
}

.movie-poster {
  width: 100%;
  height: 375px;
  object-fit: cover;
}

.movie-info {
  padding: 1rem;
}

.movie-info h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.movie-info p {
  font-size: 0.9rem;
  color: #aaa;
  margin: 0 0 1rem 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.movie-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  margin-bottom: 1rem;
  color: #e50914;
}

.remove-btn {
  width: 100%;
}

.empty-message {
  text-align: center;
  padding: 2rem;
  color: #999;
  font-size: 1.2rem;
}


.modal-content {
  display: flex;
  gap: 2rem;
}

.modal-poster {
  flex: 0 0 40%;
}

.modal-poster-img {
  width: 100%;
  border-radius: 8px;
}

.modal-details {
  flex: 1;
}

.modal-details p {
  margin-bottom: 1rem;
}

@media (max-width: 768px) {
  .modal-content {
    flex-direction: column;
  }
  
  .modal-poster {
    margin-bottom: 1rem;
  }
}
</style>

<style>

.fixed-dropdown-input {
  font-family: inherit !important;
  width: 100% !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  padding-right: 2rem !important;
}


.fixed-dropdown-panel .p-dropdown-items .p-dropdown-item {
  text-align: center !important;
  justify-content: center !important;
  padding: 0.5rem 1rem !important;
}

.p-dropdown .p-dropdown-label {
  text-align: center !important;
}
.p-dropdown .p-dropdown-label {
  background: transparent !important;
  border: none !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}

.p-dropdown-panel .p-dropdown-items .p-dropdown-item {
  white-space: normal !important;
  word-wrap: break-word !important;
}
</style>