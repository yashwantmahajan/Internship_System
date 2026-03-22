/* Main Scripts for Internship System */

document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Mobile Menu Toggle (if we add one in index.php)
    const menuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if(menuBtn && navLinks) {
        menuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            menuBtn.classList.toggle('open');
        });
    }

    // 2. Input Animation Logic (Optional visual flair)
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
             input.parentElement.classList.add('focused');
        });
        input.addEventListener('blur', () => {
            if(input.value === '') {
                input.parentElement.classList.remove('focused');
            }
        });
    });

    // 3. Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    if(alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    }

    // 4. Custom Cursor Effect
    const cursor = document.createElement('div');
    cursor.classList.add('custom-cursor');
    document.body.appendChild(cursor);

    document.addEventListener('mousemove', e => {
        cursor.style.left = e.clientX + 'px';
        cursor.style.top = e.clientY + 'px';
    });

    document.querySelectorAll('a, button, .btn, input, select, .glass-panel').forEach(el => {
        el.addEventListener('mouseenter', () => cursor.classList.add('cursor-hover'));
        el.addEventListener('mouseleave', () => cursor.classList.remove('cursor-hover'));
    });
});
