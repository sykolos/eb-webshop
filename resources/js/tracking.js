const CONSENT_COOKIE = 'eb_analytics_consent';
const endpoint = '/tracking/events';

const cookieValue = (name) => document.cookie.split('; ').find((part) => part.startsWith(`${name}=`))?.split('=')[1] || null;
const setConsent = (value) => {
    document.cookie = `${CONSENT_COOKIE}=${value}; Max-Age=${60 * 60 * 24 * 180}; Path=/; SameSite=Lax${location.protocol === 'https:' ? '; Secure' : ''}`;
    document.querySelector('#eb-consent')?.setAttribute('hidden', 'hidden');
};

const sendEvent = (eventName, payload = {}) => {
    if (cookieValue(CONSENT_COOKIE) !== 'accepted' || !window.crypto?.randomUUID) return;
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!token) return;
    const url = new URL(location.href);
    const body = {
        event_uuid: crypto.randomUUID(), event_name: eventName, page_path: location.pathname,
        referrer: document.referrer || null, campaign_source: url.searchParams.get('utm_source'),
        campaign_medium: url.searchParams.get('utm_medium'), campaign_name: url.searchParams.get('utm_campaign'),
        device_type: matchMedia('(max-width: 600px)').matches ? 'mobile' : (matchMedia('(max-width: 1000px)').matches ? 'tablet' : 'desktop'),
        payload,
    };
    fetch(endpoint, { method: 'POST', credentials: 'same-origin', keepalive: true, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-EB-Analytics-Consent': 'accepted', Accept: 'application/json' }, body: JSON.stringify(body) }).catch(() => {});
};

const initTracking = () => {
    if (location.pathname.startsWith('/adminpanel') || location.pathname.startsWith('/login')) return;
    const consent = document.querySelector('#eb-consent');
    if (consent && !cookieValue(CONSENT_COOKIE)) consent.removeAttribute('hidden');
    document.querySelectorAll('[data-consent]').forEach((button) => button.addEventListener('click', () => setConsent(button.dataset.consent)));
    document.querySelectorAll('[data-consent-settings]').forEach((button) => button.addEventListener('click', () => consent?.removeAttribute('hidden')));
    if (cookieValue(CONSENT_COOKIE) === 'accepted') sendEvent('page_view');

    document.addEventListener('click', (event) => {
        const target = event.target.closest('[data-track-event]');
        if (target) sendEvent(target.dataset.trackEvent, { product_id: target.dataset.trackProductId || undefined, source: target.dataset.trackSource || undefined });
    });
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        const action = form.getAttribute('action') || '';
        if (form.matches('[data-track-search], #global-search-form') || action.includes('search')) sendEvent('search', { query_present: Boolean(new FormData(form).get('q') || new FormData(form).get('search')) });
        if (form.matches('[data-track-filter]')) sendEvent('filter');
        if (action.includes('checkout')) sendEvent('checkout_step', { step: 'submit' });
        if (action.includes('registration')) sendEvent('registration_request');
    });
};

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initTracking);
else initTracking();

window.ElectroBusinessTracking = { sendEvent, setConsent };
