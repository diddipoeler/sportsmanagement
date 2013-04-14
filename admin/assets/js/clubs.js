function searchClub(val, key) {
	var f = $('adminForm');
	if (f) {
		f.elements['search'].value = val;
		f.elements['search_mode'].value = 'matchfirst';
		f.submit();
	}
}