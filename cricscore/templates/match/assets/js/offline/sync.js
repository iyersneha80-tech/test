
(function(){
  const cfg = window.CricScoreCfg || { apiRoot:'', matchId:'', nonce:'' };
  let lastServerSeq = 0;
  let scorerToken = null;
  let syncing = false;

  async function ensureToken(){
    if (scorerToken) return scorerToken;
    try {
      const url = cfg.apiRoot + '/matches/' + encodeURIComponent(cfg.matchId) + '/scorer-token';
      const resp = await fetch(url, { method:'POST', headers:{ 'X-WP-Nonce': cfg.nonce } });
      if(!resp.ok){ console.warn('Issue token failed'); return null; }
      const data = await resp.json();
      scorerToken = data.token;
      return scorerToken;
    } catch(e){ console.warn('Token error', e); return null; }
  }

  async function flushOutbox(){
    if (syncing) return;
    syncing = true;
    try {
      const batch = await CricDB.getOutboxBatch(50);
      if (batch.length){
        const events = batch.map(x=>x.value);
        const token = await ensureToken();
        const url = cfg.apiRoot + '/matches/' + encodeURIComponent(cfg.matchId) + '/events';
        const resp = await fetch(url, {
          method:'POST',
          headers:{ 'Content-Type':'application/json' },
          body: JSON.stringify({ scorer_token: token, events })
        });
        if (resp.ok){
          const data = await resp.json();
          lastServerSeq = data.last_server_seq || lastServerSeq;
          await CricDB.clearOutbox(batch.map(x=>x.id));
        }
      }
      // Pull
      const pull = await fetch(cfg.apiRoot + '/matches/' + encodeURIComponent(cfg.matchId) + '/events?since=' + lastServerSeq);
      if (pull.ok){
        const p = await pull.json();
        lastServerSeq = p.last_server_seq || lastServerSeq;
      }
      window.dispatchEvent(new CustomEvent('cr:sync-state', { detail:{ syncing:false, unsynced:0, lastServerSeq } }));
    } catch(e){
      console.warn('Sync error', e);
      window.dispatchEvent(new CustomEvent('cr:sync-state', { detail:{ syncing:false } }));
    } finally {
      syncing = false;
    }
  }

  function schedule(){
    flushOutbox();
    setTimeout(schedule, 8000);
  }

  window.addEventListener('online', flushOutbox);
  document.addEventListener('DOMContentLoaded', schedule);

  // Public minimal API
  window.CricSync = {
    async recordBall(evt){
      await CricDB.addOutbox(evt);
      window.dispatchEvent(new CustomEvent('cr:sync-state', { detail:{ unsynced: (evt && 1) } }));
      if (navigator.onLine) flushOutbox();
    }
  };
})();
