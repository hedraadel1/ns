import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('auth_token') || null);
  const user = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'));
  const isLoading = ref(false);
  const error = ref(null);

  const isAuthenticated = computed(() => !!token.value);
  const userEmail = computed(() => user.value?.email || null);
  const userName = computed(() => user.value?.name || 'User');

  // Set axios default header with token
  const updateAxiosHeader = (newToken) => {
    if (newToken) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;
    } else {
      delete axios.defaults.headers.common['Authorization'];
    }
  };

  const login = async (email, password) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/v1/login', { email, password });
      const { access_token, user: userData } = response.data;
      
      token.value = access_token;
      user.value = userData;
      
      localStorage.setItem('auth_token', access_token);
      localStorage.setItem('auth_user', JSON.stringify(userData));
      
      updateAxiosHeader(access_token);
      return true;
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed';
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const register = async (name, email, password, passwordConfirmation) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/v1/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
      const { access_token, user: userData } = response.data;
      
      token.value = access_token;
      user.value = userData;
      
      localStorage.setItem('auth_token', access_token);
      localStorage.setItem('auth_user', JSON.stringify(userData));
      
      updateAxiosHeader(access_token);
      return true;
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed';
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const logout = async () => {
    try {
      await axios.post('/api/v1/logout');
    } catch (err) {
      console.error('Logout error:', err);
    } finally {
      token.value = null;
      user.value = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
      updateAxiosHeader(null);
    }
  };

  const refreshToken = async () => {
    try {
      const response = await axios.post('/api/v1/refresh-token');
      const { access_token } = response.data;
      
      token.value = access_token;
      localStorage.setItem('auth_token', access_token);
      updateAxiosHeader(access_token);
      return true;
    } catch (err) {
      logout();
      return false;
    }
  };

  const verifyToken = async () => {
    if (!token.value) return false;
    try {
      const response = await axios.post('/api/v1/verify-token');
      return response.data.valid;
    } catch (err) {
      logout();
      return false;
    }
  };

  const setToken = (newToken, userData = null) => {
    token.value = newToken;
    if (userData) {
      user.value = userData;
      localStorage.setItem('auth_user', JSON.stringify(userData));
    }
    if (newToken) {
      localStorage.setItem('auth_token', newToken);
      updateAxiosHeader(newToken);
    }
  };

  // Initialize token on app load
  const initializeAuth = () => {
    if (token.value) {
      updateAxiosHeader(token.value);
    }
  };

  // Call on store creation
  initializeAuth();

  return {
    token,
    user,
    isLoading,
    error,
    isAuthenticated,
    userEmail,
    userName,
    login,
    register,
    logout,
    refreshToken,
    verifyToken,
    setToken,
    initializeAuth,
    updateAxiosHeader,
  };
});
