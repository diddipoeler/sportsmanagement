function saveorder(n, task) {
console.warn('window.saveorder() is deprecated without a replacement!');
console.warn('n ' + n);
console.warn('task ' + task);
checkAll_button( n, task );
}

function checkAll_button(n, task) {
console.warn('window.checkAll_button() is deprecated without a replacement!');
		task = task ? task : 'saveorder';
		var j, box;
		for ( j = 0; j <= n; j++ ) {
			box = document.adminForm[ 'cb' + j ];
			if ( box ) {
				box.checked = true;
			} else {
				alert( "You cannot change the order of items, as an item in the list is `Checked Out`" );
				return;
			}
		}
		Joomla.submitform( task );
}