/**
 * provides autocomplete code for quickadd team
 */

Autocompleter.Request.JsonQuickAddTeam = new Class({

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
				var choice = new Element('li', {'teamid': token.id, 'html': this.markQueryValue(token.name)});
				choice.inputValue = token.name;
				this.addChoiceEvents(choice).inject(this.choices);
			}, this);
			this.showChoices();
		}
	}, 	

	choiceSelect: function(choice) {
		if (choice) this.choiceOver(choice);
		$('cteamid').value = choice.getProperty('teamid');
		this.setSelection(true);
		this.queryValue = false;
		this.hideChoices();
	}

});

window.addEvent('domready', function () {
	
	new Autocompleter.Request.JsonQuickAddTeam('quickadd', 
			quickaddsearchurl, {
        'postVar': 'query'
    });

});