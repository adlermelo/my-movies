import { defineStore } from 'pinia'
import movieService from '@/services/movieService'
import { useToast } from 'primevue/usetoast'

export const useMovieStore = defineStore('movies', {
  state: () => ({
    favorites: [],
    searchResults: [],
    currentMovie: null,
    isLoading: false
  }),
  actions: {
    async fetchFavorites() {
      this.isLoading = true
      try {
        this.favorites = await movieService.getFavorites()
      } catch (error) {
        useToast().add({
          severity: 'error',
          summary: 'Erro',
          detail: error.message,
          life: 3000
        })
      } finally {
        this.isLoading = false
      }
    },
    async searchMovies(query) {
      this.isLoading = true
      try {
        const { results } = await movieService.search(query)
        this.searchResults = results
      } catch (error) {
        useToast().add({
          severity: 'error',
          summary: 'Erro',
          detail: error.message,
          life: 3000
        })
      } finally {
        this.isLoading = false
      }
    },
    async toggleFavorite(movie) {
      try {
        const isFavorite = this.favorites.some(f => f.tmdb_id === movie.id)
        
        if (isFavorite) {
          await movieService.removeFavorite(movie.id)
          this.favorites = this.favorites.filter(f => f.tmdb_id !== movie.id)
        } else {
          const newFavorite = await movieService.addFavorite(movie)
          this.favorites.push(newFavorite)
        }
        
        useToast().add({
          severity: 'success',
          summary: 'Sucesso',
          detail: isFavorite ? 'Removido dos favoritos' : 'Adicionado aos favoritos',
          life: 3000
        })
      } catch (error) {
        useToast().add({
          severity: 'error',
          summary: 'Erro',
          detail: error.message,
          life: 3000
        })
      }
    },
    async fetchMovieDetails(movieId) {
      this.isLoading = true
      try {
        this.currentMovie = await movieService.getMovieDetails(movieId)
      } catch (error) {
        useToast().add({
          severity: 'error',
          summary: 'Erro',
          detail: error.message,
          life: 3000
        })
      } finally {
        this.isLoading = false
      }
    }
  },
  getters: {
    isFavorite: (state) => (movieId) => {
      return state.favorites.some(f => f.tmdb_id === movieId)
    }
  }
})