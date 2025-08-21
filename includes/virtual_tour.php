<!-- includes/virtual_tour.php -->
<style>
  #vt-overlay{position:fixed;inset:0;background:rgba(0,0,0,.85);display:none;z-index:1000}
  #vt-box{position:absolute;inset:6% 10%;background:#000;border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.4)}
  #vt-header{display:flex;align-items:center;justify-content:space-between;background:#003580;color:#fff;padding:10px 14px}
  #vt-close{border:0;background:#febb02;color:#003580;font-weight:600;padding:8px 12px;border-radius:8px;cursor:pointer}
  #vt-stage{width:100%;height:calc(100% - 48px)}
  @media(max-width:768px){#vt-box{inset:6% 4%}}
</style>

<script src="https://aframe.io/releases/1.5.0/aframe.min.js"></script>

<div id="vt-overlay" role="dialog" aria-modal="true" aria-label="Virtual Room Tour">
  <div id="vt-box">
    <div id="vt-header">
      <div>Virtual Room Tour</div>
      <button id="vt-close">Close</button>
    </div>
    <div id="vt-stage">
      <!-- Exactly your Option 2 scene, just embedded in a modal -->
      <a-scene embedded background="color: #000">
        <a-sky id="vt-sky" rotation="0 -90 0"></a-sky>
        <a-entity camera look-controls wasd-controls position="0 1.6 0"></a-entity>
      </a-scene>
    </div>
  </div>
</div>

<script>
(function(){
  const overlay = document.getElementById('vt-overlay');
  const closeBtn = document.getElementById('vt-close');
  const sky = document.getElementById('vt-sky');

  // Open using a direct texture set (same behavior as your working test)
  window.openVirtualTour = function(url, opts = {}){
    const yaw = typeof opts.rotationY === 'number' ? opts.rotationY : -90;
    overlay.style.display = 'block';
    sky.setAttribute('rotation', `0 ${yaw} 0`);
    sky.setAttribute('src', url); // direct set avoids black screen
  };

  function closeTour(){
    overlay.style.display = 'none';
    sky.removeAttribute('src'); // optional: free memory
  }
  closeBtn.addEventListener('click', closeTour);
  overlay.addEventListener('click', (e)=>{ if (e.target === overlay) closeTour(); });
})();
</script>
