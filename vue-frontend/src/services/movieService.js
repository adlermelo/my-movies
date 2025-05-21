import api from './api'

export default {
  async search(query, page = 1) {
    return await api.get('/movies/search', { params: { query, page } })
  },

  async getFavorites() {
    return await api.get('/movies/favorites')
  },

  async addFavorite(movie) {
    return await api.post('/movies/favorites', {
      tmdb_id: movie.id,
      title: movie.title,
      poster_path: movie.poster_path,
      genres: movie.genres || []
    })
  },

  async removeFavorite(movieId) {
    return await api.delete(`/movies/favorites/${movieId}`)
  },

  async getMovieDetails(movieId) {
    return await api.get(`/movies/${movieId}`)
  }
}