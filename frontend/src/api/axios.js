import axios from 'axios';

// Base URL API yang akan digunakan di seluruh aplikasi
const baseURL = 'http://127.0.0.1:8000/api';

const axiosInstance = axios.create({
  baseURL: baseURL,
  headers: {
    'Content-Type': 'application/json',
  },
});

export default axiosInstance;
