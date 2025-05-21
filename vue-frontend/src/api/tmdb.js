//

import axios from 'axios'

const TMDB_API_KEY = '17410523a8a9692121525379fe393593' // Substitua pela sua chave
const BASE_URL = 'https://api.themoviedb.org/3'

const tmdbApi = axios.create({
  baseURL: BASE_URL,
  params: {
    api_key: TMDB_API_KEY,
    language: 'pt-BR'
  }
})

export default {
  async getMovies(type = 'popular', page = 1) {
    try {
      const response = await tmdbApi.get(`/movie/${type}`, { params: { page } })
      return response.data
    } catch (error) {
      console.error('Error fetching movies:', error)
      throw error
    }
  }
}