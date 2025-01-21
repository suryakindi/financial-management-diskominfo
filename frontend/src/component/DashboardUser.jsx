import React, { useState, useEffect } from 'react';
import { AppBar, Toolbar, Typography, Box, Drawer, List, ListItem, ListItemText, Button, TextField, Table, TableHead, TableBody, TableRow, TableCell, TableContainer, Paper, FormControl, InputLabel, Select, MenuItem } from '@mui/material';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

const Transaksi = ({ id_user }) => {
    const [categoryOptions, setCategoryOptions] = useState([]);
    const [selectedCategory, setSelectedCategory] = useState('');
    const [amount, setAmount] = useState('');
    const [description, setDescription] = useState('');
    const [error, setError] = useState('');

    const fetchCategories = async () => {
        try {
            const response = await axios.get('http://127.0.0.1:8000/api/v1/get-category', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
            });
            if (response.data.status === 'success') {
                setCategoryOptions(response.data.data);
            }
        } catch (error) {
            console.error('Error fetching categories:', error);
            setError('Failed to fetch categories');
        }
    };

    useEffect(() => {
        fetchCategories();
    }, []);

    const handleAddTransaction = async () => {
        if (!selectedCategory || !amount || !description) {
            setError('Please fill in all fields');
            return;
        }

        try {
            const response = await axios.post(
                'http://127.0.0.1:8000/api/v1/transactions',
                {
                    id_user: id_user,
                    id_category: selectedCategory,
                    amount,
                    description,
                },
                {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Content-Type': 'application/json',
                    },
                }
            );
            if (response.status === 201) {
                alert('Transaction added successfully');
                setSelectedCategory('');
                setAmount('');
                setDescription('');
                window.location.reload();
            }
        } catch (error) {
            alert(error.response.data.detailError);
            console.error('Error adding transaction:', error);
            setError('Transaksi Gagal');
        }
    };

    return (
        <Box>
            <Typography variant="h6" gutterBottom>Transaksi</Typography>
            
            {error && <Typography color="error">{error}</Typography>}

            <Box sx={{ marginBottom: 2 }}>
                <FormControl fullWidth sx={{ marginBottom: 2 }}>
                    <InputLabel>Select Category</InputLabel>
                    <Select
                        value={selectedCategory}
                        onChange={(e) => setSelectedCategory(e.target.value)}
                        label="Select Category"
                    >
                        {categoryOptions.map((category) => (
                            <MenuItem key={category.id} value={category.id}>
                                {category.category.charAt(0).toUpperCase() + category.category.slice(1)}
                            </MenuItem>
                        ))}
                    </Select>
                </FormControl>
            </Box>
            
            <Box sx={{ marginBottom: 2 }}>
                <TextField
                    label="Amount"
                    variant="outlined"
                    value={amount}
                    onChange={(e) => setAmount(e.target.value)}
                    fullWidth
                    type="number"
                />
            </Box>
            
            <Box sx={{ marginBottom: 2 }}>
                <TextField
                    label="Description"
                    variant="outlined"
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                    fullWidth
                />
            </Box>

            <Button
                variant="contained"
                color="primary"
                sx={{ marginTop: 2 }}
                onClick={handleAddTransaction}
            >
                Add Transaction
            </Button>
        </Box>
    );
};


const Budget = ({ id_user }) => {
    const [categoryOptions, setCategoryOptions] = useState([]);
    const [selectedCategory, setSelectedCategory] = useState('');
    const [amount, setAmount] = useState('');
    const [description, setDescription] = useState('');
    const [error, setError] = useState('');
    const [budgets, setBudgets] = useState([]);

    const fetchCategories = async () => {
        try {
            const response = await axios.get('http://127.0.0.1:8000/api/v1/get-category', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
            });
            if (response.data.status === 'success') {
                setCategoryOptions(response.data.data);
            }
        } catch (error) {
            setError('Failed to fetch categories');
        }
    };

    const fetchBudgets = async () => {
        try {
            const response = await axios.get(`http://127.0.0.1:8000/api/v1/get-budgets?id_user=${id_user}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
            });
            if (response.data.status === 'success') {
                setBudgets(response.data.data);
            }
        } catch (error) {
            setError('Failed to fetch budgets');
        }
    };

    useEffect(() => {
        fetchCategories();
        fetchBudgets();
    }, [id_user]);

    const handleUpdateBudget = async () => {
        if (!selectedCategory || !amount || !description) {
            setError('Please fill in all fields');
            return;
        }

        try {
            const response = await axios.put(
                'http://127.0.0.1:8000/api/v1/budgets',
                {
                    id_user: id_user,
                    id_category: selectedCategory,
                    amount,
                    description,
                },
                {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Content-Type': 'application/json',
                    },
                }
            );
            if (response.status === 201) {
                alert('Budget Updated');
                setSelectedCategory('');
                setAmount('');
                setDescription('');
                fetchBudgets();
                window.location.reload();
            }
        } catch (error) {
            setError('Failed to update budget');
        }
    };

    const handleRefund = async (budgetId, userID) => {
        try {
            const response = await axios.get(
                `http://127.0.0.1:8000/api/v1/refund-budgets?id_budget=${budgetId}&id_user=${userID}&id_category=3`, 
                {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Content-Type': 'application/json',
                    },
                }
            );
            if (response.status === 201) {
                alert('Refund Sukses');
                fetchBudgets();
                window.location.reload();
            }
        } catch (error) {
            setError('Failed to refund budget');
        }
    };

    return (
        <Box>
            <Typography variant="h6" gutterBottom>Budget</Typography>
         
            <Box sx={{ marginBottom: 2 }}>
                <FormControl fullWidth sx={{ marginBottom: 2 }}>
                    <InputLabel>Select Category</InputLabel>
                    <Select
                        value={selectedCategory}
                        onChange={(e) => setSelectedCategory(e.target.value)}
                        label="Select Category"
                    >
                        {categoryOptions.map((category) => (
                            <MenuItem key={category.id} value={category.id}>
                                {category.category.charAt(0).toUpperCase() + category.category.slice(1)}
                            </MenuItem>
                        ))}
                    </Select>
                </FormControl>
            </Box>
            <Box sx={{ marginBottom: 2 }}>
                <TextField
                    label="Amount"
                    variant="outlined"
                    value={amount}
                    onChange={(e) => setAmount(e.target.value)}
                    fullWidth
                    type="number"
                />
            </Box>
            <Box sx={{ marginBottom: 2 }}>
                <TextField
                    label="Description"
                    variant="outlined"
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                    fullWidth
                />
            </Box>
            <Button
                variant="contained"
                color="primary"
                sx={{ marginTop: 2 }}
                onClick={handleUpdateBudget}
            >
                Update Budget
            </Button>
            <TableContainer component={Paper} sx={{ marginTop: 3 }}>
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableCell>Category</TableCell>
                            <TableCell>Amount</TableCell>
                            <TableCell>Status</TableCell>
                            <TableCell>Action</TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {budgets.map((budget) => (
                            <TableRow key={budget.id}>
                                <TableCell>{budget.category_name}</TableCell>
                                <TableCell>{budget.amount}</TableCell>
                                <TableCell>{budget.status ? 'Active' : 'Inactive'}</TableCell>
                                <TableCell>
                                    {budget.status && (
                                        <Button
                                            variant="outlined"
                                            color="secondary"
                                            onClick={() => handleRefund(budget.id, id_user )}
                                        >
                                            Refund
                                        </Button>
                                    )}
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </TableContainer>
        </Box>
    );
};



const Reminder = ({ id_user }) => {
    const [reminders, setReminders] = useState([]);
    const [title, setTitle] = useState('');
    const [amount, setAmount] = useState('');
    const [dueDate, setDueDate] = useState('');
    const [error, setError] = useState('');
    const [fetchError, setFetchError] = useState('');

    const fetchReminders = async () => {
        try {
            const response = await axios.get(`http://127.0.0.1:8000/api/v1/get-reminders?id_user=${id_user}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
            });
            if (response.data.status === 'success') {
                setReminders(response.data.data);
            }
        } catch (error) {
            console.error('Error fetching reminders:', error);
            setFetchError('Failed to fetch reminders');
        }
    };

    const handlePayReminder = async (reminderId, UserID) => {
        try {
            const response = await axios.post(
                `http://127.0.0.1:8000/api/v1/pay-reminders`,
                {
                    id_reminder: reminderId,
                    id_category : 99,
                    id_user : UserID
                },
                {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Content-Type': 'application/json',
                    },
                }
            );
            if (response.status === 201) {
                alert('Reminder Sukses Dibayar');
                window.location.reload();
                fetchReminders();
            }
        } catch (error) {
            console.error('Error paying reminder:', error);
            setError('Failed to pay reminder');
        }
    };

    useEffect(() => {
        fetchReminders();
    }, [id_user]);

    const handleAddReminder = async () => {
        if (!title || !amount || !dueDate) {
            setError('Please fill in all fields');
            return;
        }

        try {
            const response = await axios.post(
                'http://127.0.0.1:8000/api/v1/reminders',
                {
                    id_user,
                    title,
                    amount,
                    due_date: dueDate,
                },
                {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Content-Type': 'application/json',
                    },
                }
            );
            if (response.status === 201) {
                alert('Reminder successfully added');
                setTitle('');
                setAmount('');
                setDueDate('');
                fetchReminders();
            }
        } catch (error) {
            console.error('Error adding reminder:', error);
            setError('Failed to add reminder');
        }
    };

    return (
        <Box>
            <Typography variant="h6" gutterBottom>Reminder</Typography>
            {error && <Typography color="error">{error}</Typography>}
            <Box sx={{ marginBottom: 2 }}>
                <TextField
                    label="Title"
                    variant="outlined"
                    value={title}
                    onChange={(e) => setTitle(e.target.value)}
                    fullWidth
                    sx={{ marginBottom: 2 }}
                />
                <TextField
                    label="Amount"
                    variant="outlined"
                    value={amount}
                    onChange={(e) => setAmount(e.target.value)}
                    fullWidth
                    type="number"
                    sx={{ marginBottom: 2 }}
                />
                <TextField
                    label="Due Date"
                    variant="outlined"
                    value={dueDate}
                    onChange={(e) => setDueDate(e.target.value)}
                    fullWidth
                    type="date"
                    InputLabelProps={{
                        shrink: true,
                    }}
                />
            </Box>
            <Button
                variant="contained"
                color="primary"
                onClick={handleAddReminder}
                sx={{ marginBottom: 2 }}
            >
                Add Reminder
            </Button>

           

            <TableContainer component={Paper}>
                <Table sx={{ minWidth: 650 }} aria-label="reminder table">
                    <TableHead>
                        <TableRow>
                            <TableCell>Title</TableCell>
                            <TableCell>Amount</TableCell>
                            <TableCell>Due Date</TableCell>
                            <TableCell>Status</TableCell>
                            <TableCell>Action</TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {reminders.length === 0 ? (
                            <TableRow>
                                <TableCell colSpan={5} align="center">
                                    No reminders found
                                </TableCell>
                            </TableRow>
                        ) : (
                            reminders.map((reminder) => (
                                <TableRow key={reminder.id}>
                                    <TableCell>{reminder.title}</TableCell>
                                    <TableCell>{reminder.amount}</TableCell>
                                    <TableCell>
                                        {new Date(reminder.due_date).toLocaleDateString()}
                                    </TableCell>
                                    <TableCell>
                                        {reminder.status ? 'Completed' : 'Pending'}
                                    </TableCell>
                                    <TableCell>
                                        {!reminder.status && (
                                            <Button
                                                variant="contained"
                                                color="secondary"
                                                onClick={() => handlePayReminder(reminder.id, id_user)}
                                            >
                                                Bayar
                                            </Button>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </TableContainer>
        </Box>
    );
};

const Report = ({ id_user }) => {
    const [transactions, setTransactions] = useState([]);
    const [month, setMonth] = useState(1); 
    const [year, setYear] = useState(2025); 
    const [filteredTransactions, setFilteredTransactions] = useState([]); 
    const [total_income, setIncome] = useState([]);
    const [total_expense, SetExpense] = useState([]);
    
    useEffect(() => {
        const fetchTransactions = async () => {
            try {
                const response = await axios.get(`http://127.0.0.1:8000/api/v1/reports/monthly?user_id=${id_user}&month=${month}&year=${year}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Content-Type': 'application/json',
                    },
                });
                
                const data = response.data?.data || [];
                
                setTransactions(data.detail_data); 
                setIncome(data.total_income);
                SetExpense(data.total_expense);
                setFilteredTransactions(data.detail_data);  
            } catch (error) {
                console.error('Error fetching transactions:', error);
                setFilteredTransactions([]);
                setTransactions([]); 
                setIncome(0);
                SetExpense(0);
            }
        };

        if (id_user) {
            fetchTransactions();  
        }
    }, [month, year, id_user]); 

    return (
        <Box>
            <Typography variant="h6" gutterBottom>
                Report Bulanan
            </Typography>

            <Box sx={{ marginBottom: 2 }}>
                <TextField
                    label="Pilih Bulan"
                    type="number"
                    value={month}
                    onChange={(e) => setMonth(parseInt(e.target.value))}
                    fullWidth
                    sx={{ marginBottom: 2 }}
                    inputProps={{ min: 1, max: 12 }}
                />
                <TextField
                    label="Pilih Tahun"
                    type="number"
                    value={year}
                    onChange={(e) => setYear(parseInt(e.target.value))}
                    fullWidth
                />
            </Box>

            {/* Make sure filteredTransactions is always an array */}
            <TableContainer component={Paper}>
            <Table sx={{ minWidth: 650 }} aria-label="simple table">
                <TableHead>
                    <TableRow>
                        <TableCell>Nama User</TableCell>
                        <TableCell>Tipe Kategori</TableCell>
                        <TableCell>Tipe Type</TableCell>
                        <TableCell>Jumlah</TableCell>
                        <TableCell>Description</TableCell>
                        <TableCell>Date</TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {Array.isArray(filteredTransactions) && filteredTransactions.map((transaction, index) => (
                        <TableRow key={index}>
                            <TableCell>{transaction.user_name}</TableCell>
                            <TableCell>{transaction.category_name}</TableCell>
                            <TableCell>{transaction.type_name}</TableCell>
                            <TableCell>{transaction.amount}</TableCell>
                            <TableCell>{transaction.description}</TableCell>
                            <TableCell>{new Date(transaction.date).toLocaleDateString()}</TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </TableContainer>

        
        <div style={{ marginTop: "20px", padding: "10px", border: "1px solid #ccc", borderRadius: "5px" }}>
            <Typography variant="h6" component="h2">Hasil Laporan</Typography>
            <Typography>Total Income: {total_income}</Typography>
            <Typography>Total Expense: {total_expense}</Typography>
           
        </div>

        </Box>
    );
};



const CreateCategory = () => {
    const [categoryName, setCategoryName] = useState('');
    const [categoryType, setCategoryType] = useState('');
    const [error, setError] = useState('');
    const [typeOptions, setTypeOptions] = useState([]);

    const fetchCategoryTypes = async () => {
        try {
            const response = await axios.get(
                'http://127.0.0.1:8000/api/v1/get-type',
                {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Content-Type': 'application/json',
                    },
                }
            );
            if (response.data.status === 'success') {
                setTypeOptions(response.data.data);
            }
        } catch (error) {
            console.error('Error fetching category types:', error);
            setError('Failed to fetch category types');
        }
    };

    useEffect(() => {
        fetchCategoryTypes();
    }, []);

    const handleAddCategory = async () => {
        if (!categoryName || !categoryType) {
            setError('Please fill in all fields');
            return;
        }

        try {
            const response = await axios.post(
                'http://127.0.0.1:8000/api/v1/create-category',
                {
                    category: categoryName,
                    id_type: categoryType,
                },
                {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Content-Type': 'application/json',
                    },
                }
            );
            if (response.status === 201) {
                alert('Category sukses ditambahkan');
                setCategoryName('');
                setCategoryType('');
            }
        } catch (error) {
            console.error('Error adding category:', error);
            setError('Failed to add category');
        }
    };

    return (
        <Box>
            <Typography variant="h6" gutterBottom>Create Category</Typography>
        
            {error && <Typography color="error">{error}</Typography>}
          
            <Box sx={{ marginBottom: 2 }}>
                <TextField
                    label="Category Name"
                    variant="outlined"
                    value={categoryName}
                    onChange={(e) => setCategoryName(e.target.value)}
                    fullWidth
                />
            </Box>
            <FormControl fullWidth sx={{ marginBottom: 2 }}>
                <InputLabel>Select Type</InputLabel>
                <Select
                    value={categoryType}
                    onChange={(e) => setCategoryType(e.target.value)}
                    label="Select Type"
                >
                    {typeOptions.map((type) => (
                        <MenuItem key={type.id} value={type.id}>
                            {type.type.charAt(0).toUpperCase() + type.type.slice(1)}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            <Button
                variant="contained"
                color="primary"
                sx={{ marginTop: 2 }}
                onClick={handleAddCategory}
            >
                Add Category
            </Button>
        </Box>
    );
};

const DashboardUser = () => {
    const navigate = useNavigate();
    const [selectedTab, setSelectedTab] = useState(0);
    const [balance, setBalance] = useState(1000);
    const [isAuthenticated, setIsAuthenticated] = useState(true); 
    const [id_user, setID] = useState(null);  // Setting default id_user state to null
    const [totalReminders, setTotalReminders] = useState(0);
    useEffect(() => {
        const checkAuth = async () => {
            const token = localStorage.getItem('token');
            if (!token) {
                navigate('/login');
                return;
            }
            try {
                const response = await axios.get('http://127.0.0.1:8000/api/v1/check-token', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                    },
                });
                if (response.status !== 200) {
                    throw new Error('Token tidak valid');
                }
                setBalance(response.data.data.balance);
                setID(response.data.data.id); // Setting id_user here from API response
                setIsAuthenticated(true);
            } catch (error) {
                alert('Sesi Anda telah berakhir. Silakan login kembali.');
                localStorage.removeItem('token');
                navigate('/login');
            }
        };

        checkAuth();
    }, [navigate]);

    useEffect(() => {
        const fetchTotalReminders = async () => {
            if (id_user) {
                try {
                    const response = await axios.get(
                        `http://127.0.0.1:8000/api/v1/check-reminders?id_user=${id_user}`,
                        {
                            headers: {
                                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                                'Content-Type': 'application/json',
                            },
                        }
                    );
                    if (response.data.status === 'success') {
                        console.log(response.data.data.total);
                        setTotalReminders(response.data.data.total); 
                    }
                } catch (error) {
                    console.error('Error fetching reminders:', error);
                }
            }
        };

        fetchTotalReminders();
    }, [id_user]);

    const formatIDR = (amount) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
        }).format(amount);
    };

    const handleLogout = () => {
        alert("Logout");
        localStorage.removeItem('token');
        navigate('/login');
    };

    return (
        <Box sx={{ display: 'flex' }}>
            <Drawer
                sx={{
                    width: 240,
                    flexShrink: 0,
                    '& .MuiDrawer-paper': {
                        width: 240,
                        boxSizing: 'border-box',
                    },
                }}
                variant="permanent"
                anchor="left"
            >
                <List>
                    <ListItem button onClick={() => setSelectedTab(0)}>
                        <ListItemText primary="Transaksi" />
                    </ListItem>
                    <ListItem button onClick={() => setSelectedTab(1)}>
                        <ListItemText primary="Budget" />
                    </ListItem>
                    <ListItem button onClick={() => setSelectedTab(2)}>
                        <ListItemText primary="Reminder" />
                        <ListItemText
                            primary={`(${totalReminders})`}
                            sx={{
                                fontWeight: 'bold',         // Make it bold
                                color: 'error.main',        // Red color to emphasize importance
                                textAlign: 'right',         // Align the text to the right
                            }}
                        />
                    </ListItem>

                    <ListItem button onClick={() => setSelectedTab(3)}>
                        <ListItemText primary="Report" />
                    </ListItem>
                   
                    <ListItem button onClick={() => setSelectedTab(5)}>
                        <ListItemText primary="Create Category" />
                    </ListItem>
                </List>
            </Drawer>

            <Box sx={{ flexGrow: 1 }}>
                <AppBar position="static">
                    <Toolbar sx={{ display: 'flex', justifyContent: 'space-between' }}>
                        <Typography variant="h6">Manage Uang Dashboard</Typography>
                        <Typography variant="body1" sx={{ marginRight: 2 }}>
                            Balance: {formatIDR(balance)}
                        </Typography>
                        <Button color="inherit" onClick={handleLogout}>Logout</Button>
                    </Toolbar>
                </AppBar>

                <Box sx={{ padding: 3 }}>
                    {selectedTab === 0 && id_user && <Transaksi id_user={id_user} />}
                    {selectedTab === 1 && id_user && <Budget id_user={id_user} />}
                    {selectedTab === 2 && id_user && <Reminder id_user={id_user} />}
                    {selectedTab === 3 && id_user && <Report id_user={id_user} />}
                    {selectedTab === 5 && <CreateCategory />}
                </Box>
            </Box>
        </Box>
    );
};

export default DashboardUser;
