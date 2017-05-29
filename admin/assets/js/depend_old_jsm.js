// add update of field when fields it depends on change.
window.addEvent('domready', function() {

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
	});

	$$('.depend').each(function(element) {
		// get value of attribute "depends", can be multiple
		var depends = element.getProperty('depends');
		// create array
		var dependsArray = depends.split(',');
		// gets the active element
		var myelement = element;

		// gets the prefix of the current element
		var prefix = getElementIdPrefix(element);

		// Attach update_depend to the change event of all elements it depends upon,
		// so that when (one of) the dependencies change, the element is refreshed.
		dependsArray.each(function(el) {

			// incoming: string, without prefix so let's attach the prefix
			var combined = '#'+String(prefix)+String(el);
			var newid = document.id(combined);

			jQuery(combined).change(function() {
				update_depend(myelement);
			});
		});

		// Refresh the element also after the page is loaded (to fill the element)
		load_default(myelement);

	});
});


// load default values
function load_default(element) {

	// the element that will be changed upon change of depend
	var combo = element;
	// prefix
	var prefix = getElementIdPrefix(element);
	// do we have a required attributed?
	var required = element.getProperty('required') || 'false';

	if (required == 'true') {
		var required = "&required=true";
	}
	if (required == 'false') {
		var required = "&required=false";
	}

	var selectedItems = combo.getProperty('current').split('|');
	var depends = combo.getProperty('depends').split(',');
	var dependquery = '';
	depends.each(function(str) {
		dependquery += '&' + str + '=' + $(prefix + str).value;
	});

	var loaddefault = 1;
	var task = combo.getProperty('task');
	var postStr = '';
	var url = 'index.php?option=com_sportsmanagement&format=json&task=ajax.' + task
		+ required + dependquery;
    
    alert(url);
    
	var theAjax = new Request.JSON({
		url : url,
		method : 'post',
		postBody : postStr,
		onSuccess : function(response) {
			/* var JSON_output = JSON.stringify(response); */

			// options is equal to the response
			var options = response;

      alert(url);
      alert(response);
      
      //console.log("Ajax Link: " + url);
      
			var headingLine = null;

			// @todo: check!
			if (combo.getProperty('isrequired') == 0) {
				// In case the element is not mandatory, then first option is 'select': keep it
				// Remark : the old solution options.unshift(combo.options[0]); does not work properly
				//          It seems to result in problems in the mootools library.
				//          Therefore a different approach is taken.
				headingLine = {value: combo.options[0].value, text: combo.options[0].text};
			}
			combo.empty();

			// adding first option
			if (headingLine != null) {
				new Element('option', headingLine).injec(combo,'inside');
			}

			options.each(function(el) {
				/*
				 if (typeof el == "undefined") return;
				 if (selectedItems != null && selectedItems.indexOf(el.value) != -1) {
				 el.selected = "selected";
				 }
				 */
         alert(el);
				new Element('option', {'value': el.value, 'text':el.text}).inject(combo,'inside');
			});

			jQuery(combo).val(selectedItems);
			jQuery(combo).trigger("chosen:updated");
			jQuery(combo).trigger("liszt:updated");

		}
	});

	theAjax.post();
}





// update dependant element function
function update_depend(element) {

	// the element that will be changed upon change of depend
	var combo = element;
	// prefix
	var prefix = getElementIdPrefix(element);
	// do we have a required attributed?
	var required = element.getProperty('required') || 'false';

	if (required == 'true') {
		var required = "&required=true";
	}
	if (required == 'false') {
		var required = "&required=false";
	}

	var selectedItems = combo.getProperty('current').split('|');
	var depends = combo.getProperty('depends').split(',');
	var dependquery = '';
	depends.each(function(str) {
		dependquery += '&' + str + '=' + $(prefix + str).value;
	});

	var task = combo.getProperty('task');
	var postStr = '';
	var url = 'index.php?option=com_sportsmanagement&format=json&task=ajax.' + task
		+ required + dependquery;
	var theAjax = new Request.JSON({
		url : url,
		method : 'post',
		postBody : postStr,
		onSuccess : function(response) {
			/* var JSON_output = JSON.stringify(response); */

			// options is equal to the response
			var options = response;
     // alert(response);

			var headingLine = null;

			// @todo: check!
			if (combo.getProperty('isrequired') == 0) {
				// In case the element is not mandatory, then first option is 'select': keep it
				// Remark : the old solution options.unshift(combo.options[0]); does not work properly
				//          It seems to result in problems in the mootools library.
				//          Therefore a different approach is taken.
				headingLine = {value: combo.options[0].value, text: combo.options[0].text};
			}
			combo.empty();

			// adding first option
			if (headingLine != null) {
				new Element('option', headingLine).injec(combo,'inside');
			}

			options.each(function(el) {
				/*
				 if (typeof el == "undefined") return;
				 if (selectedItems != null && selectedItems.indexOf(el.value) != -1) {
				 el.selected = "selected";
				 }
				 */
				new Element('option', {'value': el.value, 'text':el.text}).inject(combo,'inside');
			});

			jQuery(combo).trigger("chosen:updated");
			jQuery(combo).trigger("liszt:updated");
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
