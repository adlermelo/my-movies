<template>
  <div class="app-container">
    <!-- Navbar -->
    <header class="navbar">
      <div class="logo">MyMovies</div>

      <ul class="nav-menu">
        <li><router-link to="/">Início</router-link></li>
        <li><router-link to="/favorites">Meus Favoritos</router-link></li>
      </ul>

      <div class="search-wrapper">
        <input
          type="text"
          v-model="searchQuery"
          @focus="searchActive = true"
          @blur="onSearchBlur"
          @keyup.enter="executeSearch"
          placeholder="Buscar filmes..."
          class="search-input"
          autocomplete="off"
        />
        <button class="search-btn" @click="executeSearch" aria-label="Buscar">
          🔍
        </button>

        <transition name="slide-fade">
          <div 
            v-if="searchActive && searchResults.length" 
            class="search-results"
            @mousedown.prevent
          >
            <h3 class="results-title">Resultados para "{{ searchQuery }}"</h3>
            <div class="search-results-grid">
              <div
                v-for="movie in searchResults"
                :key="movie.id"
                class="movie-card"
                @click="openMovieModal(movie)"
              >
                <img
                  :src="getPosterUrl(movie.poster_path)"
                  :alt="movie.title"
                  class="movie-poster"
                />
                <div class="movie-info">
                  <h4>{{ movie.title }}</h4>
                  <p>{{ movie.release_date }}</p>
                  <Button 
                    :icon="isFavorite(movie.id) ? 'pi pi-heart-fill' : 'pi pi-heart'"
                    class="favorite-btn"
                    :class="{ 'favorited': isFavorite(movie.id) }"
                    @click.stop="toggleFavorite(movie)"
                  />
                </div>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </header>

    <!-- Main (router-view) -->
    <main class="main-content">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="footer">
      <p>© 2025 MyMovies - Todos os direitos reservados</p>
    </footer>

    <!-- Modal -->
    <transition name="modal-fade">
      <div v-if="selectedMovie" class="modal-overlay" @click.self="closeMovieModal">
        <div class="modal-content">
          <img
            :src="getPosterUrl(selectedMovie.poster_path)"
            class="modal-poster"
            alt="Poster do filme"
          />
          <div class="modal-details">
            <h2>{{ selectedMovie.title }}</h2>
            <p><strong>Lançamento:</strong> {{ selectedMovie.release_date }}</p>
            <p><strong>Nota:</strong> {{ selectedMovie.vote_average }}</p>
            <p class="modal-overview">{{ selectedMovie.overview }}</p>
            <button
              class="fav-btn modal-fav-btn"
              :class="{ favorited: isFavorite(selectedMovie.id) }"
              @click="toggleFavorite(selectedMovie)"
            >
              {{ isFavorite(selectedMovie.id) ? '❤️ Favoritado' : '🤍 Favoritar' }}
            </button>
          </div>
          <button class="modal-close-btn" @click="closeMovieModal" aria-label="Fechar modal">✖</button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useToast } from 'primevue/usetoast'
import api from '@/api' 

const toast = useToast()

const searchQuery = ref('')
const searchActive = ref(false)
const searchResults = ref([])
const favorites = ref([])
const selectedMovie = ref(null)

let searchTimeout = null

const getPosterUrl = (path) =>
  path
    ? `https://image.tmdb.org/t/p/w500${path}`
    : 'https://via.placeholder.com/500x750?text=Poster+Indisponível'

const isFavorite = (id) =>
  favorites.value.some(f => f.tmdb_id === id)

const activateSearch = () => {
  searchActive.value = true
}

const onSearchBlur = () => {
  
  setTimeout(() => {
    if (!searchQuery.value) {
      searchActive.value = false
      searchResults.value = []
    }
  }, 200)
}

const openMovieModal = (movie) => {
  selectedMovie.value = movie
}

const closeMovieModal = () => {
  selectedMovie.value = null
}

const executeSearch = async () => {
  if (searchQuery.value.trim().length < 3) {
    searchResults.value = []
    return
  }
  try {
    const response = await api.searchMovies(searchQuery.value)
    searchResults.value = response.results || []
    searchActive.value = true
  } catch {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Falha ao buscar filmes',
      life: 3000,
    })
  }
}

const toggleFavorite = async (movie) => {
  try {
    if (isFavorite(movie.id)) {
      const fav = favorites.value.find(f => f.tmdb_id === movie.id)
      await api.removeFavorite(fav.id)
      toast.add({
        severity: 'success',
        summary: 'Removido',
        detail: `${movie.title} removido dos favoritos`,
        life: 3000,
      })
    } else {
      await api.addFavorite({
        tmdb_id: movie.id,
        title: movie.title,
        poster_path: movie.poster_path,
        genres: movie.genre_ids || [],
        overview: movie.overview,
        vote_average: movie.vote_average,
        release_date: movie.release_date,
      })
      toast.add({
        severity: 'success',
        summary: 'Adicionado',
        detail: `${movie.title} adicionado aos favoritos`,
        life: 3000,
      })
    }
    loadFavorites()
  } catch {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Operação falhou. Tente novamente.',
      life: 3000,
    })
  }
}

const loadFavorites = async () => {
  try {
    const response = await api.getFavorites()
    favorites.value = Array.isArray(response) ? response : []
  } catch {
    favorites.value = []
  }
}

loadFavorites()

watch(searchQuery, (newVal) => {
  clearTimeout(searchTimeout)
  if (newVal.length > 2) {
    searchTimeout = setTimeout(executeSearch, 500)
  } else {
    searchResults.value = []
  }
})
</script>

<style scoped>

* {
  box-sizing: border-box;
}

.app-container {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #121212;
  color: #fff;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}


.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 40px;
  background-color: #141414;
  position: relative;
  z-index: 1000;
}


.logo {
  font-weight: 700;
  font-size: 1.8rem;
  color: #e50914;
}


.nav-menu {
  list-style: none;
  display: flex;
  gap: 36px;
  margin: 0;
  padding: 0;
}

.nav-menu li a {
  color: #fff;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.3s ease;
}

.nav-menu li a:hover {
  color: #e50914;
}


.search-wrapper {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  width: 45%;
  max-width: 600px;
  display: flex;
  align-items: center;
  background-color: #222;
  border-radius: 30px;
  padding: 6px 12px;
  box-shadow: 0 0 10px rgba(229, 9, 20, 0.7);
}

.search-input {
  flex: 1;
  border: none;
  background: transparent;
  color: #fff;
  font-size: 1.1rem;
  padding: 8px 12px;
  outline: none;
  border-radius: 30px;
}

.search-input::placeholder {
  color: #aaa;
}

.search-btn {
  background: none;
  border: none;
  color: #e50914;
  font-size: 1.3rem;
  cursor: pointer;
  padding: 4px 8px;
  transition: color 0.3s ease;
  border-radius: 50%;
}

.search-btn:hover {
  color: #ff2a2a;
}


.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: opacity 0.3s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
}

.search-results {
  position: absolute;
  top: 48px;
  left: 0;
  right: 0;
  background: #1f1f1f;
  border-radius: 12px;
  max-height: 450px;
  overflow-y: auto;
  box-shadow: 0 0 12px rgba(229, 9, 20, 0.6);
  padding: 12px 16px;
  z-index: 1500;
}

.results-title {
  margin: 0 0 10px 8px;
  font-weight: 700;
  color: #e50914;
  font-size: 1.2rem;
}


.search-results-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 12px;
}


.movie-card {
  background: #2a2a2a;
  border-radius: 8px;
  cursor: pointer;
  transition: transform 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-bottom: 8px;
}

.movie-card:hover {
  transform: scale(1.05);
  box-shadow: 0 0 10px #e50914;
}

.movie-poster {
  width: 100%;
  border-radius: 6px;
  aspect-ratio: 2/3;
  object-fit: cover;
}

.movie-info {
  padding: 6px 8px 0;
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.movie-info h4 {
  font-size: 1rem;
  margin: 0;
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  padding-right: 8px;
}

.movie-info p {
  font-size: 0.85rem;
  color: #ccc;
  margin: 0;
}


.favorite-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #fff;
  transition: color 0.3s ease;
  font-size: 1.3rem;
}

.favorite-btn.favorited {
  color: #e50914;
}

.favorite-btn:hover {
  color: #ff2a2a;
}


.main-content {
  flex-grow: 1;
  padding: 24px 40px;
  background: #121212;
}


.footer {
  text-align: center;
  padding: 12px 20px;
  background: #141414;
  font-size: 0.85rem;
  color: #666;
}


.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(18, 18, 18, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
  padding: 16px;
}

.modal-content {
  background: #222;
  border-radius: 12px;
  max-width: 800px;
  width: 90%;
  display: flex;
  gap: 24px;
  position: relative;
  padding: 24px;
  color: #eee;
  box-shadow: 0 0 20px #e50914;
}

.modal-poster {
  width: 280px;
  border-radius: 12px;
  object-fit: cover;
  flex-shrink: 0;
  aspect-ratio: 2/3;
  box-shadow: 0 0 12px #e50914;
}

.modal-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.modal-details h2 {
  margin: 0;
  font-size: 2rem;
  color: #e50914;
}

.modal-details p {
  margin: 0;
  line-height: 1.3;
  font-size: 1rem;
}

.modal-overview {
  flex-grow: 1;
  margin-top: 8px;
  font-size: 1rem;
  color: #ddd;
  overflow-y: auto;
  max-height: 220px;
}

.modal-fav-btn {
  align-self: flex-start;
  background-color: #e50914;
  color: white;
  border-radius: 30px;
  border: none;
  padding: 10px 20px;
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.modal-fav-btn.favorited {
  background-color: #b0060f;
}

.modal-fav-btn:hover {
  background-color: #ff2a2a;
}


.modal-close-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  font-size: 1.8rem;
  background: transparent;
  border: none;
  color: #e50914;
  cursor: pointer;
  transition: color 0.3s ease;
}

.modal-close-btn:hover {
  color: #ff2a2a;
}


.search-results::-webkit-scrollbar {
  width: 8px;
}
.search-results::-webkit-scrollbar-track {
  background: #1f1f1f;
}
.search-results::-webkit-scrollbar-thumb {
  background-color: #e50914;
  border-radius: 4px;
}


@media (max-width: 768px) {
  .search-wrapper {
    width: 70%;
  }
  .modal-content {
    flex-direction: column;
    align-items: center;
  }
  .modal-poster {
    width: 100%;
    max-width: 320px;
  }
  .modal-details {
    max-width: 100%;
  }
  .nav-menu {
    gap: 20px;
  }
}
</style>