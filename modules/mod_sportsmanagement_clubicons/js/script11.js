/**
* Module mod_jl_clubicons For Joomla 1.5 and joomleague 1.5b.2
* Version: 1.5b.2
* Created by: johncage
* Created on: 21 June 2011
* 
* URL: www.yourlife.de
* License http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/
function observeClubIcons(moduleid, imgclass, itemcnt, wdiff)
  {
    var holderid = 'clubicons'+moduleid;
    $(holderid).setStyle('visibility', 'hidden');
    new Asset.images($$('#'+holderid+' '+imgclass), {
      onComplete: function(){
          $(holderid).setStyle('visibility', 'visible');
      }
    });
    var maxheight = 0;
    var holderpartwidth = ($(holderid).getSize().size.x / itemcnt).toInt();
    $$('#'+holderid+' '+imgclass).each(function(el, index){ 
      var maxwidth = ((jcclubiconsglobalmaxwidth > 0 && (holderpartwidth > jcclubiconsglobalmaxwidth)) ? jcclubiconsglobalmaxwidth : holderpartwidth;
      var minwidth = maxwidth - wdiff;
      if (!el.hasClass('nolink')) el.setStyle('cursor', 'pointer');
      el.setStyle('width', maxwidth);
      var h = el.getStyle('height').toInt();
      
      if (h > maxheight) maxheight = h;
      el.setStyle('width', minwidth);
   		var fx = new Fx.Styles(el, {duration:400, wait:false});
 
    	el.addEvent('mouseenter', function(){
    		fx.start({
    			'width': maxwidth,
    		});
    	});
     
    	el.addEvent('mouseleave', function(){
    		fx.start({
    			'width': minwidth,
    			});
    	});
  	  
    });
    $$('#'+holderid+' tr').each(function(el){ el.setStyle('height', maxheight+"px"); });
  }