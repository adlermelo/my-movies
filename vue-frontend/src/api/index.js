import axios from 'axios'


const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost/api',
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})


api.interceptors.response.use(
  response => response,
  error => {
    if (error.code === 'ERR_NETWORK') {
      console.error('Erro de conexão com a API:', error)
      throw new Error('Servidor indisponível. Verifique sua conexão.')
    }
    return Promise.reject(error)
  }
)

export default {
  async searchMovies(query, page = 1) {
    try {
      const response = await api.get('/movies/search', { 
        params: { query, page } 
      })
      console.log('searchMovies response:', response.data)  
      return response.data
    } catch (error) {
      console.error('Search error:', error)
      throw error
    }
  },
  
  async getFavorites() {
    try {
      const response = await api.get('/movies/favorites')
      console.log('getFavorites response:', response.data) 
      return response.data
    } catch (error) {
      console.error('Error loading favorites:', error)
      throw error
    }
  },
  
  async addFavorite(movie) {
    console.log('addFavorite chamado com movie:', movie) 
    
    try {
      
      const payload = {
        tmdb_id: movie.tmdb_id,  
        title: movie.title,
        poster_path: movie.poster_path,
        overview: movie.overview,
        vote_average: movie.vote_average,
        release_date: movie.release_date,
         
        genres: Array.isArray(movie.genres) ? movie.genres.map(Number) : Array.from(movie.genres).map(Number),
      }

      console.log('Payload para adicionar favorito:', payload) 

      const response = await api.post('/movies/favorites', payload)
      console.log('addFavorite response:', response.data) 
      return response.data
    } catch (error) {
      console.error('Error adding favorite:', error)
      throw error
    }
  },
  
  async removeFavorite(id) {
    console.log('removeFavorite chamado com id:', id) 
    try {
      const response = await api.delete(`/movies/favorites/${id}`)
      console.log('removeFavorite response:', response.data) 
      return response.data
    } catch (error) {
      console.error('Error removing favorite:', error)
      throw error
    }
  }
}
