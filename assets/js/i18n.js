/**
 * MHTECH i18n - Internationalization System
 * Gestion du changement de langue FR/EN
 */

class I18n {
    constructor() {
        this.currentLang = localStorage.getItem('mhtech_lang') || 'fr';
        this.translations = {};
        this.init();
    }

    async init() {
        await this.loadTranslations(this.currentLang);
        this.updatePageLanguage();
        this.attachEventListeners();
    }

    async loadTranslations(lang) {
        try {
            const response = await fetch(`assets/js/lang/${lang}.json`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            this.translations = await response.json();
            this.currentLang = lang;
            localStorage.setItem('mhtech_lang', lang);
        } catch (error) {
            console.error('Erreur lors du chargement des traductions:', error);
            // Fallback vers français si erreur
            if (lang !== 'fr') {
                await this.loadTranslations('fr');
            }
        }
    }

    translate(key) {
        const keys = key.split('.');
        let value = this.translations;

        for (const k of keys) {
            if (value && typeof value === 'object' && k in value) {
                value = value[k];
            } else {
                console.warn(`Traduction manquante pour: ${key}`);
                return key;
            }
        }

        return value;
    }

    updatePageLanguage() {
        // Mise à jour de l'attribut lang du HTML
        document.documentElement.lang = this.currentLang;

        // Mise à jour de tous les éléments avec data-i18n
        const elements = document.querySelectorAll('[data-i18n]');
        elements.forEach(element => {
            const key = element.getAttribute('data-i18n');
            const translation = this.translate(key);

            // Vérifier si c'est un placeholder, value ou text content
            if (element.hasAttribute('placeholder')) {
                element.setAttribute('placeholder', translation);
            } else if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                element.value = translation;
            } else {
                element.textContent = translation;
            }
        });

        // Mise à jour des placeholders avec data-i18n-placeholder
        const placeholderElements = document.querySelectorAll('[data-i18n-placeholder]');
        placeholderElements.forEach(element => {
            const key = element.getAttribute('data-i18n-placeholder');
            const translation = this.translate(key);
            element.setAttribute('placeholder', translation);
        });

        // Mise à jour des éléments avec data-i18n-html (pour le HTML)
        const htmlElements = document.querySelectorAll('[data-i18n-html]');
        htmlElements.forEach(element => {
            const key = element.getAttribute('data-i18n-html');
            const translation = this.translate(key);
            element.innerHTML = translation;
        });

        // Mise à jour du titre de la page
        this.updatePageTitle();

        // Mise à jour du bouton de langue actif
        this.updateLanguageButton();
    }

    updatePageTitle() {
        const pageTitleMap = {
            'index.html': {
                'fr': 'Accueil | MHTECH Consulting',
                'en': 'Home | MHTECH Consulting'
            },
            'about.html': {
                'fr': 'A propos | MHTECH Consulting',
                'en': 'About | MHTECH Consulting'
            },
            'services.html': {
                'fr': 'Services IT | MHTECH Consulting',
                'en': 'IT Services | MHTECH Consulting'
            },
            'staffing.html': {
                'fr': 'Staffing IT | MHTECH Consulting',
                'en': 'IT Staffing | MHTECH Consulting'
            },
            'contact.html': {
                'fr': 'Contact | MHTECH Consulting',
                'en': 'Contact | MHTECH Consulting'
            },
            'blog.html': {
                'fr': 'Blog | MHTECH Consulting',
                'en': 'Blog | MHTECH Consulting'
            },
            'testimonials.html': {
                'fr': 'Temoignages | MHTECH Consulting',
                'en': 'Testimonials | MHTECH Consulting'
            },
            'projects.html': {
                'fr': 'Projets | MHTECH Consulting',
                'en': 'Projects | MHTECH Consulting'
            }
        };

        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        if (pageTitleMap[currentPage] && pageTitleMap[currentPage][this.currentLang]) {
            document.title = pageTitleMap[currentPage][this.currentLang];
        }
    }

    updateLanguageButton() {
        const langButtons = document.querySelectorAll('.lang-btn');
        langButtons.forEach(btn => {
            if (btn.getAttribute('data-lang') === this.currentLang) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    async changeLanguage(lang) {
        if (lang === this.currentLang) return;

        // Animation de transition (optionnel)
        document.body.style.opacity = '0.7';

        await this.loadTranslations(lang);
        this.updatePageLanguage();

        // Fin de l'animation
        setTimeout(() => {
            document.body.style.opacity = '1';
        }, 200);
    }

    attachEventListeners() {
        // Écouter les clics sur les boutons de langue
        document.addEventListener('click', (e) => {
            const langBtn = e.target.closest('.lang-btn');
            if (langBtn) {
                e.preventDefault();
                const lang = langBtn.getAttribute('data-lang');
                this.changeLanguage(lang);
            }
        });
    }

    getCurrentLang() {
        return this.currentLang;
    }
}

// Initialisation automatique au chargement du DOM
let i18nInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    i18nInstance = new I18n();
});

// Export pour utilisation globale
window.I18n = I18n;
window.getI18n = () => i18nInstance;
