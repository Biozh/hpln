// Détermine le thème par défaut en fonction des préférences du navigateur
function getNavigatorDefaultTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

// Récupère le thème depuis le localStorage ou utilise le thème par défaut du navigateur
function getCurrentTheme() {
    return localStorage.getItem('theme') ?? getNavigatorDefaultTheme();
}

// Applique le thème à la page
function applyTheme(theme) {
    document.body.setAttribute('data-bs-theme', theme);
    document.querySelectorAll('.project-icon').forEach(function(icon) {
        let src = icon.src;
        if(src.includes('logo_dark')) {
            src = src.replace('logo_dark', 'logo');
        } else {
            src = src.replace('logo', 'logo_dark');
        }
        icon.src = src;
    }
    );
    
    localStorage.setItem('theme', theme);
    updateThemeIcon(theme);
}

// Met à jour l'icône en fonction du thème actuel
function updateThemeIcon(theme) {
    const themeIcon = document.querySelector('.theme-icon .material-symbols-rounded');
    if (themeIcon) {
        themeIcon.textContent = theme === 'dark' ? 'dark_mode' : 'light_mode';
    }
}

// Bascule entre le thème clair et sombre
function toggleTheme() {
    const currentTheme = document.body.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    fadeOut(document.body, 300, function() {
        applyTheme(newTheme);
        fadeIn(document.body, 300);
    });
}

// Fonctions pour simuler fadeOut et fadeIn en JavaScript pur
function fadeOut(element, duration, callback) {
    element.style.transition = `opacity ${duration}ms`;
    element.style.opacity = 0;

    setTimeout(function() {
        callback();
    }, duration);
}

function fadeIn(element, duration) {
    element.style.transition = `opacity ${duration}ms`;
    element.style.opacity = 1;
}

// Initialisation : appliquer le thème au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const theme = getCurrentTheme();
    // applyTheme(theme);

    // Attacher l'événement click à l'élément de basculement du thème
    const themeToggler = document.querySelector('.theme-toggler');
    if (themeToggler) {
        themeToggler.addEventListener('click', toggleTheme);
    }
});
