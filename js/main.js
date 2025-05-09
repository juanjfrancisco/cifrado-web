document.addEventListener('DOMContentLoaded', () => {
    // Inicializar los iconos de Lucide
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    } else {
        console.warn('Lucide icons library not found.');
    }

    // Configuración del gráfico (si el elemento existe)
    const chartElement = document.getElementById('performanceChart');
    if (chartElement) {
        const ctx = chartElement.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Trimestre 1', 'Trimestre 2', 'Trimestre 3', 'Trimestre 4'], // Etiquetas más descriptivas
                datasets: [{
                    label: 'Rendimiento Estratégico', // Etiqueta más descriptiva
                    data: [40, 65, 85, 95], // Datos de ejemplo
                    borderColor: '#60a5fa', // Azul claro para la línea
                    backgroundColor: 'rgba(59, 130, 246, 0.15)', // Azul primario con más opacidad
                    tension: 0.4, // Curvatura de la línea
                    fill: true,
                    pointBackgroundColor: '#ffffff', // Puntos blancos
                    pointBorderColor: '#60a5fa', // Borde de puntos azul
                    pointRadius: 5, // Tamaño de los puntos
                    pointHoverRadius: 7, // Tamaño de los puntos al pasar el mouse
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true, // Mostrar leyenda
                        position: 'bottom', // Posición de la leyenda
                        labels: {
                            color: 'rgba(255, 255, 255, 0.85)', // Color del texto de la leyenda
                            font: {
                                size: 14, // Tamaño de fuente de la leyenda
                                family: 'Lato, sans-serif'
                            }
                        }
                    },
                    tooltip: { // Mejorar tooltips
                        backgroundColor: 'rgba(0,0,0,0.7)',
                        titleFont: { size: 16, family: 'Lato, sans-serif' },
                        bodyFont: { size: 14, family: 'Lato, sans-serif' },
                        padding: 10,
                        cornerRadius: 4,
                        displayColors: false, // No mostrar el cuadrado de color en el tooltip
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.15)', // Color de la cuadrícula Y
                            borderColor: 'rgba(255, 255, 255, 0.15)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.85)', // Color de las etiquetas Y
                            font: {
                                family: 'Lato, sans-serif'
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)', // Color de la cuadrícula X
                            borderColor: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.85)', // Color de las etiquetas X
                             font: {
                                family: 'Lato, sans-serif'
                            }
                        }
                    }
                }
            }
        });
    } else {
        console.warn('Element with ID "performanceChart" not found for chart initialization.');
    }

    // Navegación móvil
    const menuButton = document.querySelector('.menu-button');
    const navLinks = document.querySelector('.nav-links');

    if (menuButton && navLinks) {
        menuButton.addEventListener('click', () => {
            navLinks.classList.toggle('active'); // Usar clase para mostrar/ocultar
            // Cambiar icono del menú (opcional)
            const menuIcon = menuButton.querySelector('i');
            if (navLinks.classList.contains('active')) {
                menuIcon.setAttribute('data-lucide', 'x');
            } else {
                menuIcon.setAttribute('data-lucide', 'menu');
            }
            if (typeof lucide !== 'undefined') { // Re-renderizar el icono
                lucide.createIcons();
            }
        });

        // Cerrar menú al hacer clic en un enlace (para SPAs o navegación en la misma página)
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (navLinks.classList.contains('active')) {
                    navLinks.classList.remove('active');
                    const menuIcon = menuButton.querySelector('i');
                    menuIcon.setAttribute('data-lucide', 'menu');
                     if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            });
        });

    } else {
        console.warn('Menu button or nav links not found for mobile navigation.');
    }

    // Smooth scroll para los enlaces de navegación (ya implementado, se mantiene)
    // y Active link highlighting
    const navAnchors = document.querySelectorAll('.nav-links a[href^="#"]');
    const sections = [];
    navAnchors.forEach(anchor => {
        const sectionId = anchor.getAttribute('href');
        try {
            const section = document.querySelector(sectionId);
            if (section) {
                sections.push({ anchor, section });
            }
        } catch (e) {
            console.warn(`Invalid selector for anchor ${sectionId}: ${e.message}`);
        }
    });
    
    function highlightNav() {
        let currentSectionId = '';
        const scrollPosition = window.scrollY;

        sections.forEach(({ anchor, section }) => {
            const sectionTop = section.offsetTop - 100; // Ajustar offset por header fijo
            const sectionHeight = section.offsetHeight;
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                currentSectionId = section.id;
            }
        });

        navAnchors.forEach(anchor => {
            anchor.classList.remove('active');
            if (anchor.getAttribute('href') === `#${currentSectionId}`) {
                anchor.classList.add('active');
            }
        });
    }
    window.addEventListener('scroll', highlightNav);
    highlightNav(); // Llamar una vez al cargar


    // Animación de números en la sección de estadísticas
    function animateNumbers() {
        const stats = document.querySelectorAll('.stats .number'); // Más específico
        stats.forEach(stat => {
            const targetText = stat.dataset.target || stat.innerText; // Usar data-target si existe
            const target = parseFloat(targetText.replace(/[^0-9.]/g, '')); // Limpiar string
            const suffix = targetText.replace(/[0-9.]/g, ''); // Obtener sufijo (+, %)
            
            if (isNaN(target)) return; // Si no es un número, salir

            let current = 0;
            const duration = 1500; // Duración de la animación en ms
            const stepTime = 20; // Intervalo de actualización
            const totalSteps = duration / stepTime;
            const increment = target / totalSteps;
            
            const updateNumber = () => {
                current += increment;
                if (current >= target) {
                    current = target;
                    stat.innerText = Math.floor(current) + suffix; // Usar floor para enteros
                    if (suffix === '%') { // Precisión para porcentajes
                         stat.innerText = target.toFixed(1) + suffix;
                    }
                    return;
                }
                
                stat.innerText = Math.floor(current) + suffix;
                 if (suffix === '%') {
                     stat.innerText = current.toFixed(1) + suffix;
                }
                requestAnimationFrame(updateNumber);
            };
            updateNumber();
        });
    }

    // Observer para activar la animación cuando la sección sea visible
    const statsSection = document.querySelector('.stats');
    if (statsSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateNumbers();
                    observer.unobserve(entry.target); // Animar solo una vez
                }
            });
        }, { threshold: 0.5 });
        observer.observe(statsSection);
    }

    // Validación del formulario (mejorada)
    const form = document.querySelector('.contact-form-fields');
    if (form) {
        const inputs = form.querySelectorAll('input[required], textarea[required]');

        form.addEventListener('submit', function(event) {
            let isValid = true;
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#ef4444'; // Rojo para error
                    // Podrías añadir un mensaje de error aquí
                } else {
                    input.style.borderColor = '#cbd5e1'; // Borde normal
                }
            });
            if (!isValid) {
                event.preventDefault(); // Prevenir envío si no es válido
                // Podrías mostrar un mensaje general de error
                alert('Por favor, completa todos los campos obligatorios.'); // Temporal, reemplazar con un mensaje en la UI
            } else {
                // Aquí iría la lógica de envío del formulario (AJAX, etc.)
                // event.preventDefault(); // Descomentar si manejas el envío con AJAX
                // alert('Formulario enviado (simulación)'); // Temporal
                console.log('Formulario listo para enviar');
            }
        });

        inputs.forEach(input => {
            input.addEventListener('blur', function() { // Validar al perder foco
                if (!this.value.trim() && this.hasAttribute('required')) {
                    this.style.borderColor = '#ef4444';
                } else {
                    this.style.borderColor = '#cbd5e1';
                }
            });
            input.addEventListener('focus', function() { // Estilo al ganar foco
                this.style.borderColor = '#3b82f6';
            });
            input.addEventListener('input', function() { // Quitar borde rojo al escribir
                 if (this.style.borderColor === 'rgb(239, 68, 68)') { // si es rojo
                    this.style.borderColor = '#3b82f6'; // cambiar a foco
                }
            });
        });
    }


    // Efecto de aparición gradual para elementos
    const fadeElements = document.querySelectorAll('.service-card, .expertise-item, .case-card, .about-content, .contact-form-container'); // Añadir más selectores si es necesario
    if (fadeElements.length > 0) {
        const fadeInObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible'); // Usar clase CSS
                    fadeInObserver.unobserve(entry.target); // Observar solo una vez
                }
            });
        }, {
            threshold: 0.1 // Porcentaje del elemento visible para activar
        });

        fadeElements.forEach(el => {
            el.classList.add('fade-in'); // Añadir clase base para la animación
            fadeInObserver.observe(el);
        });
    }

    // Actualizar año en el footer
    const currentYearSpan = document.getElementById('currentYear');
    if (currentYearSpan) {
        currentYearSpan.textContent = new Date().getFullYear();
    }

});
