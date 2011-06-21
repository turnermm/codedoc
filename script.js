function codedoc_toggle(id) {
   var dom  = document.getElementById(id);
   var show_dom  = document.getElementById('s_'+ id);
   if(dom.style.display && dom.style.display == 'block') {
      dom.style.display='none';   
      show_dom.innerHTML="show";     
   }
   else {
	dom.style.display='block';  
    show_dom.innerHTML="hide"; 
   }
}
