// Check for saved user preference, if any, on load of the website
const currentTheme = localStorage.getItem('theme') ? localStorage.getItem('theme') : null;

if (currentTheme) {
    document.documentElement.setAttribute('data-theme', currentTheme);

    if (currentTheme === 'dark') {
        document.body.classList.add('dark-mode');
    }
}

// Function to switch theme
function switchTheme(e) {
    if (e.target.checked) {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.body.classList.add('dark-mode');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
        document.body.classList.remove('dark-mode');
        localStorage.setItem('theme', 'light');
    }
}

// Wait for DOM to load
document.addEventListener('DOMContentLoaded', () => {
    const toggleSwitch = document.querySelector('.theme-switch input[type="checkbox"]');
    if (toggleSwitch) {
        // Set toggle state based on current theme
        if (currentTheme === 'dark') {
            toggleSwitch.checked = true;
        }
        toggleSwitch.addEventListener('change', switchTheme, false);
    }
});
