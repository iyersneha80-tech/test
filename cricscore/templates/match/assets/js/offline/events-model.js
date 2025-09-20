
(function(){
  function uuid(){
    // RFC4122-ish v4
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c=>{
      const r = Math.random()*16|0, v = c === 'x' ? r : (r&0x3|0x8);
      return v.toString(16);
    });
  }
  window.CricModel = {
    makeBallEvent(base){
      return Object.assign({
        event_id: uuid(),
        type: 'BALL',
        timestamp_client: Date.now(),
        author_device_id: (window.CricScoreCfg && CricScoreCfg.deviceId) || 'dev',
      }, base);
    }
  };
})();
