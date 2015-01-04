var jlcinjectcontainer = new Array();
var jlcmodal = new Array();

window.addEvent('domready', function() {
	SqueezeBox.initialize({});
});

function jlCalmod_setTitle(targetid, sourceids, thistitle, modid) {
	var titleid = sourceids.replace('jlcal_', 'jlcaltitte_');
	if ($(titleid)) {
		$('jlCalListDayTitle-' + modid).innerHTML = $(titleid).innerHTML;
	}
}

function jlCalmod_setContent(targetid, tempcontentid, sourcecontent, thistitle, modid) {
	$(targetid).innerHTML = sourcecontent;
	$(tempcontentid).innerHTML = '<div class="componentheading">'
			+ thistitle.replace('<br />', ' - ') + '</div>' + sourcecontent;
	$$('#' + tempcontentid + ' acronym').each(function(handle) {
		var header = new Element('span').injectAfter(handle);
		header.innerHTML = handle.title;
		handle.dispose();
	});
}

function jlCalmod_injectContent(sourceid, destinationid, modid) {
	var tmp = $(destinationid).innerHTML;
	if (!$('temp_jlcal-' + modid)) {
		$(destinationid).innerHTML = '<div id="temp_jlcal-' + modid	+
									'" class="jcal_inject"></div>' + tmp;
	}
	var closer = '<span class="jcal_inject_close" onclick="$(\'temp_jlcal-'
			+ modid + '\').style.display=\'none\';">x</span>';
	$('temp_jlcal-' + modid).innerHTML = closer + $(sourceid).innerHTML;
	$('temp_jlcal-' + modid).style.display = 'block';
}
function jlCalmod_showhide(targetid, sourceids, thistitle, inject, modid) {

	if ($(targetid)) {
		var targetcontent = $(targetid).innerHTML;
		var sourcecontent = ($(sourceids)) ? $(sourceids).innerHTML	: 'Something went wrong this day';
		var tempcontentid = 'jlCalList-' + modid + '_temp';
		jlCalmod_setTitle(targetid, sourceids, thistitle, modid);
		jlCalmod_setContent(targetid, 'jlCalList-' + modid + '_temp', sourcecontent, thistitle, modid);
		var incont = jlcinjectcontainer[modid];
		if ($(incont) && inject > 0) {
			jlCalmod_injectContent(tempcontentid, incont, modid);
		}
		if(jlcmodal[modid] == 1) {
			SqueezeBox.setContent('string', sourcecontent);
		}
	}
}
function jlcnewAjax() {
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

function jlcHide(modid) {
	if ($('jlCalListDayTitle-' + modid))
		$('jlCalListDayTitle-' + modid).innerHTML = '';
	if ($('jlCalListTitle-' + modid))
		$('jlCalListTitle-' + modid).innerHTML = '';
	if ($('jlcteam' + modid))
		$('jlcteam' + modid).toggleClass('jcalbox_hidden');
	if ($('jlCalList-' + modid))
		$('jlCalList-' + modid).innerHTML = '';
}

function jlcnewDate(month, year, modid, day) {
	if (!day)
		day = 0;
	var teamid = 0;
	if ($('jlcteam' + modid))
		teamid = $('jlcteam' + modid).options[$('jlcteam' + modid).selectedIndex].value;
	var myFx = new Fx.Morph('jlctableCalendar-' + modid);
	myFx.start({
		'opacity' : 0
	});
	loadHtml = "<p id='loadingDiv-"
			+ modid
			+ "' style='margin-left: 10px; margin-top: -10px; margin-bottom: 10px;'>";
	loadHtml += "<img src='" + calendar_baseurl +
				"modules/mod_joomleague_calendar/assets/images/loading.gif'>";
	loadHtml += "</p>";
	$('jlccalendar-' + modid).innerHTML += loadHtml;
	jlcHide(modid);
	var myFx = new Fx.Morph('jlctableCalendar-' + modid);
	myFx.start({
		'opacity' : 1
	});

	if (month <= 0) {
		month += 12;
		year--;
	}
	if (month > 12) {
		month -= 12;
		year++;
	}

	var ajax = jlcnewAjax();
	ajax.open("POST", location.href, true);
	ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	ajax.send('jlcteam=' + teamid + '&year=' + year + '&month=' + month
			+ '&ajaxCalMod=1' + '&ajaxmodid=' + modid);
	ajax.onreadystatechange = function() {
		if (ajax.readyState == 4) {

			var response = ajax.responseText;
			var start = response.indexOf('<!--jlccalendar-' + modid
					+ ' start-->');
			var finish = response.indexOf('<!--jlccalendar-' + modid
					+ ' end-->');

			justTheCalendar = response.substring(start, finish);

			var myFx = new Fx.Morph('jlctableCalendar-' + modid);
			myFx.start({
				'opacity' : 1
			});
			$('jlccalendar-' + modid).innerHTML = justTheCalendar;

			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth() + 1;
			var yy = today.getFullYear();
			mm = (mm < 10) ? '0' + mm : mm;
			if (dd < 10)
				dd = '0' + dd;
			var sc = 'jlCalList-' + modid;
			var tc = 'jlcal_' + yy + '-' + mm + '-' + dd + '-' + modid;
			if ($(tc))
				jlCalmod_showhide(sc, tc, dd + '.' + mm + '.' + yy, 1, modid);
			if (SqueezeBox && jlcmodal[modid] == 1) {
				SqueezeBox.initialize({});

				$$('a.jlcmodal' + modid).each(function(el) {
					el.addEvent('click', function(e) {
						new Event(e).stop();
						SqueezeBox.fromElement(el);
					});
				});
			}
			var JTooltips = new Tips($$('#jlccalendar-' + modid + ' .hasTip'),
					{
						maxTitleChars : 50,
						fixed : false
					});
		}
	}
}