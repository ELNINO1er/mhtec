/**
 * MHTECH i18n - Internationalization System
 * Gestion du changement de langue FR/EN
 */

class I18n {
    constructor() {
        this.currentLang = this.getInitialLanguage();
        this.translations = {};
        this.init();
    }

    getInitialLanguage() {
        const params = new URLSearchParams(window.location.search);
        const urlLang = params.get('lang');

        if (urlLang === 'en') {
            return 'en';
        }

        if (urlLang === 'fr') {
            return 'fr';
        }

        return localStorage.getItem('mhtech_lang') === 'en' ? 'en' : 'fr';
    }

    syncLanguageUrl(lang) {
        const url = new URL(window.location.href);

        if (lang === 'en') {
            url.searchParams.set('lang', 'en');
        } else {
            url.searchParams.delete('lang');
        }

        window.history.replaceState({}, '', url.toString());
    }

    getCurrentPage() {
        const path = window.location.pathname.replace(/\/+$/, '');
        const lastSegment = path.split('/').pop() || 'index';

        if (lastSegment === '' || lastSegment === 'index') {
            return 'index.html';
        }

        return lastSegment.endsWith('.html') ? lastSegment : `${lastSegment}.html`;
    }

    async init() {
        await this.loadTranslations(this.currentLang);
        this.updatePageLanguage();
        this.attachEventListeners();
    }

    async loadTranslations(lang) {
        try {
            const response = await fetch(`assets/js/lang/${lang}.json?v=3`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            this.translations = await response.json();
            this.currentLang = lang;
            localStorage.setItem('mhtech_lang', lang);
            this.syncLanguageUrl(lang);
            this.updateLanguageButton();
        } catch (error) {
            console.error('Erreur lors du chargement des traductions:', error);
            if (lang !== 'en') {
                await this.loadTranslations('en');
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
        this.resetAnimatedTitles();

        document.documentElement.lang = this.currentLang;

        const elements = document.querySelectorAll('[data-i18n]');
        elements.forEach((element) => {
            const key = element.getAttribute('data-i18n');
            const translation = this.translate(key);

            if (element.hasAttribute('placeholder')) {
                element.setAttribute('placeholder', translation);
            } else if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                element.value = translation;
            } else if (this.containsHtml(translation)) {
                element.innerHTML = translation;
            } else if (element.children.length > 0) {
                this.updateElementTextPreservingChildren(element, translation);
            } else {
                element.textContent = translation;
            }
        });

        const placeholderElements = document.querySelectorAll('[data-i18n-placeholder]');
        placeholderElements.forEach((element) => {
            const key = element.getAttribute('data-i18n-placeholder');
            const translation = this.translate(key);
            element.setAttribute('placeholder', translation);
        });

        const htmlElements = document.querySelectorAll('[data-i18n-html]');
        htmlElements.forEach((element) => {
            const key = element.getAttribute('data-i18n-html');
            const translation = this.translate(key);
            element.innerHTML = translation;
        });

        this.updateSearchInputs();
        this.updatePageTitle();
        this.updateLanguageButton();
        this.syncFormLanguageFields();
        this.refreshAnimatedTitles();
        this.refreshSelectPickers();
        window.dispatchEvent(new CustomEvent('mhtech:langchange', {
            detail: {
                lang: this.currentLang
            }
        }));
    }

    containsHtml(value) {
        return /<\/?[a-z][\s\S]*>/i.test(value);
    }

    resetAnimatedTitles() {
        const animatedTitles = document.querySelectorAll('.sec-title-animation .title-animation');
        animatedTitles.forEach((title) => {
            if (title.animation) {
                title.animation.progress(1).kill();
                title.animation = null;
            }

            if (title.split && typeof title.split.revert === 'function') {
                title.split.revert();
                title.split = null;
            }
        });
    }

    refreshAnimatedTitles() {
        if (window.ScrollTrigger && typeof window.ScrollTrigger.refresh === 'function') {
            window.ScrollTrigger.refresh();
        }
    }

    refreshSelectPickers() {
        if (window.jQuery) {
            const $ = window.jQuery;
            if (typeof $.fn.niceSelect === 'function') {
                $('select').niceSelect('update');
            }
            if (typeof $.fn.selectpicker === 'function') {
                $('.selectpicker').selectpicker('refresh');
            }
        }
    }

    updateSearchInputs() {
        const searchPlaceholder = this.translate('search.placeholder');
        const searchInputs = document.querySelectorAll('input[name="search-field"], input[name="q"], .sidebar__search-form input[type="search"]');

        searchInputs.forEach((input) => {
            if (!input.hasAttribute('data-i18n-placeholder')) {
                input.setAttribute('data-i18n-placeholder', 'search.placeholder');
            }

            input.setAttribute('placeholder', searchPlaceholder);
            input.setAttribute('aria-label', searchPlaceholder);
        });
    }

    syncFormLanguageFields() {
        const forms = document.querySelectorAll('form[action*="assets/inc/"]');

        forms.forEach((form) => {
            let input = form.querySelector('input[name="lang"]');

            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'lang';
                form.appendChild(input);
            }

            input.value = this.currentLang;
        });
    }

    updateElementTextPreservingChildren(element, translation) {
        const textNodes = Array.from(element.childNodes).filter(
            (node) => node.nodeType === Node.TEXT_NODE
        );

        if (textNodes.length > 0) {
            textNodes[0].textContent = `${translation} `;
            textNodes.slice(1).forEach((node) => {
                if (node.textContent.trim()) {
                    node.textContent = '';
                }
            });
            return;
        }

        element.prepend(document.createTextNode(`${translation} `));
    }

    updatePageTitle() {
        const currentPage = this.getCurrentPage();
        const pageKeyMap = {
            'index.html': 'meta',
            'about.html': 'about_page',
            'services.html': 'services_page',
            'staffing.html': 'staffing_page',
            'contact.html': 'contact_page',
            'blog.html': 'blog_page',
            'blog-details.html': 'blog_details',
            'testimonials.html': 'testimonials_page',
            'projects.html': 'projects_page',
            'software-development.html': 'software_dev',
            'web-development.html': 'web_dev',
            'ui-ux-design.html': 'ui_ux',
            'digital-marketing.html': 'digital_marketing',
            'business-analysis.html': 'business_analysis',
            'product-design.html': 'product_design'
        };

        const pageKey = pageKeyMap[currentPage];
        const pageTitle = pageKey && this.translations[pageKey] && this.translations[pageKey].page_title;

        if (pageTitle) {
            document.title = pageTitle.includes('MHTECH Consulting')
                ? pageTitle
                : `${pageTitle} | MHTECH Consulting`;
        }
    }

    updateLanguageButton() {
        const langButtons = document.querySelectorAll('.lang-btn');
        langButtons.forEach((btn) => {
            if (btn.getAttribute('data-lang') === this.currentLang) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    async changeLanguage(lang) {
        if (lang === this.currentLang) return;

        document.body.style.opacity = '0.7';

        await this.loadTranslations(lang);
        this.updatePageLanguage();

        setTimeout(() => {
            document.body.style.opacity = '1';
        }, 200);
    }

    attachEventListeners() {
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

let i18nInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    i18nInstance = new I18n();
});

window.I18n = I18n;
window.getI18n = () => i18nInstance;
