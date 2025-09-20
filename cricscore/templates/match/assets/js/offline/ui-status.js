
(function(){
  function el(tag, cls, text){ const e=document.createElement(tag); if(cls)e.className=cls; if(text)e.textContent=text; return e; }
  function fmtTime(d){ const pad=n=>(''+n).padStart(2,'0'); return pad(d.getHours())+':'+pad(d.getMinutes())+':'+pad(d.getSeconds()); }

  function mount(){
    if(document.querySelector('.cricscore-status')) return;
    const bar = el('div','cricscore-status' + (navigator.onLine?' online':''));
    const dot = el('span','dot','');
    const text = el('span','text','Offline');
    const meta = el('span','meta','Unsynced: 0 • Last sync: —');
    bar.append(dot, text, meta);
    document.body.appendChild(bar);

    function refresh(e){
      bar.classList.toggle('online', navigator.onLine);
      text.textContent = navigator.onLine ? 'Online' : 'Offline';
      const d = e && e.detail ? e.detail : {};
      const uns = (typeof d.unsynced==='number') ? d.unsynced : 0;
      const last = d.lastServerSeq ? ' • Last sync: ' + fmtTime(new Date()) : ' • Last sync: —';
      meta.textContent = 'Unsynced: ' + uns + last;
    }
    window.addEventListener('cr:sync-state', refresh);
    window.addEventListener('online', refresh);
    window.addEventListener('offline', refresh);
    refresh();
  }

  document.addEventListener('DOMContentLoaded', mount);
})();
