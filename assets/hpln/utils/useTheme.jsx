
import { createContext, useContext, useEffect, useState } from "react";

export const ThemeContext = createContext()

export const ThemeProvider = ({ children }) => {
  const [theme, setTheme] = useState()
  const THEME = {
    LIGHT: 'light',
    DARK: 'dark',
  }
  
  const IS_SERVER = typeof window === 'undefined'

  useEffect(() => {
    initTheme()
  }, [])
  
  const getDefaultTheme = () => (
    window.matchMedia('(prefers-color-scheme: dark)').matches
      ? THEME.DARK
      : THEME.LIGHT
  )
  
  const getPreferredTheme = () => {
    const storedTheme = localStorage.getItem('theme')
    if (storedTheme) return storedTheme
  
    const defaultTheme = getDefaultTheme()
    localStorage.setItem('theme', defaultTheme)
  
    return defaultTheme
  }
  
  function handleSetTheme(theme) {
    if (IS_SERVER) return
    document.documentElement.dataset.bsTheme = theme
    localStorage.setItem('theme', theme)
    document.querySelector("body").dataBsTheme = theme
    setTheme(theme)
  }
  
  function resetTheme() {
    if (IS_SERVER) return
    handleSetTheme(getDefaultTheme())
  }
  
  function toggleTheme() {
    if (IS_SERVER) return
    const nextTheme =
      document.documentElement.dataset.bsTheme === THEME.DARK
        ? THEME.LIGHT
        : THEME.DARK
    handleSetTheme(nextTheme)
  }
  
  function initTheme() {
    if (IS_SERVER) return
    handleSetTheme(getPreferredTheme())
  }

  return (
    <ThemeContext.Provider value={{ theme, toggleTheme }}>
      {children} 
    </ThemeContext.Provider>
  );
};

export const useTheme = () => useContext(ThemeContext)