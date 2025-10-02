// ApiService.js - Complete React Native API Service for Snake Game
// Place this file in your React Native project at: services/ApiService.js

import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'http://10.0.2.2:8000/api'; // Android emulator
// const API_BASE_URL = 'http://localhost:8000/api'; // iOS simulator
// const API_BASE_URL = 'https://your-production-domain.com/api'; // Production

class ApiService {
  constructor() {
    this.token = null;
  }

  async init() {
    try {
      const token = await AsyncStorage.getItem('jwt_token');
      if (token) {
        this.token = token;
      }
    } catch (error) {
      console.error('Error loading token:', error);
    }
  }

  async setToken(token) {
    this.token = token;
    try {
      if (token) {
        await AsyncStorage.setItem('jwt_token', token);
      } else {
        await AsyncStorage.removeItem('jwt_token');
      }
    } catch (error) {
      console.error('Error saving token:', error);
    }
  }

  async clearToken() {
    this.token = null;
    try {
      await AsyncStorage.removeItem('jwt_token');
    } catch (error) {
      console.error('Error clearing token:', error);
    }
  }

  async request(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`;
    const config = {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(this.token && { Authorization: `Bearer ${this.token}` }),
      },
      ...options,
    };

    try {
      const response = await fetch(url, config);
      const data = await response.json();

      if (!response.ok) {
        if (response.status === 401) {
          // Token expired or invalid, clear it
          await this.clearToken();
        }
        throw new Error(data.message || `HTTP error! status: ${response.status}`);
      }

      return data;
    } catch (error) {
      console.error('API request failed:', error);
      throw error;
    }
  }

  // =============================================================================
  // AUTHENTICATION METHODS
  // =============================================================================

  async register(userData) {
    const response = await this.request('/auth/register', {
      method: 'POST',
      body: JSON.stringify(userData),
    });
    
    if (response.success && response.token) {
      await this.setToken(response.token);
    }
    
    return response;
  }

  async login(email, password) {
    const response = await this.request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });
    
    if (response.success && response.token) {
      await this.setToken(response.token);
    }
    
    return response;
  }

  async logout() {
    try {
      await this.request('/auth/logout', { method: 'POST' });
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      await this.clearToken();
    }
  }

  async refreshToken() {
    const response = await this.request('/auth/refresh', { method: 'POST' });
    
    if (response.success && response.token) {
      await this.setToken(response.token);
    }
    
    return response;
  }

  async getProfile() {
    return this.request('/auth/profile');
  }

  // =============================================================================
  // USER METHODS
  // =============================================================================

  async updateProfile(userData) {
    return this.request('/user', {
      method: 'PUT',
      body: JSON.stringify(userData),
    });
  }

  async getUserStats() {
    return this.request('/user/stats');
  }

  async uploadAvatar(imageUri) {
    const formData = new FormData();
    formData.append('avatar', {
      uri: imageUri,
      type: 'image/jpeg',
      name: 'avatar.jpg',
    });

    return this.request('/user/avatar', {
      method: 'POST',
      headers: {
        'Content-Type': 'multipart/form-data',
        ...(this.token && { Authorization: `Bearer ${this.token}` }),
      },
      body: formData,
    });
  }

  // =============================================================================
  // SCORE METHODS
  // =============================================================================

  async submitScore(scoreData) {
    return this.request('/scores', {
      method: 'POST',
      body: JSON.stringify(scoreData),
    });
  }

  async getUserScores(page = 1) {
    return this.request(`/scores?page=${page}`);
  }

  async getBestScores() {
    return this.request('/scores/best');
  }

  async deleteScore(scoreId) {
    return this.request(`/scores/${scoreId}`, {
      method: 'DELETE',
    });
  }

  // =============================================================================
  // LEADERBOARD METHODS (PUBLIC)
  // =============================================================================

  async getGlobalLeaderboard() {
    return this.request('/leaderboard/global');
  }

  async getDailyLeaderboard() {
    return this.request('/leaderboard/daily');
  }

  async getWeeklyLeaderboard() {
    return this.request('/leaderboard/weekly');
  }

  async getMonthlyLeaderboard() {
    return this.request('/leaderboard/monthly');
  }

  async getLeaderboardByDifficulty(difficulty) {
    return this.request(`/leaderboard/difficulty/${difficulty}`);
  }

  async getHighScores(difficulty = null, limit = 10) {
    const params = new URLSearchParams();
    if (difficulty) params.append('difficulty', difficulty);
    if (limit) params.append('limit', limit.toString());
    
    return this.request(`/high-scores?${params.toString()}`);
  }

  // =============================================================================
  // UTILITY METHODS
  // =============================================================================

  async checkHealth() {
    return this.request('/health');
  }

  isAuthenticated() {
    return !!this.token;
  }
}

export default new ApiService();
