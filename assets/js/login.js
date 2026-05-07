/**
 * Animaciones GSAP - Login
 * Animaciones minimalistas y fluidas
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    initEntryAnimations();
    initInputAnimations();
    initButtonAnimations();
    initSubmitAnimation();
});

function initEntryAnimations() {
    const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

    tl.from('.login-card', {
        y: 40,
        autoAlpha: 0,
        duration: 0.8,
        clearProps: 'all'
    })
    .from('.logo', {
        scale: 0.8,
        autoAlpha: 0,
        duration: 0.6,
        ease: 'back.out(1.7)'
    }, '-=0.5')
    .from('.title', {
        y: 20,
        autoAlpha: 0,
        duration: 0.5
    }, '-=0.3')
    .from('.subtitle', {
        y: 15,
        autoAlpha: 0,
        duration: 0.5
    }, '-=0.3')
    .from('.form-group', {
        y: 25,
        autoAlpha: 0,
        duration: 0.6,
        stagger: 0.1
    }, '-=0.2')
    .from('.form-options', {
        y: 15,
        autoAlpha: 0,
        duration: 0.5
    }, '-=0.4')
    .from('.login-button', {
        y: 20,
        autoAlpha: 0,
        duration: 0.6,
        ease: 'back.out(1.2)'
    }, '-=0.3')
    .from('.login-footer', {
        y: 10,
        autoAlpha: 0,
        duration: 0.4
    }, '-=0.2');

    if (document.querySelector('.alert-error')) {
        gsap.from('.alert-error', {
            x: -20,
            autoAlpha: 0,
            duration: 0.5,
            ease: 'power2.out',
            delay: 0.3
        });
    }
}

function initInputAnimations() {
    const inputs = document.querySelectorAll('.form-input');

    inputs.forEach(function(input) {
        input.addEventListener('focus', function() {
            gsap.to(this.parentElement.querySelector('label'), {
                color: '#2d2d2d',
                duration: 0.2
            });
        });

        input.addEventListener('blur', function() {
            gsap.to(this.parentElement.querySelector('label'), {
                color: '#2d2d2d',
                duration: 0.2
            });
        });
    });
}

function initButtonAnimations() {
    const button = document.querySelector('.login-button');
    const icon = document.querySelector('.button-icon');

    if (!button || !icon) return;

    button.addEventListener('mouseenter', function() {
        gsap.to(icon, {
            x: 4,
            duration: 0.3,
            ease: 'power2.out'
        });
    });

    button.addEventListener('mouseleave', function() {
        gsap.to(icon, {
            x: 0,
            duration: 0.3,
            ease: 'power2.out'
        });
    });
}

function initSubmitAnimation() {
    const form = document.querySelector('.login-form');
    const button = document.querySelector('.login-button');
    const loadingMessage = document.querySelector('.loading-message');

    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formGroups = document.querySelectorAll('.form-group');
        const formOptions = document.querySelector('.form-options');
        const loginFooter = document.querySelector('.login-footer');

        const elementsToAnimate = [];

        formGroups.forEach(function(group) {
            elementsToAnimate.push(group);
        });

        if (formOptions) {
            elementsToAnimate.push(formOptions);
        }

        if (button) {
            elementsToAnimate.push(button);
        }

        gsap.to(elementsToAnimate, {
            autoAlpha: 0,
            y: -20,
            scale: 0.9,
            duration: 0.4,
            stagger: {
                each: 0.1,
                from: 'end'
            },
            ease: 'power2.in',
            onComplete: function() {
                if (loginFooter) {
                    gsap.to(loginFooter, {
                        autoAlpha: 0,
                        duration: 0.3
                    });
                }

                if (loadingMessage) {
                    loadingMessage.classList.add('visible');
                    gsap.to(loadingMessage, {
                        autoAlpha: 1,
                        duration: 0.4,
                        ease: 'power2.out'
                    });
                }

                setTimeout(function() {
                    form.submit();
                }, 800);
            }
        });
    });
}
