document.addEventListener('DOMContentLoaded', () => {
  console.log('✅ Local main.js loaded');
  const buttons = document.querySelectorAll('.recast-play-button');

  const searchParams = new URLSearchParams(window.location.search);
  const purchaseSuccess = searchParams.get('success') === 'true';
  const contentToken = searchParams.get('content_access_token');
  const intent = searchParams.get('intent');

  console.log('🔁 success:', purchaseSuccess);
  console.log('🪪 content_access_token:', contentToken);
  console.log('🧠 intent:', intent);

  // ✅ Autoplay logic (only checks for success now)
  if (purchaseSuccess) {
    const iframeId = document.querySelector('.recast-play-button')?.dataset.iframeId;
    if (iframeId) {
      const overlayId = `recast-overlay-${iframeId.replace('vimeo-player-', '')}`;
      const overlay = document.getElementById(overlayId);
      if (overlay) overlay.style.display = 'none';

      const iframe = document.getElementById(iframeId);
      if (iframe && window.Vimeo) {
        const player = new Vimeo.Player(iframe);
        player.play().catch(err => {
          console.warn('⚠️ Autoplay failed:', err);
        });
      }
    }
  }

  // 🖱️ Handle click
  buttons.forEach((btn) => {
    btn.addEventListener('click', async () => {
      const productId = btn.dataset.productId;
      const iframeId = btn.dataset.iframeId;

      if (!productId || !iframeId) {
        alert('Missing product or iframe ID');
        return;
      }

      try {
        const res = await fetch('/wp-json/recast/v1/intent', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            product_id: productId,
            user_id: 'anonymous-user'
          })
        });

        const data = await res.json();
        const token = typeof data.intent_token === 'string'
          ? data.intent_token
          : (data.intent_token?.token || Object.values(data.intent_token)[0]);

        console.log('📨 Final token:', token);

        if (!token) return alert('Invalid token from API');

        // 🌐 Redirect to Recast
        const redirectUrl = new URL('https://account.recast-sandbox.tv/redirect');
        redirectUrl.searchParams.set('mode', 'redirect');
        redirectUrl.searchParams.set('intent', token);
        redirectUrl.searchParams.set('sandbox', 'true');

        const baseUrl = window.location.origin + window.location.pathname;
        redirectUrl.searchParams.set('redirect_uri', baseUrl);

        window.location.href = redirectUrl.toString();
      } catch (err) {
        console.error('❌ Error fetching intent token:', err);
        alert('Something went wrong.');
      }
    });
  });
});