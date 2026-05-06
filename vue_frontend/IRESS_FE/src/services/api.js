import axios from "axios";

let isLoggingOut = false;

const api = axios.create({
  baseURL: "http://localhost:9000/api",
});

// Request Interceptor: Attach token to headers
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response Interceptor: Handle 401 Unauthorized globally
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401 && !isLoggingOut) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
      // Prevent infinite redirect if already on login page
      if (!window.location.pathname.includes('/login') && !window.location.pathname.includes('/register')) {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

// Export flag setter for logout flow
export const setLoggingOut = (value) => { isLoggingOut = value; };

export default api;

