(function () {
    function getCurrentLang() {
        const params = new URLSearchParams(window.location.search);

        if (params.get('lang') === 'fr') {
            return 'fr';
        }

        if (window.getI18n && typeof window.getI18n === 'function') {
            const instance = window.getI18n();
            if (instance && typeof instance.getCurrentLang === 'function') {
                return instance.getCurrentLang() === 'fr' ? 'fr' : 'en';
            }
        }

        return localStorage.getItem('mhtech_lang') === 'fr' ? 'fr' : 'en';
    }

    function getPosts() {
        return Array.isArray(window.MHTECH_BLOG_POSTS) ? window.MHTECH_BLOG_POSTS : [];
    }

    function getCategories() {
        return Array.isArray(window.MHTECH_BLOG_CATEGORIES) ? window.MHTECH_BLOG_CATEGORIES : [];
    }

    function getCurrentPage() {
        return window.location.pathname.split('/').pop() || 'index.html';
    }

    function getPostBySlug(slug) {
        const posts = getPosts();
        return posts.find((post) => post.slug === slug) || posts[0] || null;
    }

    function getPostContent(post, lang) {
        return post && post[lang] ? post[lang] : post.en;
    }

    function buildPostUrl(slug) {
        const params = new URLSearchParams();
        params.set('slug', slug);

        if (getCurrentLang() === 'fr') {
            params.set('lang', 'fr');
        }

        return 'blog-details.html?' + params.toString();
    }

    function wireBlogListingLinks() {
        document.querySelectorAll('.blog-one__single[data-blog-slug]').forEach((card) => {
            const slug = card.getAttribute('data-blog-slug');
            if (!slug) {
                return;
            }

            card.querySelectorAll('a').forEach((link) => {
                link.setAttribute('href', buildPostUrl(slug));
            });
        });
    }

    function renderCategories(currentPost, lang) {
        const list = document.querySelector('.sidebar__category-list');
        if (!list || !currentPost) {
            return;
        }

        list.innerHTML = getCategories().map((category) => {
            const isActive = category.key === currentPost.category;
            const label = category[lang] || category.en;
            return (
                '<li' + (isActive ? ' class="active"' : '') + '>' +
                '<a href="blog.html">' +
                label +
                ' <span>(' + String(category.count).padStart(2, '0') + ')</span>' +
                '</a>' +
                '</li>'
            );
        }).join('');
    }

    function renderRecentPosts(currentPost, lang) {
        const list = document.querySelector('.sidebar__post-list');
        if (!list || !currentPost) {
            return;
        }

        const items = getPosts()
            .filter((post) => post.slug !== currentPost.slug)
            .sort((a, b) => b.sortDate.localeCompare(a.sortDate))
            .slice(0, 3)
            .map((post) => {
                const content = getPostContent(post, lang);
                return (
                    '<li>' +
                    '<div class="sidebar__post-image">' +
                    '<img src="' + post.heroImage + '" alt="' + content.heroAlt + '">' +
                    '</div>' +
                    '<div class="sidebar__post-content">' +
                    '<p class="sidebar__post-date"><span class="icon-calendar"></span>' + content.displayDate + '</p>' +
                    '<h3 class="sidebar__post-title"><a href="' + buildPostUrl(post.slug) + '">' + content.title + '</a></h3>' +
                    '</div>' +
                    '</li>'
                );
            });

        list.innerHTML = items.join('');
    }

    function renderTagCloud(currentPost, lang) {
        const list = document.querySelector('.sidebar__tags-list');
        if (!list || !currentPost) {
            return;
        }

        const content = getPostContent(currentPost, lang);
        list.innerHTML = content.tags.map((tag) => '<li><a href="blog.html">' + tag + '</a></li>').join('');
    }

    function renderBlogDetail() {
        const slug = new URLSearchParams(window.location.search).get('slug') || 'cybersecurity-controls-smb';
        const post = getPostBySlug(slug);
        if (!post) {
            return;
        }

        const lang = getCurrentLang();
        const content = getPostContent(post, lang);

        const pageHeaderBg = document.querySelector('.page-header__bg');
        const pageTitle = document.querySelector('.page-header__inner h3');
        const breadcrumbCurrent = document.querySelector('.thm-breadcrumb li:last-child');
        const heroImage = document.querySelector('.blog-details__img > img');
        const dateBadge = document.querySelector('.blog-details__date p');
        const author = document.querySelector('.blog-details__user p span:last-child');
        const comments = document.querySelector('.blog-details__meta li:nth-child(1) span:last-child');
        const readTime = document.querySelector('.blog-details__meta li:nth-child(2) span:last-child');
        const title = document.querySelector('.blog-details__title');
        const text1 = document.querySelector('.blog-details__text-1');
        const text2 = document.querySelector('.blog-details__text-2');
        const quote = document.querySelector('.blog-details__author-text');
        const subtitle = document.querySelector('.blog-details__title-2');
        const text3 = document.querySelector('.blog-details__text-3');
        const bodyImages = document.querySelectorAll('.blog-details__img-box-img img');
        const tagLinks = document.querySelectorAll('.blog-details__tag-list a');

        if (pageHeaderBg) {
            pageHeaderBg.style.backgroundImage = 'url("' + post.heroImage + '")';
        }

        if (pageTitle) {
            pageTitle.textContent = content.title;
        }

        if (breadcrumbCurrent) {
            breadcrumbCurrent.textContent = content.title;
        }

        if (heroImage) {
            heroImage.src = post.heroImage;
            heroImage.alt = content.heroAlt;
        }

        if (dateBadge) {
            dateBadge.innerHTML = content.dateBadgeHtml;
        }

        if (author) {
            author.textContent = content.author;
        }

        if (comments) {
            comments.textContent = content.comments;
        }

        if (readTime) {
            readTime.textContent = content.readTime;
        }

        if (title) {
            title.textContent = content.title;
        }

        if (text1) {
            text1.textContent = content.intro;
        }

        if (text2) {
            text2.textContent = content.body;
        }

        if (quote) {
            quote.textContent = content.quote;
        }

        if (subtitle) {
            subtitle.textContent = content.subtitle;
        }

        if (text3) {
            text3.textContent = content.conclusion;
        }

        bodyImages.forEach((image, index) => {
            if (post.bodyImages[index]) {
                image.src = post.bodyImages[index];
                image.alt = content.bodyAlts[index] || content.title;
            }
        });

        tagLinks.forEach((link, index) => {
            if (content.tags[index]) {
                link.textContent = content.tags[index];
            }
        });

        document.title = content.pageTitle + ' | MHTECH Consulting';

        renderCategories(post, lang);
        renderRecentPosts(post, lang);
        renderTagCloud(post, lang);

        if (window.updateSeoHead && typeof window.updateSeoHead === 'function') {
            window.updateSeoHead();
        }
    }

    function runPageEnhancements() {
        const page = getCurrentPage();

        if (page === 'blog.html') {
            wireBlogListingLinks();
        }

        if (page === 'blog-details.html') {
            renderBlogDetail();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(runPageEnhancements, 250);
        setTimeout(runPageEnhancements, 800);
    });

    window.addEventListener('load', function () {
        setTimeout(runPageEnhancements, 250);
    });

    document.addEventListener('click', function (event) {
        if (event.target.closest('.lang-btn')) {
            setTimeout(runPageEnhancements, 350);
            setTimeout(runPageEnhancements, 900);
        }
    });
})();
