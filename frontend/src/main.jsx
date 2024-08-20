import React from 'react'
import ReactDOM from 'react-dom/client'
import oravixSecurity from "./security.js";
import App from './App.jsx'
import "./main.css";
import Security from "./components/Security.jsx";
import CssBaseline from '@mui/material/CssBaseline';
import {ThemeProvider, createTheme} from '@mui/material/styles';
import {ToastContainer} from "react-toastify";
import {LocalizationProvider} from '@mui/x-date-pickers/LocalizationProvider';
import {AdapterDayjs} from '@mui/x-date-pickers/AdapterDayjs';

const darkTheme = createTheme({
    palette: {
        mode: 'dark',
    },
});

ReactDOM.createRoot(document.getElementById('root')).render(<>
    <ToastContainer position="top-left" />
    <Security oravixClass={oravixSecurity}>
        <ThemeProvider theme={darkTheme}>
            <CssBaseline enableColorScheme />
            <LocalizationProvider dateAdapter={AdapterDayjs}>
                <App />
            </LocalizationProvider>
        </ThemeProvider>
    </Security>
</>,)
