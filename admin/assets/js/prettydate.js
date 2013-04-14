/*
---
description: This plugin provides a way to format JavaScript dates in the style of Twitter's timeline: "just now", "about 8 minutes ago","about 11 hours ago","yesterday". The method is, originally, written by John Resig.

authors:
- Adrian Statescu (http://thinkphp.ro)

license:
- MIT-style license

requires:
 core/1.3.1: '*'

provides: [PrettyDate]
...
*/

var PrettyDate = new Class({

	/* Implements */
	Implements: [Options],

	/* constructor of class - initialize */
	initialize: function(elems,options) {

		  this.setOptions(options);
 
		  if($type(elems) == 'array') {

			 elems.each(function(elem) {
				   var pretty = this.format(elem.get('title'));
					   if(pretty) {
						  elem.set('text',pretty);
					   }
			 }, this);

		  } else {
				   var pretty = this.format(elems.get('title'));
					   if(pretty) {
						  elems.set('text',pretty);
					   }
		  }
	},
       
	/* the method written by John Resig, improving a bit */
	format: function(time){

		  var date = new Date((time || "").replace(/-/g,"/").replace(/[TZ]/g," ")),
		  diff = ((((this.now()).getTime() - date.getTime()) / 1000) + ((this.now()).getTimezoneOffset() * 60)),
		  day_diff = Math.floor(diff / 86400);

		  if(isNaN(day_diff) || day_diff < 0 || day_diff >= 31) {
			 return;
		  }

		  return day_diff == 0 && (
					 diff < 60 && this.options.now ||
					 diff < 120 && this.options.minute.replace("%s",Math.floor(diff / 60)) ||
					 diff < 3600  &&  this.options.minutes.replace("%s",Math.floor( diff / 60)) ||
					 diff < 7200  &&  this.options.hour.replace("%s",Math.floor(diff / 3600)) ||
					 diff < 86400 &&  this.options.hours.replace("%s",Math.floor(diff / 3600))
					 ) ||
					 day_diff == 1 && this.options.yesterday.replace("%s",day_diff) ||
						  day_diff < 7 && this.options.days.replace("%s",day_diff) ||
								 day_diff < 31  && this.options.weeks.replace("%s",Math.ceil( day_diff / 7 ));
	 },

	 /* get the current date */
	 now: function() {
		  return new Date();
	 }
     
});

Element.implement({
		prettyDate: function(options) {
			  new PrettyDate(this,options);
		  return this;
		}
});
