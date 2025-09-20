
(function(){
  const DB_NAME = 'cricscore_offline';
  const DB_VERSION = 1;
  const STORE_OUTBOX = 'outbox';
  const STORE_SETTINGS = 'settings';
  const STORE_CACHE = 'events_cache';
  const STORE_SNAP = 'snapshots';

  function openDB(){
    return new Promise((resolve, reject)=>{
      const req = indexedDB.open(DB_NAME, DB_VERSION);
      req.onupgradeneeded = (e)=>{
        const db = req.result;
        if(!db.objectStoreNames.contains(STORE_OUTBOX)){
          db.createObjectStore(STORE_OUTBOX, { keyPath: 'id', autoIncrement: true });
        }
        if(!db.objectStoreNames.contains(STORE_SETTINGS)){
          db.createObjectStore(STORE_SETTINGS, { keyPath: 'key' });
        }
        if(!db.objectStoreNames.contains(STORE_CACHE)){
          const s = db.createObjectStore(STORE_CACHE, { keyPath: ['match_id','server_seq'] });
          s.createIndex('by_match', ['match_id','server_seq']);
        }
        if(!db.objectStoreNames.contains(STORE_SNAP)){
          db.createObjectStore(STORE_SNAP, { keyPath: ['match_id','innings_no'] });
        }
      };
      req.onsuccess = ()=> resolve(req.result);
      req.onerror = ()=> reject(req.error);
    });
  }

  async function tx(store, mode, fn){
    const db = await openDB();
    return new Promise((resolve, reject)=>{
      const t = db.transaction(store, mode);
      const s = t.objectStore(store);
      const res = fn(s);
      t.oncomplete = ()=> resolve(res);
      t.onerror = ()=> reject(t.error);
    });
  }

  window.CricDB = {
    async addOutbox(event){ return tx(STORE_OUTBOX, 'readwrite', s=> s.add(event)); },
    async getOutboxBatch(limit=50){ 
      return tx(STORE_OUTBOX, 'readonly', s=> new Promise(res=>{
        const out = []; const req = s.openCursor();
        req.onsuccess = e=>{ const c = e.target.result;
          if(!c || out.length>=limit){ res(out); return; }
          out.push({id:c.key, value:c.value}); c.continue();
        };
      }));
    },
    async clearOutbox(ids){ return tx(STORE_OUTBOX, 'readwrite', s=> ids.forEach(id=> s.delete(id))); },
    async setSetting(key, value){ return tx(STORE_SETTINGS, 'readwrite', s=> s.put({key, value})); },
    async getSetting(key){ return tx(STORE_SETTINGS, 'readonly', s=> new Promise(r=>{ const g=s.get(key); g.onsuccess=()=>r(g.result?g.result.value:null);})); },
  };
})();
