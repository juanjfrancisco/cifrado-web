// Inicializar los iconos de Lucide
lucide.createIcons();

// Configuración del gráfico
const ctx = document.getElementById('performanceChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Q1', 'Q2', 'Q3', 'Q4'],
        datasets: [{
            label: 'Rendimiento',
            data: [40, 65, 85, 95],
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                },
                ticks: {
                    color: 'rgba(255, 255, 255, 0.7)'
                }
            },
            x: {
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                },
                ticks: {
                    color: 'rgba(255, 255, 255, 0.7)'
                }
            }
        }
    }
});

// Navegación móvil
const menuButton = document.querySelector('.menu-button');
const navLinks = document.querySelector('.nav-links');

menuButton?.addEventListener('click', () => {
    navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
});

// Smooth scroll para los enlaces de navegación
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Animación de números en la sección de estadísticas
function animateNumbers() {
    const stats = document.querySelectorAll('.number');
    
    stats.forEach(stat => {
        const target = parseFloat(stat.innerText);
        let current = 0;
        
        const increment = target / 50; // Velocidad de la animación
        
        const updateNumber = () => {
            if (current < target) {
                current += increment;
                if (current > target) current = target;
                
                // Formatear el número según su tipo
                if (stat.innerText.includes('+')) {
                    stat.innerText = Math.floor(current) + '+';
                } else if (stat.innerText.includes('%')) {
                    stat.innerText = current.toFixed(1) + '%';
                } else {
                    stat.innerText = Math.floor(current);
                }
                
                requestAnimationFrame(updateNumber);
            }
        };
        
        updateNumber();
    });
}

// Observer para activar la animación cuando la sección sea visible
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateNumbers();
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

// Observar la sección de estadísticas
const statsSection = document.querySelector('.stats');
if (statsSection) {
    observer.observe(statsSection);
}

// Validación del formulario
const form = document.querySelector('.contact-form');
const inputs = form.querySelectorAll('input, textarea');

inputs.forEach(input => {
    input.addEventListener('blur', function() {
        if (!this.value) {
            this.style.borderColor = '#ef4444';
        } else {
            this.style.borderColor = '#e2e8f0';
        }
    });

    input.addEventListener('focus', function() {
        this.style.borderColor = '#3b82f6';
    });
});

// Efecto de aparición gradual para las tarjetas de servicios
const serviceCards = document.querySelectorAll('.service-card');

const fadeInObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, {
    threshold: 0.1
});

serviceCards.forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    fadeInObserver.observe(card);
});