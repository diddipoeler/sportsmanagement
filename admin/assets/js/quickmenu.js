/**
 * @copyright Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
 * @license GNU/GPL, see LICENSE.php Joomla! is free software. This version may
 *          have been modified pursuant to the GNU General Public License, and
 *          as distributed it includes or is derivative of works licensed under
 *          the GNU General Public License or other free or open source software
 *          licenses. See COPYRIGHT.php for copyright notices and details.
 */
window.addEvent('domready', function() {
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