'use strict';
(function(){
  // simple UX tweaks
  document.addEventListener('input', function(e){
    if(e.target && e.target.matches('input, textarea')){
      e.target.classList.toggle('dirty', e.target.value.trim() !== '');
    }
  });
})();
