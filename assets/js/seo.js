(function () {
    const SITE = {
        origin: "https://mhtechconsulting.com",
        name: "MHTECH Consulting",
        logo: "https://mhtechconsulting.com/assets/images/resources/logo-1.png",
        email: "contact@mhtechconsulting.com",
        telephone: "+1 (248) 938 1944",
        telephoneHref: "+12489381944"
    };

    const PAGE_META = {
        "index.html": {
            type: "WebPage",
            en: {
                name: "Home",
                description: "MHTECH Consulting supports businesses with IT consulting, cybersecurity, cloud and digital solutions."
            },
            fr: {
                name: "Accueil",
                description: "MHTECH Consulting accompagne les entreprises avec des solutions IT, cloud, cybersécurité et transformation digitale."
            }
        },
        "about.html": {
            type: "AboutPage",
            en: {
                name: "About MHTECH",
                description: "Discover MHTECH Consulting, our mission, values and expertise in IT consulting, cybersecurity, cloud and digital transformation."
            },
            fr: {
                name: "A propos de MHTECH",
                description: "Découvrez MHTECH Consulting, notre mission, nos valeurs et notre expertise en conseil IT, cybersécurité, cloud et transformation digitale."
            }
        },
        "services.html": {
            type: "CollectionPage",
            en: {
                name: "IT Services",
                description: "Explore our IT services: cybersecurity, DevOps and Cloud, digital design, IT operations, strategic consulting and project governance."
            },
            fr: {
                name: "Services IT",
                description: "Découvrez nos services IT : cybersécurité, DevOps et Cloud, design digital, opérations IT, conseil stratégique et pilotage de projet."
            }
        },
        "staffing.html": {
            type: "Service",
            en: {
                name: "IT Staffing",
                description: "MHTECH Consulting helps companies recruit qualified IT talent and supports candidates with relevant technology opportunities."
            },
            fr: {
                name: "Staffing IT",
                description: "MHTECH Consulting aide les entreprises à recruter des talents IT qualifiés et accompagne les candidats dans leurs opportunités."
            }
        },
        "contact.html": {
            type: "ContactPage",
            en: {
                name: "Contact",
                description: "Contact MHTECH Consulting for IT consulting, cybersecurity, cloud, staffing and digital transformation projects."
            },
            fr: {
                name: "Contact",
                description: "Contactez MHTECH Consulting pour vos projets de conseil IT, cybersécurité, cloud, staffing et transformation digitale."
            }
        },
        "blog.html": {
            type: "Blog",
            en: {
                name: "Blog",
                description: "Read MHTECH Consulting insights on cybersecurity, DevOps, cloud, IT operations and technology staffing."
            },
            fr: {
                name: "Blog",
                description: "Retrouvez les analyses de MHTECH Consulting sur la cybersécurité, le DevOps, le cloud, les opérations IT et le staffing."
            }
        },
        "testimonials.html": {
            type: "CollectionPage",
            en: {
                name: "Testimonials",
                description: "See what clients say about MHTECH Consulting and our IT consulting, staffing and delivery support."
            },
            fr: {
                name: "Temoignages",
                description: "Découvrez les retours de nos clients sur l'accompagnement MHTECH Consulting en conseil IT, staffing et delivery."
            }
        },
        "projects.html": {
            type: "CollectionPage",
            en: {
                name: "Projects",
                description: "Browse selected MHTECH Consulting projects across cybersecurity, cloud, digital delivery and IT operations."
            },
            fr: {
                name: "Projets",
                description: "Parcourez une sélection de projets MHTECH Consulting en cybersécurité, cloud, delivery digital et opérations IT."
            }
        },
        "software-development.html": {
            type: "Service",
            en: {
                name: "Cybersecurity",
                description: "Reduce business risk with pragmatic cybersecurity services covering access protection, resilience and exposure management."
            },
            fr: {
                name: "Cybersecurite",
                description: "Réduisez les risques avec des services de cybersécurité pragmatiques autour de la protection des accès, de la résilience et de l'exposition."
            }
        },
        "web-development.html": {
            type: "Service",
            en: {
                name: "DevOps and Cloud",
                description: "Accelerate delivery with DevOps and Cloud services focused on automation, reliability, infrastructure and deployment pipelines."
            },
            fr: {
                name: "DevOps et Cloud",
                description: "Accélérez vos livraisons avec des services DevOps et Cloud centrés sur l'automatisation, la fiabilité, l'infrastructure et les pipelines."
            }
        },
        "ui-ux-design.html": {
            type: "Service",
            en: {
                name: "Digital Design and Experience",
                description: "Design clear and effective digital experiences aligned with user needs, business goals and brand consistency."
            },
            fr: {
                name: "Design et experience digitale",
                description: "Concevez des expériences digitales claires et efficaces alignées sur les usages, les objectifs business et la cohérence de marque."
            }
        },
        "digital-marketing.html": {
            type: "Service",
            en: {
                name: "IT Operations and Help Desk",
                description: "Structure IT operations and support with dependable processes, clearer visibility and stronger day-to-day service quality."
            },
            fr: {
                name: "Operations IT et Help Desk",
                description: "Structurez vos opérations IT et votre support avec des processus fiables, plus de visibilité et une meilleure qualité de service."
            }
        },
        "business-analysis.html": {
            type: "Service",
            en: {
                name: "Strategic IT Consulting",
                description: "Clarify technology priorities, governance and investment decisions with strategic IT consulting aligned to business needs."
            },
            fr: {
                name: "Conseil IT strategique",
                description: "Clarifiez vos priorités technologiques, votre gouvernance et vos arbitrages avec un conseil IT stratégique aligné au métier."
            }
        },
        "product-design.html": {
            type: "Service",
            en: {
                name: "Project Analysis and Governance",
                description: "Frame IT projects with clearer requirements, governance, decision-making and delivery structure."
            },
            fr: {
                name: "Analyse et pilotage projet",
                description: "Cadrez vos projets IT avec des besoins mieux formalisés, une gouvernance plus claire et une structure de pilotage solide."
            }
        },
        "blog-details.html": {
            type: "BlogPosting",
            en: {
                name: "Blog article",
                description: "Read a MHTECH Consulting article on cybersecurity, cloud, IT operations or staffing."
            },
            fr: {
                name: "Article de blog",
                description: "Lisez un article MHTECH Consulting sur la cybersécurité, le cloud, les opérations IT ou le staffing."
            }
        }
    };

    const PAGE_BREADCRUMBS = {
        "about.html": ["about.html"],
        "services.html": ["services.html"],
        "staffing.html": ["staffing.html"],
        "contact.html": ["contact.html"],
        "blog.html": ["blog.html"],
        "testimonials.html": ["testimonials.html"],
        "projects.html": ["projects.html"],
        "software-development.html": ["services.html", "software-development.html"],
        "web-development.html": ["services.html", "web-development.html"],
        "ui-ux-design.html": ["services.html", "ui-ux-design.html"],
        "digital-marketing.html": ["services.html", "digital-marketing.html"],
        "business-analysis.html": ["services.html", "business-analysis.html"],
        "product-design.html": ["services.html", "product-design.html"]
    };

    function getCurrentPage() {
        return window.location.pathname.split("/").pop() || "index.html";
    }

    function getCurrentLang() {
        const params = new URLSearchParams(window.location.search);
        const urlLang = params.get("lang");

        if (urlLang === "fr") {
            return "fr";
        }

        if (window.getI18n && typeof window.getI18n === "function") {
            const instance = window.getI18n();
            if (instance && typeof instance.getCurrentLang === "function") {
                return instance.getCurrentLang() === "fr" ? "fr" : "en";
            }
        }

        return localStorage.getItem("mhtech_lang") === "fr" ? "fr" : "en";
    }

    function getPageMeta(page, lang) {
        const pageMeta = PAGE_META[page];
        if (!pageMeta) {
            return null;
        }

        return pageMeta[lang] || pageMeta.en;
    }

    function buildPageUrl(page, lang, extraParams) {
        const url = page === "index.html"
            ? new URL("/", SITE.origin + "/")
            : new URL(page, SITE.origin + "/");
        const params = new URLSearchParams(extraParams || {});

        if (lang === "fr") {
            params.set("lang", "fr");
        } else {
            params.delete("lang");
        }

        const query = params.toString();
        url.search = query;
        return url.toString();
    }

    function getCurrentExtraParams(page) {
        const params = new URLSearchParams(window.location.search);

        if (page === "blog-details.html") {
            const slug = params.get("slug");
            return slug ? { slug: slug } : {};
        }

        return {};
    }

    function ensureMeta(selector, factory) {
        let element = document.head.querySelector(selector);
        if (!element) {
            element = factory();
            document.head.appendChild(element);
        }
        return element;
    }

    function setMetaName(name, content) {
        const meta = ensureMeta('meta[name="' + name + '"]', function () {
            const element = document.createElement("meta");
            element.setAttribute("name", name);
            return element;
        });

        meta.setAttribute("content", content);
    }

    function setMetaProperty(property, content) {
        const meta = ensureMeta('meta[property="' + property + '"]', function () {
            const element = document.createElement("meta");
            element.setAttribute("property", property);
            return element;
        });

        meta.setAttribute("content", content);
    }

    function setLink(rel, href, hreflang) {
        let selector = 'link[rel="' + rel + '"]';
        if (hreflang) {
            selector += '[hreflang="' + hreflang + '"]';
        }

        const link = ensureMeta(selector, function () {
            const element = document.createElement("link");
            element.setAttribute("rel", rel);
            if (hreflang) {
                element.setAttribute("hreflang", hreflang);
            }
            return element;
        });

        link.setAttribute("href", href);
    }

    function upsertJsonLd(id, payload) {
        const selector = 'script[data-seo-id="' + id + '"]';
        let script = document.head.querySelector(selector);

        if (!script) {
            script = document.createElement("script");
            script.type = "application/ld+json";
            script.setAttribute("data-seo-id", id);
            document.head.appendChild(script);
        }

        script.textContent = JSON.stringify(payload);
    }

    function absolutizeAsset(path) {
        if (!path) {
            return SITE.logo;
        }

        if (/^https?:\/\//i.test(path)) {
            return path;
        }

        return SITE.origin + "/" + path.replace(/^\/+/, "");
    }

    function getBlogPost(slug) {
        const posts = Array.isArray(window.MHTECH_BLOG_POSTS) ? window.MHTECH_BLOG_POSTS : [];
        return posts.find(function (post) {
            return post.slug === slug;
        }) || posts[0] || null;
    }

    function getSeoContext() {
        const page = getCurrentPage();
        const lang = getCurrentLang();
        const extraParams = getCurrentExtraParams(page);
        const pageMeta = getPageMeta(page, lang);

        if (!pageMeta) {
            return null;
        }

        const context = {
            page: page,
            lang: lang,
            pageType: PAGE_META[page].type,
            pageName: pageMeta.name,
            description: pageMeta.description,
            canonicalUrl: buildPageUrl(page, lang, extraParams),
            englishUrl: buildPageUrl(page, "en", extraParams),
            frenchUrl: buildPageUrl(page, "fr", extraParams),
            xDefaultUrl: buildPageUrl(page, "en", extraParams),
            image: SITE.logo
        };

        if (page === "blog-details.html") {
            const slug = extraParams.slug || "cybersecurity-controls-smb";
            const post = getBlogPost(slug);

            if (post) {
                const content = post[lang] || post.en;
                context.pageName = content.pageTitle || content.title;
                context.description = content.intro;
                context.image = absolutizeAsset(post.heroImage);
                context.post = post;
                context.postContent = content;
            }
        }

        return context;
    }

    function buildOrganizationSchema() {
        return {
            "@context": "https://schema.org",
            "@type": "Organization",
            name: SITE.name,
            url: SITE.origin,
            logo: SITE.logo,
            email: SITE.email,
            telephone: SITE.telephoneHref,
            contactPoint: [
                {
                    "@type": "ContactPoint",
                    contactType: "customer support",
                    email: SITE.email,
                    telephone: SITE.telephoneHref,
                    availableLanguage: ["en", "fr"]
                }
            ]
        };
    }

    function buildBreadcrumbSchema(context) {
        const trail = PAGE_BREADCRUMBS[context.page] || [];
        const items = [{
            "@type": "ListItem",
            position: 1,
            name: context.lang === "fr" ? "Accueil" : "Home",
            item: buildPageUrl("index.html", context.lang, {})
        }];

        trail.forEach(function (page, index) {
            const pageMeta = getPageMeta(page, context.lang);
            if (!pageMeta) {
                return;
            }

            items.push({
                "@type": "ListItem",
                position: index + 2,
                name: pageMeta.name,
                item: buildPageUrl(page, context.lang, {})
            });
        });

        if (context.page === "blog-details.html" && context.postContent) {
            items.push({
                "@type": "ListItem",
                position: 2,
                name: context.lang === "fr" ? "Blog" : "Blog",
                item: buildPageUrl("blog.html", context.lang, {})
            });

            items.push({
                "@type": "ListItem",
                position: 3,
                name: context.postContent.title,
                item: context.canonicalUrl
            });
        }

        if (context.page !== "index.html" && context.page !== "blog-details.html" && trail.length === 0) {
            items.push({
                "@type": "ListItem",
                position: 2,
                name: context.pageName,
                item: context.canonicalUrl
            });
        }

        return {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            itemListElement: items
        };
    }

    function buildPageSchema(context) {
        if (context.page === "index.html") {
            return {
                "@context": "https://schema.org",
                "@type": "WebSite",
                name: SITE.name,
                url: SITE.origin,
                inLanguage: context.lang
            };
        }

        if (context.page === "blog.html") {
            return {
                "@context": "https://schema.org",
                "@type": "Blog",
                name: context.pageName,
                url: context.canonicalUrl,
                description: context.description,
                inLanguage: context.lang,
                publisher: {
                    "@type": "Organization",
                    name: SITE.name,
                    logo: {
                        "@type": "ImageObject",
                        url: SITE.logo
                    }
                }
            };
        }

        if (context.page === "blog-details.html" && context.post && context.postContent) {
            return {
                "@context": "https://schema.org",
                "@type": "BlogPosting",
                headline: context.postContent.title,
                description: context.description,
                image: [context.image],
                author: {
                    "@type": "Organization",
                    name: SITE.name
                },
                publisher: {
                    "@type": "Organization",
                    name: SITE.name,
                    logo: {
                        "@type": "ImageObject",
                        url: SITE.logo
                    }
                },
                datePublished: context.post.sortDate,
                dateModified: context.post.sortDate,
                mainEntityOfPage: context.canonicalUrl,
                inLanguage: context.lang
            };
        }

        return {
            "@context": "https://schema.org",
            "@type": context.pageType,
            name: context.pageName,
            url: context.canonicalUrl,
            description: context.description,
            inLanguage: context.lang
        };
    }

    function updateInternalLinks(lang) {
        document.querySelectorAll('a[href]').forEach(function (link) {
            const rawHref = link.getAttribute("href");

            if (!rawHref ||
                rawHref.startsWith("#") ||
                rawHref.startsWith("mailto:") ||
                rawHref.startsWith("tel:") ||
                rawHref.startsWith("javascript:") ||
                rawHref.startsWith("http://") ||
                rawHref.startsWith("https://") ||
                rawHref.startsWith("//")) {
                return;
            }

            if (!/\.html(\?|$)/i.test(rawHref)) {
                return;
            }

            const url = new URL(rawHref, window.location.href);

            if (lang === "fr") {
                url.searchParams.set("lang", "fr");
            } else {
                url.searchParams.delete("lang");
            }

            const relativeHref = url.pathname.split("/").pop() + (url.search ? url.search : "") + (url.hash ? url.hash : "");
            link.setAttribute("href", relativeHref);
        });
    }

    function applySeo() {
        const context = getSeoContext();
        if (!context) {
            return;
        }

        const ogType = context.page === "blog-details.html" ? "article" : "website";
        const pageTitle = document.title || (context.pageName + " | " + SITE.name);

        setMetaName("description", context.description);
        setMetaProperty("og:site_name", SITE.name);
        setMetaProperty("og:type", ogType);
        setMetaProperty("og:title", pageTitle);
        setMetaProperty("og:description", context.description);
        setMetaProperty("og:url", context.canonicalUrl);
        setMetaProperty("og:image", context.image);
        setMetaProperty("og:locale", context.lang === "fr" ? "fr_FR" : "en_US");
        setMetaName("twitter:card", "summary_large_image");
        setMetaName("twitter:title", pageTitle);
        setMetaName("twitter:description", context.description);
        setMetaName("twitter:image", context.image);

        setLink("canonical", context.canonicalUrl);
        setLink("alternate", context.englishUrl, "en");
        setLink("alternate", context.frenchUrl, "fr");
        setLink("alternate", context.xDefaultUrl, "x-default");

        upsertJsonLd("organization", buildOrganizationSchema());
        upsertJsonLd("page", buildPageSchema(context));
        upsertJsonLd("breadcrumb", buildBreadcrumbSchema(context));

        updateInternalLinks(context.lang);
    }

    function queueSeoRefresh() {
        applySeo();
        setTimeout(applySeo, 250);
        setTimeout(applySeo, 800);
    }

    document.addEventListener("DOMContentLoaded", queueSeoRefresh);
    window.addEventListener("load", queueSeoRefresh);
    window.addEventListener("mhtech:langchange", queueSeoRefresh);

    window.updateSeoHead = applySeo;
})();
