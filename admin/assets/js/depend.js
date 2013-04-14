/**
 * @copyright	Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
 * @license	GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * javascript for dependant element xml parameter
 * 
 * 
 */

// add update of field when fields it depends on change.
window.addEvent('domready', function() {
	$$('.depend').each(function(element) {
		var depends = element.getProperty('depends');
		var myelement = element;
		var prefix = getElementIdPrefix(element);

		// Attach update_depend to the change event of all elements it depends upon,
		// so that when (one of) the dependencies change, the element is refreshed. 
		depends.split(',').each(function(el) {
			$(prefix + el).addEvent('change', function(event) {
				update_depend(myelement);
			});
		});

		// Refresh the element also after the page is loaded (to fill the element)
		update_depend(myelement);
	});

	$$('.mdepend').addEvent('click', function() {
		// rebuild hidden field list
		var sel = new Array();
		var i = 0;
		this.getElements('option').each(function(el) {
			if (el.getProperty('selected')) {
				sel[i++] = el.value;
			}
		});
		this.getParent().getElement('input').value = sel.join("|");
	}).fireEvent('click');
});

// update dependant element function
function update_depend(element) {
	var combo = element;
	var prefix = getElementIdPrefix(element);
	var required = element.getProperty('required') || 'false';
	required = (required=='true') ? "&required=true" : "&required=false" ;
	var value = combo.getProperty('current');
	var depends = combo.getProperty('depends').split(',');
	var dependquery = '';
	depends.each(function(str) {
		dependquery += '&' + str + '=' + $(prefix + str).value;
	});

	var task = combo.getProperty('task');
	var postStr = '';
	var url = 'index.php?option=com_joomleague&format=json&task=ajax.' + task 
			+ required + dependquery;
	var theAjax = new Request.JSON({
		url : url,
		method : 'post',
		postBody : postStr,
		onSuccess : function(response) {
			var options = response;
			var headingLine = null;
			if (combo.getProperty('isrequired') == 0) {
				// In case the element is not mandatory, then first option is 'select': keep it
				// Remark : the old solution options.unshift(combo.options[0]); does not work properly
				//          It seems to result in problems in the mootools library.
				//          Therefore a different approach is taken.
				headingLine = {value: combo.options[0].value, text: combo.options[0].text};
			}
			combo.empty();
			if (headingLine != null) {
				new Element('option', headingLine).injectInside(combo);
			}
			options.each(function(el) {
				if (typeof el == "undefined") return;
				if (value != null && value == el.value) {
					el.selected = "selected";
				}
				new Element('option', el).injectInside(combo);
			});
			combo.fireEvent('click');
		}
	});

	theAjax.post();
}

/** The element IDs can be either "jform_request_" (for menu items) or "jform_params_" (for modules)
 *  This function will check if we have to do with menu items or modules, and return the right
 *  prefix to be used for element-IDs */ 
function getElementIdPrefix(el) {
	var id = el.getProperty('id');
	var infix = id.replace(/^jform_(\w+)_.*$/, "$1");
	return infix.match("request") ? "jform_request_" : "jform_params_";
}