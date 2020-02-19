window.addEvent('domready', function(){
	$$('.eventstoggle').addEvent('click', function(){
		var id = this.getProperty('id').substr(7);
		if ($('info'+id).getStyle('display') == 'block') {
			$('info'+id).setStyle('display', 'none');
		}
		else {
			$('info'+id).setStyle('display', 'block');
		}
	});
});