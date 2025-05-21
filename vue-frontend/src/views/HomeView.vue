<template>
  <div class="app-container">
    <!-- Hero Section -->
    <section class="hero-section">
      
      <video 
        autoplay 
        muted 
        loop 
        playsinline 
        class="hero-video"
        poster="https://via.placeholder.com/1920x1080?text=Loading+Video"
      >
        <source src="/videos/meu-video.mp4" type="video/mp4">
        <source src="https://assets.mixkit.co/videos/preview/mixkit-action-movie-trailer-2184-large.webm" type="video/webm">
        <img src="https://via.placeholder.com/1920x1080?text=Movie+Trailer" alt="Trailer de filme de ação">
      </video>
      
      <div class="hero-overlay"></div>
      
      <div class="hero-content">
        <h1>Os Melhores Filmes, Diversos Gêneros, A sua Lista de Favoritos!</h1>
        <p>Romance, suspense, aventura, ação e terror em um só lugar</p>
        <div class="hero-buttons">
          <Button 
            label="Explorar Catálogo" 
            icon="pi pi-chevron-down" 
            class="hero-btn scroll-btn" 
            @click="scrollToContent"
          />
        </div>
      </div>
    </section>
    
    <!-- Categorias de Filmes -->
    <section class="movie-section" v-for="(category, idx) in movieCategories" :key="idx">
      <h2 class="category-title">{{ category.name }}</h2>
      <Swiper
        :modules="modules"
        :slides-per-view="5"
        :space-between="15"
        :breakpoints="swiperBreakpoints"
        navigation
        class="movie-swiper"
      >
        <SwiperSlide v-for="movie in category.movies" :key="movie.id">
          <div class="movie-card-container">
            
            <Button 
              :icon="isFavorite(movie.id) ? 'pi pi-heart-fill' : 'pi pi-heart'"
              class="favorite-btn"
              :class="{ 'favorited': isFavorite(movie.id) }"
              @click.stop="toggleFavorite(movie)"
            />
            
            <div class="movie-card-wrapper">
              <Card class="movie-card">
                <template #header>
                  <div class="poster-container">
                    <img 
                      :src="getPosterUrl(movie.poster_path)" 
                      :alt="movie.title" 
                      class="movie-poster"
                    />
                  </div>
                </template>
                <div class="movie-info">
                  <h3>{{ movie.title }}</h3>
                  <Button 
                    label="Detalhes" 
                    icon="pi pi-info-circle" 
                    class="p-button-text details-btn" 
                  />
                </div>
              </Card>

              <!-- Overlay no hover -->
              <div class="movie-hover-info">
                <div class="hover-content">
                  <p class="movie-overview">{{ truncateText(movie.overview, 120) }}</p>
                  <div class="movie-meta">
                    <span><i class="pi pi-star"></i> {{ movie.vote_average?.toFixed(1) }}</span>
                    <span>{{ formatDate(movie.release_date) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </SwiperSlide>
      </Swiper>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Navigation } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import api from '@/api'
import tmdbApi from '@/api/tmdb'
import { useToast } from 'primevue/usetoast'

const toast = useToast()
const favorites = ref([])

// Categorias de filmes
const movieCategories = ref([
  { name: 'Recomendados para você', type: 'popular', movies: [] },
  { name: 'Novidades', type: 'now_playing', movies: [] },
  { name: 'Mais Curtidos', type: 'top_rated', movies: [] }
])

const modules = [Navigation]
const swiperBreakpoints = {
  320: { slidesPerView: 1.5 },
  640: { slidesPerView: 2.5 },
  768: { slidesPerView: 3.5 },
  1024: { slidesPerView: 5 }
}

// Funções auxiliares
const getPosterUrl = (path) => 
  path ? `https://image.tmdb.org/t/p/w500${path}` : 'https://via.placeholder.com/500x750?text=Poster+Indispon%C3%ADvel'

const truncateText = (text, length) => 
  text?.length > length ? `${text.substring(0, length)}...` : text || 'Descrição não disponível'

const formatDate = (date) => 
  date ? new Date(date).toLocaleDateString('pt-BR') : 'Data desconhecida'

const isFavorite = (id) => 
  Array.isArray(favorites.value) && favorites.value.some(f => f.tmdb_id === id)

const toggleFavorite = async (movie) => {
  try {
    if (isFavorite(movie.id)) {
      const favorite = favorites.value.find(f => f.tmdb_id === movie.id)
      await api.removeFavorite(favorite.id)
      toast.add({ 
        severity: 'success', 
        summary: 'Removido', 
        detail: `${movie.title} foi removido dos favoritos`, 
        life: 3000 
      })
    } else {
      await api.addFavorite({
        tmdb_id: movie.id,
        title: movie.title,
        poster_path: movie.poster_path,
        genres: movie.genre_ids || [],
        overview: movie.overview,
        vote_average: movie.vote_average,
        release_date: movie.release_date
      })
      toast.add({ 
        severity: 'success', 
        summary: 'Adicionado', 
        detail: `${movie.title} foi adicionado aos favoritos`, 
        life: 3000 
      })
    }
    loadFavorites()
  } catch (error) {
    toast.add({ 
      severity: 'error', 
      summary: 'Erro', 
      detail: 'Operação falhou. Tente novamente.', 
      life: 3000 
    })
  }
}

const loadFavorites = async () => {
  try {
    const response = await api.getFavorites()
    console.log('Favoritos recebidos:', response)

    const rawFavorites = Array.isArray(response) ? response : []

    favorites.value = rawFavorites.map(movie => ({
      ...movie,
      genre_ids: [] 
    }))
  } catch (error) {
    console.error('Erro ao carregar favoritos:', error)
  }
}

const loadMovies = async () => {
  try {
    for (const category of movieCategories.value) {
      const response = await tmdbApi.getMovies(category.type)
      category.movies = response.results.slice(0, 7)
    }
  } catch (error) {
    console.error('Error loading movies:', error)
  }
}

const scrollToContent = () => {
  const firstSection = document.querySelector('.movie-section');
  if (firstSection) {
    firstSection.scrollIntoView({ behavior: 'smooth' });
  }
};

onMounted(() => {
  loadFavorites()
  loadMovies()
})
</script>

<style scoped>

.hero-section {
  position: relative;
  height: 80vh;
  min-height: 600px;
  max-height: 800px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.hero-content {
  position: relative;
  z-index: 3;
  text-align: center;
  padding: 2rem;
  max-width: 800px;
}

.hero-content h1 {
  font-size: 3.5rem;
  margin-bottom: 1.5rem;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.8);
  line-height: 1.2;
  animation: fadeInUp 0.8s ease-out;
}

.hero-content p {
  font-size: 1.5rem;
  margin-bottom: 2.5rem;
  text-shadow: 0 2px 5px rgba(0, 0, 0, 0.8);
  animation: fadeInUp 0.8s ease-out 0.2s both;
}

.hero-video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: 1;
  
  transform: translateZ(0);
  backface-visibility: hidden;
  perspective: 1000;
  will-change: transform;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  z-index: 2;
}

.hero-buttons {
  display: flex;
  gap: 1.5rem;
  justify-content: center;
  animation: fadeInUp 0.8s ease-out 0.4s both;
}

.hero-btn {
  padding: 0.8rem 2rem;
  font-size: 1.1rem;
  font-weight: 600;
  border-radius: 30px;
  transition: all 0.3s ease;
}

.primary-btn {
  background-color: #e50914;
  border-color: #e50914;
}

.primary-btn:hover {
  background-color: #ff2a2a !important;
  border-color: #ff2a2a !important;
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(229, 9, 20, 0.4);
}

.secondary-btn {
  background-color: transparent;
  border: 2px solid #fff;
}

.secondary-btn:hover {
  background-color: rgba(255, 255, 255, 0.1) !important;
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(255, 255, 255, 0.1);
}

.hero-scroll-indicator {
  position: absolute;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  z-index: 10;
  animation: bounce 2s infinite;
  border-radius: 50%;
  background-color: rgba(229, 9, 20, 0.3);
  transition: all 0.3s ease;
}

.hero-scroll-indicator:hover {
  background-color: rgba(229, 9, 20, 0.6);
  transform: translateX(-50%) scale(1.1);
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0) translateX(-50%);
  }
  40% {
    transform: translateY(-20px) translateX(-50%);
  }
  60% {
    transform: translateY(-10px) translateX(-50%);
  }
}


.movie-section {
  padding: 2rem;
  margin-top: 2rem;
}

.category-title {
  margin-bottom: 1rem;
  font-size: 1.5rem;
  color: #e50914;
  padding-left: 1rem;
  border-left: 4px solid #e50914;
}

.movie-swiper {
  padding: 1rem 0;
}


.movie-card-container {
  position: relative;
  height: 100%;
  perspective: 1000px;
}


.movie-card-wrapper {
  position: relative;
  height: 100%;
  transition: all 0.3s ease;
  transform-style: preserve-3d;
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
  position: relative;
  z-index: 1;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.poster-container {
  position: relative;
  width: 100%;
  padding-top: 150%;
}

.movie-poster {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.movie-info {
  padding: 1rem;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.movie-info h3 {
  margin-bottom: 0.5rem;
  flex-grow: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.details-btn {
  color: #e50914 !important;
  align-self: flex-start;
}


.movie-hover-info {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(20, 20, 20, 0.85);
  padding: 1rem;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  opacity: 0;
  transition: all 0.3s ease;
  pointer-events: none;
  z-index: 2;
  border-radius: 8px;
  transform: translateZ(0);
  box-shadow: 0 0 20px rgba(229, 9, 20, 0.3);
}


.movie-card-container:hover .movie-card-wrapper {
  transform: scale(1.05) translateZ(10px);
}

.movie-card-container:hover .movie-poster {
  transform: scale(1.05);
}

.movie-card-container:hover .movie-hover-info {
  opacity: 1;
  transform: scale(1.05) translateZ(10px);
}


.hover-content {
  margin-top: auto;
  background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%);
  padding: 1.5rem 1rem 1rem;
  margin: -1rem;
  transform: translateZ(0);
}

.movie-overview {
  font-size: 0.85rem;
  color: #f0f0f0;
  margin-bottom: 0.8rem;
  line-height: 1.5;
  text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

.movie-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: #e50914;
  padding-top: 0.8rem;
  border-top: 1px solid rgba(229, 9, 20, 0.3);
}


.favorite-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 2.3rem;
  height: 2.3rem;
  background: rgba(30, 30, 30, 0.9);
  border: none;
  border-radius: 50%;
  color: white;
  z-index: 30;
  transition: all 0.3s ease;
  cursor: pointer;
  transform: translateZ(20px);
}

.favorite-btn.favorited {
  color: #e50914;
}

.favorite-btn:hover {
  background: rgba(229, 9, 20, 0.9) !important;
  transform: scale(1.1) translateZ(20px);
}


@media (max-width: 768px) {
  .hero-section {
    height: 70vh;
    min-height: 500px;
  }
  
  .hero-content h1 {
    font-size: 2.5rem;
  }
  
  .hero-content p {
    font-size: 1.2rem;
  }
  
  .hero-buttons {
    flex-direction: column;
    gap: 1rem;
  }
  
  .hero-btn {
    width: 100%;
    max-width: 250px;
    margin: 0 auto;
  }
  
  .movie-section {
    padding: 1rem;
  }
  
  .category-title {
    font-size: 1.3rem;
  }
}
</style>