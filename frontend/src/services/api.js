import axios from 'axios';

// Fallback logic for Railway production: if VITE_API_URL is missing, we check if we're on a railway domain
const getBaseUrl = () => {
  // 1. High-priority Build-time Variable
  if (import.meta.env.VITE_API_URL) {
    console.debug('API: Using built-in VITE_API_URL:', import.meta.env.VITE_API_URL);
    return import.meta.env.VITE_API_URL;
  }
  
  // 2. Performance-time Discovery fallback
  const isProd = typeof window !== 'undefined' && window.location.hostname.includes('railway.app');
  
  if (isProd) {
    console.warn('CRITICAL: VITE_API_URL is missing in PRODUCTION!');
    console.info('Ensure "Generate Domain" is active for laravel-backend in Railway.');
    // We do NOT fall back to localhost in production to avoid confusing errors.
    // Instead, we return an empty string which will cause axios to use current domain, 
    // which might work if there's a proxy, or fail with a clearer 404.
    return '/api/v1'; 
  }

  // 3. Local Development Fallback
  console.debug('API: Using local development fallback: http://localhost:8080/api/v1');
  return 'http://localhost:8080/api/v1';
};

const API_URL = getBaseUrl();
const AI_PROXY_URL = import.meta.env.VITE_AI_PROXY_URL || 'http://localhost:7777/api/v1';

/**
 * Service to handle Vision AI logic.
 */
export const visionService = {
  /**
   * Send an image to the AI service for extraction.
   * @param {Blob} imageBlob 
   * @param {string} command 
   */
  async extractProducts(imageBlob, command = "") {
    const formData = new FormData();
    formData.append('image', imageBlob, 'capture.jpg');
    formData.append('command', command);

    // Note: In production, this would go through the Laravel backend first
    // to handle auth and logging. For MVP Mohammed workflow, we'll try direct or via Laravel.
    // The plan says Laravel handles the process-image endpoint.
    
    try {
      const response = await axios.post(`${API_URL}/products/process-image`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });
      return response.data;
    } catch (error) {
      console.error('Vision extraction failed:', error);
      throw error;
    }
  }
};

/**
 * Service to handle Backend API calls.
 */
export const apiService = {
  async bulkUpdateStock(updates) {
    const response = await axios.post(`${API_URL}/products/bulk-stock`, { updates });
    return response.data;
  },

  async createInvoiceFromAi(payload) {
    // payload should match the AI extraction output
    const response = await axios.post(`${API_URL}/invoices`, payload);
    return response.data;
  }
};
