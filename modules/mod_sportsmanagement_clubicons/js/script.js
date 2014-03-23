/**
* Module mod_jl_clubicons For Joomla 1.5 and joomleague 1.5b.2
* Version: 1.5b.2
* Created by: johncage
* Created on: 21 June 2011
* 
* URL: www.yourlife.de
* License http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/
var observeClubIcons = function(moduleid, imgclass, itemcnt, wdiff, rowseperator)
{
  var rowsep = rowseperator;
  //alert (rowsep);
  var imgholder = (rowseperator == 'tr') ? 'td' : 'span';
  var holderid = 'clubicons'+moduleid;
  $$('#'+holderid+' '+rowsep).each(function(row){
    row.setStyle('opacity', .2);
  });
  $$('#'+holderid+' '+rowsep).each(function(row){
    new Asset.images(row.getElements(imgclass), {
      onComplete: function(){
          row.setStyle('opacity', 1);
      }
    });
  });
  var holderpartwidth = ($(holderid).getSize().x / itemcnt).toInt();
  var maxheight;
  $$('#'+holderid+' '+rowsep).each(function(row){
    maxheight = 0;
    var maxwidth = (jcclubiconsglobalmaxwidth > 0 && (holderpartwidth > jcclubiconsglobalmaxwidth)) ? jcclubiconsglobalmaxwidth : holderpartwidth;
    var minwidth = maxwidth - wdiff;
    row.getElements(imgclass).each(function(el)
    { 
      if (!el.hasClass('nolink')) el.setStyle('cursor', 'pointer');
      el.setStyle('width', maxwidth);
      var h = el.getDimensions().height.toInt();
      //alert(h);
      if (h > maxheight) maxheight = h;
      
      el.setStyle('width', minwidth);
      el.addEvents({
    		'mouseenter': function(){
    			this.set('tween', {
    				duration: 300,
    				transition: Fx.Transitions.Sine.easeOut 
    			}).tween('width', maxwidth);
    			
    		},
    		'mouseleave': function(){
    			this.set('tween', {}).tween('width', minwidth);
    		}
  	  });
  	  el.getParent().setStyles({'height': maxheight+2} );
    });
    //alert(maxheight);
    row.setStyles({'height': maxheight+2} );
    row.getElements(imgholder).each(function(mytd){ mytd.setStyles({'height': maxheight+2} )}.bind(maxheight));
    //if (imgholder == 'span') row.getElements(imgclass).each(function(myimg){myimg.setStyles({'position':'relative', 'top': '50%', 'margin-top': '-'+(myimg.getStyle('height').toInt()/2).toInt()});});
  });
}