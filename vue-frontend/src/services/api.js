import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost/api',
  withCredentials: true,
  timeout: 5000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Interceptor para tratamento de erros
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response) {
      // Erros 4xx/5xx
      console.error('Erro na resposta:', error.response.status, error.response.data)
      return Promise.reject(new Error(error.response.data.message || 'Erro no servidor'))
    } else if (error.request) {
      // Sem resposta do servidor
      console.error('Sem resposta do servidor:', error.request)
      return Promise.reject(new Error('Servidor indisponível. Tente novamente mais tarde.'))
    } else {
      // Erros de configuração
      console.error('Erro na requisição:', error.message)
      return Promise.reject(error)
    }
  }
)

export default {
  async getMovies(type) {
    const response = await api.get(`/movies/${type}`)
    return response.data
  },
  
  async searchMovies(query) {
    const response = await api.get('/movies/search', { params: { query } })
    return response.data
  },
  
  async getFavorites() {
    const response = await api.get('/movies/favorites')
    return response.data
  },
  
  async addFavorite(movie) {
    const response = await api.post('/movies/favorites', movie)
    return response.data
  },
  
  async removeFavorite(id) {
    const response = await api.delete(`/movies/favorites/${id}`)
    return response.data
  }
}