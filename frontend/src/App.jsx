import React from 'react';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import Login from './component/Login';  // Komponen Login
import Register from './component/Register';  // Komponen Register
import DashboardUser from './component/DashboardUser'; 

function App() {
    return (
        <Router>
            <div className="App">
                {/* Routing */}
                <Routes>
                    <Route path="/" element={<Login />} />  {/* Rute default halaman login */}
                    <Route path="/login" element={<Login />} />  {/* Rute login */}
                    <Route path="/register" element={<Register />} />  {/* Rute registrasi */}
                    <Route path="/dashboard" element={<DashboardUser />} />  {/* Rute registrasi */}
                </Routes>
            </div>
        </Router>
    );
}

export default App;
