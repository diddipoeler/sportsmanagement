/**
 * provides autocomplete code for quickadd, for mootools 1.1
 */

Autocompleter.Request.JsonQuickAddPerson = new Class({

	Extends: Autocompleter.Request.JSON,

	queryResponse: function(response) {
		this.parent();
		if (!response || !response.totalCount) return;
		this.update(response.rows);
	},

	update: function(tokens) {
		this.choices.empty();
		this.cached = tokens;
		var type = tokens && $type(tokens);
		if (!type || (type == 'array' && !tokens.length) || (type == 'hash' && !tokens.getLength())) {			
			(this.options.emptyChoices || this.hideChoices).call(this);
		} else {
			if (this.options.maxChoices < tokens.length && !this.options.overflow) tokens.length = this.options.maxChoices;
			tokens.each(this.options.injectChoice || function(token){
				var choice = new Element('li', {'personid': token.id, 'html': this.markQueryValue(token.name)});
				choice.inputValue = token.name;
				this.addChoiceEvents(choice).inject(this.choices);
			}, this);
			this.showChoices();
		}
	}, 	

	choiceSelect: function(choice) {
		if (choice) this.choiceOver(choice);
		$('cpersonid').value = choice.getProperty('personid');
		this.setSelection(true);
		this.queryValue = false;
		this.hideChoices();
	}

});

window.addEvent('domready', function () {
	
	new Autocompleter.Request.JsonQuickAddPerson('quickadd', 
			quickaddsearchurl, {
        'postVar': 'query'
    });

});