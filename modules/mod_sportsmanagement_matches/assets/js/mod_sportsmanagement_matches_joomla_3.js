function jlmlnewAjax() {
	/* THIS CREATES THE AJAX OBJECT */
	var xmlhttp = false;
	try {
		// ajax object for non IE navigators
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try {
			// ajax object for IE
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (E) {
			xmlhttp = false;
		}
	}
	if (!xmlhttp && typeof XMLHttpRequest != "undefined") {
		xmlhttp = new XMLHttpRequest();
	}

	return xmlhttp;
}

function jlml_loadMatch(usedteam, matchid, moduleid, nr, origin) {
	var classadd = 'modJLML' + moduleid + '_row' + nr;
	var myFx = new Fx.Morph(classadd);
	myFx.start({'opacity': 0});
	jQuery(classadd).addClass('ajaxloading');
	var myFx = new Fx.Morph(classadd);
	myFx.start({'opacity': 1});
	var ajax = jlmlnewAjax();
	ajax.open("POST", location.href, true);
	ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	ajax.send('ajaxMListMod=1&ajaxmodid=' + moduleid + '&match_id=' + matchid + '&nr=' + nr + '&usedteam=' + usedteam + '&origin=' + origin);
	ajax.onreadystatechange = function () {

		//alert(ajax.readyState);

		if (ajax.readyState == 4) {

			var response = ajax.responseText;
			var start = response.indexOf('<!--jlml-mod' + moduleid + 'nr' + nr + ' start-->');
			var finish = response.indexOf('<!--jlml-mod' + moduleid + 'nr' + nr + ' end-->');

			justMatch = response.substring(start, finish);

			//alert(justMatch);

			var myFx = new Fx.Morph(classadd);
			myFx.start({'opacity': 1});
			//jQuery(classadd).innerHTML=justMatch;
			document.getElementById(classadd).innerHTML = justMatch;

		}
		addJLMLtips('.jlmlTeamname', 'over');
	};
	return false;
}

var JLMLToolTip = new Class({
	initialize: function (options) {
		this.options = Object.extend({
			tipper: null,   // element displaying the tooltip
			message: null,   // some predefined message
			ajax: null,      // show message from ajax / if message is not null, this will override it
			ToolTipClass: 'tool',  // tooltip display class
			followMouse: false,  // follow mouse on move
			sticky: true,    // remove tooltip if closed
			fromTop: -15,   // distance from mouse or object
			duration: 300,    // fade effect transition duration
			fadeDistance: 20,    // the distance the tooltip sarts fading in/out
			closeOn: 'click'  // all options to close: click, mouseover, mouseleave
		}, options || {});
		this.el = $(this.options.tipper);
		this.start();
		this.visible = 0;
	},

	start: function () {
		this.createContainer();

		this.header.setHTML(this.el.title);
		this.el.set({'title': ''});

		if (!this.options.ajax) {
			this.message.setHTML(this.options.message);
		} else {
			this.message.removeClass('message');
			this.message.addClass('message_loading');
			new Ajax(this.options.ajax, {
				method: 'get',
				onComplete: function () {
					this.message.removeClass('message_loading');
					this.message.addClass('message');
				}.bind(this),
				update: this.message
			}).request();
		}

		this.fx = new Fx.Styles(this.container, {
			duration: this.options.duration,
			wait: false,
			transition: Fx.Transitions.Sine.easeOut
		});

		this.el.addEvent(this.options.followMouse ? 'mousemove' : 'mouseenter', this.showToolTip.bind(this));
		if (!this.options.sticky) {
			this.el.addEvent('mouseleave', this.hideToolTip.bind(this));

		} else {

			this.closeTip = new Element('a').set({
				'class': 'sticky_close',
				'href': 'javascript:void(0);'
			}).setStyles({'position': 'absolute', 'top': 3, 'right': 3});
			this.closeTip.injectInside(this.header);
			this.closeTip.addEvent(this.options.closeOn, this.hideToolTip.bind(this));
			this.container.addEvent('mouseleave', this.hideToolTip.bind(this));
		}
	},

	showToolTip: function (ev) {
		var event = new Event(ev);
		$$('.tmp_sticky_tip').each(function (handle) {
			handle.setStyles({'display': 'none'});
		});
		this.container.addClass('tmp_sticky_tip');
		this.elemHeight = this.el.getCoordinates().height;


		this.top = this.options.followMouse ? event.client.y : this.el.getPosition().y;
		var left = this.options.followMouse ? event.client.x : this.el.getPosition().x;

		var top_dist = this.visible == 1 ?
			this.top + this.options.fromTop + this.elemHeight :
			this.top + this.options.fromTop + this.elemHeight + this.options.fadeDistance;

		this.container.setStyles({'top': top_dist, 'left': left, 'display': 'block', 'z-index': '110000'});
		this.fx.start({'opacity': 1, 'top': this.top + this.options.fromTop + this.elemHeight});
		this.visible = 1;
	},

	hideToolTip: function () {
		this.container.setStyles({'z-index': '100000'});
		this.fx.start({
			'opacity': 0,
			'top': this.top + this.options.fromTop + this.elemHeight + this.options.fadeDistance
		});
		this.visible = 0;
	},

	createContainer: function () {
		this.container = new Element('div');
		this.container.addClass(this.options.ToolTipClass + '-tip');
		this.container.setStyles({'position': 'absolute', 'opacity': 0, 'display': 'none', 'z-index': '100000'});
		this.container.injectInside(document.body);
		this.wrapper = new Element('div').inject(this.container);
		this.header = new Element('span').inject(new Element('div', {'class': this.options.ToolTipClass + '-title'}).inject(this.wrapper));
		this.message = new Element('span').inject(new Element('div', {'class': this.options.ToolTipClass + '-text'}).inject(this.wrapper));
	}
});

function addJLMLtips(els, suffix) {
	$$(els).each(function (el) {
		var tipsource = el.id + '_' + suffix;
		if ($(tipsource)) {
			new JLMLToolTip({tipper: el, sticky: true, message: $(tipsource).innerHTML});
		}
	});
}
