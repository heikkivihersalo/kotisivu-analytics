/**
 * Get constants from PHP
 */
const TAGMANAGER_ID = serverside_localize.id;
const TAGMANAGER_URL = serverside_localize.url;
const TAGMANAGER_TIMEOUT = serverside_localize.timeout;
const TAGMANAGER_CONTAINER = serverside_localize.js_container;
const CONSENT_PROVIDER = serverside_localize.cmp_provider;
const CONSENT_TRACKING = serverside_localize.cmp_tracking;
const CONSENT_ID = serverside_localize.cmp_id;


/**
 * Init global objects
 */
const initGlobals = () => {
    return new Promise((resolve, reject) => {
        /**
         * Consent
         */
        window.consent = {
            /**
             * Cookiehub initial load
             * @param {string} id 
             */
            loadCookiehub: (id) => {
                return new Promise(resolve => {
                    let cpm = {};
                    let element = document.getElementsByTagName("script")[0];
                    let script = document.createElement("script");
                    script.type = 'text/javascript';
                    script.src = `https://cookiehub.net/c2/${id}.js`;

                    script.addEventListener('load', () => {
                        window.cookiehub.load(cpm);
                        resolve("Cookiehub loaded");
                    });

                    element.parentNode.insertBefore(script, element);

                });
            },


            /**
             * Cookiebot initial load
             * @param {string} id 
             * @param {string} mode 
             */
            loadCookiebot: (id, mode) => {
                return new Promise(resolve => {
                    let element = document.getElementsByTagName("script")[0];
                    let script = document.createElement("script");
                    script.type = 'text/javascript';
                    script.id = 'Cookiebot';
                    script.setAttribute('data-cbid', id);
                    script.setAttribute('data-blockingmode', mode);
                    script.src = `https://consent.cookiebot.com/uc.js`;

                    script.addEventListener('load', () => {
                        resolve("Cookiebot loaded");
                    });

                    element.parentNode.insertBefore(script, element);
                });
            },


            /**
             * Cookie Consent Status
             * @return {promise} has consent (true) or no consent (false)
             */
            getStatus: () => {
                return new Promise((resolve, reject) => {
                    /**
                     * Check Cookiehub consent status
                     * @param {*} intervalID 
                     * @returns {json} response data
                     */
                    const checkCookiehubStatus = (intervalID) => {
                        if (window.cookiehub.hasAnswered()) {
                            /* Check response value and return object as a promise */
                            let response = window.cookiehub.hasConsented("analytics")
                                ?
                                {
                                    'interval_id': intervalID,
                                    'consent': true,
                                    'cookieless': false
                                }
                                :
                                {
                                    'interval_id': intervalID,
                                    'consent': CONSENT_TRACKING == 'hybrid' ? true : false,
                                    'cookieless': CONSENT_TRACKING == 'hybrid' ? true : false
                                };

                            return response;
                        }

                        /* If no response is provided, return false */
                        return false
                    }

                    /**
                     * Check Cookiebot consent status
                     * @param {*} intervalID 
                     * @returns {json} response data
                     */
                    const checkCookiebotStatus = (intervalID) => {
                        if (window.Cookiebot.hasResponse) {
                            /* Check response value and return object as a promise */
                            let response = window.Cookiebot.consented || (window.Cookiebot.consent.marketing && window.Cookiebot.consent.statistics)
                                ?
                                {
                                    'interval_id': intervalID,
                                    'consent': true,
                                    'cookieless': false
                                }
                                :
                                {
                                    'interval_id': intervalID,
                                    'consent': CONSENT_TRACKING == 'hybrid' ? true : false,
                                    'cookieless': CONSENT_TRACKING == 'hybrid' ? true : false
                                };

                            return response;
                        }

                        /* If no response is provided, return false */
                        return false
                    }

                    /**
                     * Consent tracking disabled
                     */
                    if (CONSENT_TRACKING == 'disable') {
                        resolve({ 'consent': true, 'cookieless': false });
                    }

                    /**
                     * Consent tracking in normal and hybrid state
                     */
                    if (CONSENT_TRACKING == 'normal' || CONSENT_TRACKING == 'hybrid') {
                        /* Set timer to check consent response with interval */
                        const intervalID = setInterval(() => {
                            /**
                             * Cookiehub
                             */
                            if (CONSENT_PROVIDER == 'cookiehub') {
                                /* Check if user has answered the consent prompt */
                                try {
                                    /** 
                                     * Check Cookiehub consent status and return consent data 
                                     * If user has not answered yet, return nothing
                                     */
                                    let response = checkCookiehubStatus(intervalID);
                                    if (response != false) resolve(response);
                                } catch (err) {
                                    /* In case of error, clear interval */
                                    clearInterval(intervalID);
                                    reject(err);
                                }
                            }
                            /**
                            * Cookiebot
                            */
                            if (CONSENT_PROVIDER == 'cookiebot') {
                                try {
                                    /** 
                                     * Check Cookiebot consent status and return consent data 
                                     * If user has not answered yet, return nothing
                                     */
                                    let response = checkCookiebotStatus(intervalID);
                                    if (response != false) resolve(response);
                                } catch (err) {
                                    /* In case of error, clear interval */
                                    clearInterval(intervalID);
                                    reject(err);
                                }
                            }
                        }, 2000);
                    }
                });
            }
        }

        /**
         * Tag Manager
         */
        window.tagmanager = {
            /**
             * Tagmanager initial load
             * Original idea from Mattias Siø Fjellvang
             * Link: https://constantsolutions.dk/2020/06/delay-loading-of-google-analytics-google-tag-manager-script-for-better-pagespeed-score-and-initial-load/
             * @param {string} id 
             * @param {string} url 
             * @param {string} timeout 
             */
            loadTagManager: (id, url, timeout) => {
                /** 
                 * Init GTM
                 */
                const initGTM = () => {
                    /* Make sure that script only loads once */
                    if (window.gtmDidInit) return false;
                    window.gtmDidInit = true;

                    /* Create script tag */
                    let script = document.createElement("script");
                    script.type = 'text/javascript';
                    script.id = 'tagmanager';
                    script.async = true;
                    script.src = `${url}/gtm.js?id=${id}`;

                    script.addEventListener('load', () => {
                        dataLayer.push({ event: 'gtm.js', 'gtm.start': new Date().getTime(), 'gtm.uniqueEventId': 0 });
                    });

                    document.head.appendChild(script);
                }

                /**
                 * Init GTM OnEvent
                 */
                const initGTMOnEvent = (event) => {
                    initGTM();
                    event.currentTarget.removeEventListener(event.type, initGTMOnEvent);
                }

                /**
                 * Promise
                 */
                return new Promise(resolve => {
                    /* Init GTM */
                    setTimeout(initGTM(), timeout);

                    /* Add event listeners */
                    document.addEventListener('scroll', initGTMOnEvent);
                    document.addEventListener('mousemove', initGTMOnEvent);
                    document.addEventListener('touchstart', initGTMOnEvent);

                    if (window.gtmDidInit) {
                        resolve("Tag Manager loaded")
                    }
                });
            },
        }
        /**
         * Serverside
         */
        window.serverside = {
            endpoint: '/wp-json/ksd-server-side-analytics/v1',
            /**
             * Send request to rest-api
             * @param {json} eventData 
             */
            request: async (eventData) => {
                const sessionData = () => {
                    const getRandomInt = (min, max) => {
                        min = Math.ceil(min);
                        max = Math.floor(max);
                        return Date.now() + Math.floor(Math.random() * (max - min) + min);
                    }

                    const getSessionCount = async () => {
                        /* Check for user consent */
                        const response = await window.consent.getStatus();

                        if (response['consent']) {
                            /* If first session, return 1 */
                            if (!localStorage.getItem('session_count')) {
                                localStorage.setItem('session_count', 1);
                                return 1
                            }

                            /* Check that no session id is present */
                            if (!sessionStorage.getItem('session_id')) {
                                /* If true add +1 to current count */
                                let sct = parseInt(localStorage.getItem('session_count')) + 1;
                                localStorage.setItem('session_count', sct);
                                return sct;
                            }

                        }

                        return 1
                    }

                    const getSessionEngagement = () => {
                        return sessionStorage.getItem('session_id') ? 1 : 0
                    }

                    /* Get session count and engagement */
                    let sct = getSessionCount();
                    let sessionData = {
                        'seg': getSessionEngagement(),  // Session Engagement
                        'sct': sct,                     // Session Count
                        '_s': sct                       // Session Hit Count
                    }

                    /* Set session ID  */
                    if (!sessionStorage.getItem('session_id')) {
                        sessionStorage.setItem('session_id', getRandomInt(100000000, 999999999).toString());
                    }

                    /* Return session data */
                    return {
                        ...sessionData,
                        'sid': sessionStorage.getItem('session_id') // Session ID
                    }
                }


                /* Send request to the server */
                try {
                    /* Get consent status */
                    const response = await window.consent.getStatus();

                    /* Default browser event data */
                    let browserData = {
                        'v': 2,
                        'ul': navigator.language,
                        'sr': `${window.screen.width}x${window.screen.height}`,
                        'dl': document.URL,
                        'dt': document.title,
                        'dh': document.domain,
                        'ds': 'web',
                        'cookieless': response['cookieless'],
                        'consent': response['consent'],
                    };

                    /* Request to server-side processing */
                    if (response['consent']) {
                        fetch(`${window.serverside.endpoint}/track`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                ...browserData,
                                ...sessionData(),
                                ...eventData
                            })
                        })
                            .then((response) => {
                                if (!response.ok) {
                                    throw Error(response.statusText);
                                }
                            })
                            .catch((error) => {
                                console.error(error);
                            });
                    }

                    /* Clear setInterval */
                    if ('interval_id' in response) clearInterval(response['interval_id']);

                } catch (err) {
                    console.error(err);
                }
            }
        }

        /** 
         * Check that globals were created
         */
        if (
            (typeof window.serverside != "undefined" && typeof window.consent != "undefined") ||
            (window.serverside != "undefined" && window.consent != "undefined")
        ) {
            resolve();
        } else {
            reject(console.error("Globals not declared"));
        }

    });
}


/**
 * Add event handlers
 */
const addEventHandler = () => {
    /* Pageview */
    window.serverside.request({ en: 'page_view' });

    /* Button Clicks */
    const buttons = document.querySelectorAll("a[data-track='1']");
    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            window.serverside.request({
                'en': 'button_click',
                'button_name': button.dataset.identifier
            });
        });
    });
}


/** 
 * Load analytics
 */
(async () => {
    try {
        /* Declare global variables */
        await initGlobals();

        /* Add consent management systems */
        if (CONSENT_TRACKING == 'normal' || CONSENT_TRACKING == 'hybrid') {
            CONSENT_PROVIDER == 'cookiehub' && await window.consent.loadCookiehub(CONSENT_ID);
            CONSENT_PROVIDER == 'cookiebot' && await window.consent.loadCookiebot(CONSENT_ID, 'auto');
        }

        /* Load Tag Manager */
        if (TAGMANAGER_CONTAINER == '1') {
            await window.tagmanager.loadTagManager(TAGMANAGER_ID, TAGMANAGER_URL, TAGMANAGER_TIMEOUT);
        }

        /* Add event handlers for sending requests */
        addEventHandler();

    } catch (err) {
        console.error(err);
    }
})();
