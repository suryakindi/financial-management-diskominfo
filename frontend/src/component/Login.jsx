import React, { useState } from 'react';
import { TextField, Button, Box, Typography } from '@mui/material';
import { Link, useNavigate } from 'react-router-dom';
import axios from '../api/axios'; 

const Login = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
   
    const navigate = useNavigate();  
    
    const handleSubmit = async (e) => {
        e.preventDefault();
    
        try {
            // Mengirim request login ke server dengan axios
            const response = await axios.post('/auth/login-user', {
                email,
                password,
            });

            
            if (response.status === 200) {
                const Token = response.data.data.token;
                localStorage.setItem('token', Token);
            
                alert('Login berhasil!');
                navigate('/dashboard'); 
            }
        } catch (err) {
            alert(`Login gagal. ${err.response?.data?.detailError || 'Terjadi kesalahan saat login'}`);
        }
    };

    return (
        <Box
            sx={{
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'center', // Vertikal center
                alignItems: 'center',     // Horizontal center
                height: '100vh',          // Penuhi tinggi layar
                margin: 0,                // Pastikan tidak ada margin yang mengganggu
                padding: 2,
                backgroundColor: '#f5f5f5', // Latar belakang lebih cerah
            }}
        >
            <Box
                sx={{
                    backgroundColor: 'white',
                    padding: '40px',
                    borderRadius: '8px',
                    boxShadow: '0px 4px 15px rgba(0, 0, 0, 0.1)',
                    width: '100%',
                    maxWidth: '400px',    // Lebar maksimal
                    textAlign: 'center',
                }}
            >
                <Typography variant="h4" sx={{ mb: 2, fontWeight: 'bold', color: '#333' }}>Login</Typography>
                <Typography variant="body2" sx={{ color: '#777', mb: 3 }}>Silakan masuk untuk melanjutkan</Typography>

                <Box component="form" onSubmit={handleSubmit} sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                    <TextField
                        label="Email"
                        variant="outlined"
                        fullWidth
                        required
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        sx={{ borderRadius: '5px' }}
                    />
                    <TextField
                        label="Password"
                        type="password"
                        variant="outlined"
                        fullWidth
                        required
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        sx={{ borderRadius: '5px' }}
                    />
                    <Button
                        type="submit"
                        variant="contained"
                        color="primary"
                        sx={{
                            padding: '12px',
                            fontSize: '16px',
                            borderRadius: '5px',
                            mt: 3,
                            ':hover': {
                                backgroundColor: '#0069d9',
                            },
                        }}
                    >
                        Login
                    </Button>
                </Box>

                <Typography variant="body2" sx={{ mt: 2 }}>
                    Belum punya akun?{' '}
                    <Link
                        to="/register"
                        style={{ textDecoration: 'none', color: '#1976d2' }}
                    >
                        Daftar di sini
                    </Link>
                </Typography>
            </Box>
        </Box>
    );
};

export default Login;
