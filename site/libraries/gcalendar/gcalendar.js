function gcEncode(str) {
    return str.replace(/&amp;/g, '&');
}
function endsWith(str, suffix) {
	return str.indexOf(suffix, str.length - suffix.length) !== -1;
}