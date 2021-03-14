jQuery(document).ready(function($){
	$('stid').addEvent('change', function(){
		var form = $('adminForm1');
		form.submit();
	});
	if ($('seasonnav')) {
		$('seasonnav').addEvent('change', function(){
			var form = $('adminForm1');
			if (this.value != 0) {
				$('jl_short_act').value = 'seasons';
			}
			form.submit();
		});
	}

	if($('pid')!=null){
		$('pid').addEvent('change', function(){
			var form = $('adminForm1');
			if (this.value != 0) {
				$('jl_short_act').value = 'projects';
			}
			form.submit();
		});
	}

	if($('tid')!=null){
		$('tid').addEvent('change', function(){
			var form = $('adminForm1');
			if (this.value != 0) {
				$('jl_short_act').value = 'teams';
			}
		form.submit();
	});}
	
	if($('rid')!=null){
		$('rid').addEvent('change', function(){
		var form = $('adminForm1');
		if (this.value != 0) {
			$('jl_short_act').value = 'rounds';
		}
		form.submit();
	});}
});