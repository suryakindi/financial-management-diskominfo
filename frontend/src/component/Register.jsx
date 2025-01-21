import React, { useState } from 'react';
import { TextField, Button, Box, Typography } from '@mui/material';
import axios from '../api/axios'; 

const Register = () => {
    const [name, setName] = useState('');        
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');

   
    const handleRegister = async (e) => {
        e.preventDefault();

        try {
            // Kirim data name, email, dan password
            const response = await axios.post('/auth/register-user', {
                name,              
                email,
                password,
            });

         
            alert(`Registrasi berhasil: ${response.data.message}`);
        } catch (error) {
            // Error handling jika gagal
          
            alert(`Registrasi gagal. ${error.response.data.detailError}`);
        }
    };

    return (
        <Box
            sx={{
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'center',
                alignItems: 'center',
                minHeight: '100vh',
                p: 2,
                backgroundColor: '#f5f5f5', 
            }}
        >
            {/* Kotak Registrasi */}
            <Box
                sx={{
                    backgroundColor: 'white',
                    p: 4,
                    borderRadius: '8px',
                    boxShadow: '0px 4px 20px rgba(0, 0, 0, 0.1)',
                    width: '100%',
                    maxWidth: '400px',
                    textAlign: 'center',
                }}
            >
                <Typography variant="h4" sx={{ mb: 2, fontWeight: 'bold', color: '#333' }}>
                    Registrasi
                </Typography>
                <Typography variant="body2" sx={{ color: '#777', mb: 3 }}>
                    Daftar untuk membuat akun baru
                </Typography>

                {/* Formulir Registrasi */}
                <Box component="form" onSubmit={handleRegister} sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                    
                    {/* Input Nama */}
                    <TextField
                        label="Nama"
                        variant="outlined"
                        fullWidth
                        required
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                    />

                    {/* Input Email */}
                    <TextField
                        label="Email"
                        variant="outlined"
                        fullWidth
                        required
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                    />

                    {/* Input Password */}
                    <TextField
                        label="Password"
                        type="password"
                        variant="outlined"
                        fullWidth
                        required
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                    />

                    {/* Tombol Daftar */}
                    <Button
                        type="submit"
                        variant="contained"
                        color="primary"
                        sx={{
                            padding: '10px',
                            fontSize: '16px',
                            borderRadius: '5px',
                            mt: 3,
                            ':hover': {
                                backgroundColor: '#0056b3',
                            },
                        }}
                    >
                        Daftar
                    </Button>
                </Box>

                <Typography variant="body2" sx={{ mt: 2 }}>
                    Sudah punya akun?{' '}
                    <a href="/login" style={{ textDecoration: 'none', color: '#1976d2' }}>
                        Masuk di sini
                    </a>
                </Typography>
            </Box>
        </Box>
    );
};

export default Register;
