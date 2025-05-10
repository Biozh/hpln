import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App.jsx'

import './assets/scss/app.scss'
import './hpln.scss'

import 'animate.css';

import '@fortawesome/fontawesome-free/js/solid'
import '@fortawesome/fontawesome-free/js/fontawesome'
import '@fortawesome/fontawesome-free/js/brands'
import { ThemeProvider } from './utils/useTheme.jsx'

ReactDOM.createRoot(document.getElementById('root')).render(
    <ThemeProvider>
        <App />
    </ThemeProvider>
)
