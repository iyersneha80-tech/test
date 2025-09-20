
(function(){
  function waitFor(fn, timeoutMs){
    const started = Date.now();
    (function tick(){
      if (fn()) return;
      if (Date.now() - started > timeoutMs) return;
      setTimeout(tick, 150);
    })();
  }

  function install(){
    if (!window.cricscore || !window.cricscore.steps || !window.cricscore.steps.scoring) return false;
    const mod = window.cricscore.steps.scoring;
    if (mod._offlineWrapped) return true;
    const orig = mod.processBall && mod.processBall.bind(mod);
    if (!orig) return false;

    mod.processBall = function(runs, options){
      try {
        const ms = window.cricscore.matchState || {};
        const inn = (ms.currentInnings || {});
        const payload = {
          match_id: (window.cricscore.api && window.cricscore.api.matchId) || (window.CricScoreCfg && CricScoreCfg.matchId) || '',
          innings_no: inn.number || 1,
          over_no: inn.completed_overs || 0,
          ball_no: (inn.balls_in_over || 0) + 1,
          runs: runs || 0,
          extras: (options && options.isExtra) ? { generic: true } : {},
        };
        if (window.CricModel && window.CricSync){
          window.CricSync.recordBall( window.CricModel.makeBallEvent(payload) );
        }
      } catch(e){ /* silent */ }
      return orig(runs, options);
    };
    mod._offlineWrapped = true;
    return true;
  }

  document.addEventListener('DOMContentLoaded', function(){
    waitFor(install, 8000);
  });
})();
