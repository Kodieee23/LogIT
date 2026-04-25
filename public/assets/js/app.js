// assets/js/app.js

document.addEventListener('DOMContentLoaded', () => {
    // Mobile Sidebar Toggle
    const menuBtn = document.querySelector('.mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');

    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('active');
            menuBtn.classList.toggle('active');
        });

        // Close sidebar when clicking outside of it
        document.addEventListener('click', (e) => {
            if (sidebar.classList.contains('active') && !sidebar.contains(e.target) && e.target !== menuBtn) {
                sidebar.classList.remove('active');
                menuBtn.classList.remove('active');
            }
        });
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => alert.remove(), 300); // Wait for transition
        }, 5000);
    });

    // Custom Select Implementation
    document.querySelectorAll('.js-custom-select').forEach(nativeSelect => {
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-container';
        nativeSelect.parentNode.insertBefore(wrapper, nativeSelect);
        wrapper.appendChild(nativeSelect);
        
        const trigger = document.createElement('div');
        trigger.className = 'custom-select-trigger';
        const selectedOption = nativeSelect.options[nativeSelect.selectedIndex];
        trigger.innerHTML = `<span>${selectedOption ? selectedOption.text : ''}</span><i class='bx bx-chevron-down'></i>`;
        wrapper.appendChild(trigger);
        
        const optionsList = document.createElement('div');
        optionsList.className = 'custom-select-options';
        
        Array.from(nativeSelect.options).forEach((option, index) => {
            const customOption = document.createElement('div');
            customOption.className = `custom-option ${index === nativeSelect.selectedIndex ? 'selected' : ''}`;
            customOption.textContent = option.text;
            customOption.dataset.value = option.value;
            
            customOption.addEventListener('click', () => {
                nativeSelect.value = option.value;
                trigger.querySelector('span').textContent = option.text;
                optionsList.querySelectorAll('.custom-option').forEach(opt => opt.classList.remove('selected'));
                customOption.classList.add('selected');
                
                const event = new Event('change', { bubbles: true });
                nativeSelect.dispatchEvent(event);
                
                optionsList.classList.remove('open');
                trigger.classList.remove('active');
                const parentCard = wrapper.closest('.glass-card');
                if (parentCard) parentCard.style.zIndex = '';
            });
            optionsList.appendChild(customOption);
        });
        
        wrapper.appendChild(optionsList);
        
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = optionsList.classList.contains('open');
            document.querySelectorAll('.custom-select-options').forEach(opt => opt.classList.remove('open'));
            document.querySelectorAll('.custom-select-trigger').forEach(trig => trig.classList.remove('active'));
            document.querySelectorAll('.glass-card').forEach(card => card.style.zIndex = '');
            
            if (!isOpen) {
                optionsList.classList.add('open');
                trigger.classList.add('active');
                const parentCard = wrapper.closest('.glass-card');
                if (parentCard) {
                    parentCard.style.zIndex = '50';
                    parentCard.style.position = 'relative';
                }
            }
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-select-options').forEach(opt => opt.classList.remove('open'));
        document.querySelectorAll('.custom-select-trigger').forEach(trig => trig.classList.remove('active'));
        document.querySelectorAll('.glass-card').forEach(card => card.style.zIndex = '');
    });

    // Initialize particles.js if the container exists (Login Page)
    if (document.getElementById('particles-js')) {
        try {
            particlesJS("particles-js", {
            "particles": {
                "number": {
                    "value": 80,
                    "density": {
                        "enable": true,
                        "value_area": 800
                    }
                },
                "color": {
                    "value": "#ffffff"
                },
                "shape": {
                    "type": "circle",
                    "stroke": {
                        "width": 0,
                        "color": "#000000"
                    },
                    "polygon": {
                        "nb_sides": 5
                    }
                },
                "opacity": {
                    "value": 0.5,
                    "random": false,
                    "anim": {
                        "enable": false,
                        "speed": 1,
                        "opacity_min": 0.1,
                        "sync": false
                    }
                },
                "size": {
                    "value": 3,
                    "random": true,
                    "anim": {
                        "enable": false,
                        "speed": 40,
                        "size_min": 0.1,
                        "sync": false
                    }
                },
                "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#ffffff",
                    "opacity": 0.4,
                    "width": 1
                },
                "move": {
                    "enable": true,
                    "speed": 2,
                    "direction": "none",
                    "random": false,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false,
                    "attract": {
                        "enable": false,
                        "rotateX": 600,
                        "rotateY": 1200
                    }
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "grab"
                    },
                    "onclick": {
                        "enable": true,
                        "mode": "push"
                    },
                    "resize": true
                },
                "modes": {
                    "grab": {
                        "distance": 140,
                        "line_linked": {
                            "opacity": 1
                        }
                    },
                    "bubble": {
                        "distance": 400,
                        "size": 40,
                        "duration": 2,
                        "opacity": 8,
                        "speed": 3
                    },
                    "repulse": {
                        "distance": 200,
                        "duration": 0.4
                    },
                    "push": {
                        "particles_nb": 4
                    },
                    "remove": {
                        "particles_nb": 2
                    }
                }
            },
            "retina_detect": true
        });
        } catch (e) { console.log('ParticlesJS skipped on mobile or not loaded'); }
    }
});
