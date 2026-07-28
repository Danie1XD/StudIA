import { defineStore } from 'pinia'
import api from '../api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    // Busca si ya había una sesión abierta en el navegador
    token: localStorage.getItem('token') || null 
  }),
  
  actions: {
    async login(email, password) {
      try {
        // Hacemos la petición al backend de Laravel
        const response = await api.post('/login', { email, password })
        
        // Guardamos el token y el usuario en Pinia
        this.token = response.data.token
        this.user = response.data.user
        
        // Guardamos el token en el almacenamiento del navegador para que no se pierda al recargar
        localStorage.setItem('token', this.token)
        
        return true
      } catch (error) {
        console.error("Credenciales incorrectas o error de conexión")
        return false
      }
    },

    logout() {
      this.user = null
      this.token = null
      localStorage.removeItem('token')
    }
  }
})