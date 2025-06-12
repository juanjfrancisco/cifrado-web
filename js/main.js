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

    // Validación y envío del formulario con AJAX
    const form = document.querySelector('.contact-form-fields');
    if (form) {
        const inputs = form.querySelectorAll('input[required], textarea[required]');
        const submitBtn = form.querySelector('.btn-submit');
        
        // Crear elemento para mostrar mensajes
        const messageDiv = document.createElement('div');
        messageDiv.className = 'form-message';
        messageDiv.style.cssText = `
            margin: 1rem 0;
            padding: 1rem;
            border-radius: 0.5rem;
            display: none;
            font-weight: 500;
        `;
        form.insertBefore(messageDiv, submitBtn);
        
        // Función para mostrar mensajes
        function showMessage(message, type) {
            messageDiv.textContent = message;
            messageDiv.style.display = 'block';
            if (type === 'success') {
                messageDiv.style.backgroundColor = '#dcfce7';
                messageDiv.style.color = '#166534';
                messageDiv.style.border = '1px solid #bbf7d0';
            } else {
                messageDiv.style.backgroundColor = '#fef2f2';
                messageDiv.style.color = '#dc2626';
                messageDiv.style.border = '1px solid #fecaca';
            }
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 8000);
        }
        
        // Función para validar email
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }
        
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Validación del lado del cliente
            let isValid = true;
            const formData = new FormData();
            
            inputs.forEach(input => {
                const value = input.value.trim();
                if (!value) {
                    isValid = false;
                    input.style.borderColor = '#ef4444';
                } else {
                    input.style.borderColor = '#cbd5e1';
                    formData.append(input.name, value);
                }
            });
            
            // Validación especial para email
            const emailInput = form.querySelector('input[type="email"]');
            if (emailInput && emailInput.value.trim() && !isValidEmail(emailInput.value.trim())) {
                isValid = false;
                emailInput.style.borderColor = '#ef4444';
                showMessage('Por favor, ingresa un email válido.', 'error');
                return;
            }
            
            // Añadir campos opcionales
            const optionalInputs = form.querySelectorAll('input:not([required]), textarea:not([required])');
            optionalInputs.forEach(input => {
                if (input.value.trim()) {
                    formData.append(input.name, input.value.trim());
                }
            });
            
            if (!isValid) {
                showMessage('Por favor, completa todos los campos obligatorios.', 'error');
                return;
            }
            
            // Mostrar estado de carga
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            // Enviar formulario con AJAX
            fetch('contact.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showMessage(data.message, 'success');
                    form.reset(); // Limpiar formulario
                    inputs.forEach(input => {
                        input.style.borderColor = '#cbd5e1';
                    });
                } else {
                    showMessage(data.message || 'Error al enviar el mensaje.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error de conexión. Por favor, inténtalo nuevamente.', 'error');
            })
            .finally(() => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            });
        });

        inputs.forEach(input => {
            input.addEventListener('blur', function() { // Validar al perder foco
                if (!this.value.trim() && this.hasAttribute('required')) {
                    this.style.borderColor = '#ef4444';
                } else if (this.type === 'email' && this.value.trim() && !isValidEmail(this.value.trim())) {
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

    // Scroll to top button
    const scrollToTopBtn = document.createElement('button');
    scrollToTopBtn.className = 'scroll-to-top';
    scrollToTopBtn.setAttribute('aria-label', 'Volver arriba');
    scrollToTopBtn.innerHTML = '<i data-lucide="arrow-up"></i>';
    document.body.appendChild(scrollToTopBtn);

    // Show/hide scroll to top button
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            scrollToTopBtn.classList.add('visible');
        } else {
            scrollToTopBtn.classList.remove('visible');
        }
    });

    // Scroll to top functionality
    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Scroll progress indicator
    const progressBar = document.createElement('div');
    progressBar.className = 'scroll-progress';
    document.body.appendChild(progressBar);

    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        progressBar.style.width = scrollPercent + '%';
    });

    // Navbar scroll effect
    const header = document.querySelector('.header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Re-initialize Lucide icons after DOM changes
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

});
