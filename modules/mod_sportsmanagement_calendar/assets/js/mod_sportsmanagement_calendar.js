var jlcinjectcontainer = new Array();
var jlcmodal = new Array();

//window.addEvent('domready', function() {
//	SqueezeBox.initialize({});
//});

// jQuery(function ($) {
//     SqueezeBox.initialize({});
//     SqueezeBox.assign($("a.modal").get(), {
//         parse: "rel",
//     });
// });

function jlCalmod_setTitle(targetid, sourceids, thistitle, modid) {
    var titleid = sourceids.replace("jlcal_", "jlcaltitte_");
    if (document.getElementById(titleid)) {
        document.getElementById("jlCalListDayTitle-" + modid).innerHTML =
            document.getElementById(titleid).innerHTML;
    }
}

function jlCalmod_setContent(
    targetid,
    tempcontentid,
    sourcecontent,
    thistitle,
    modid
) {
    document.getElementById(targetid).innerHTML = sourcecontent;
    document.getElementById(tempcontentid).innerHTML =
        '<div class="componentheading">' +
        //			+ thistitle.replace('<br />', ' - ') + '</div>' + sourcecontent;
        "</div>" +
        sourcecontent;
    jQuery("#" + tempcontentid + " acronym").each(function (handle) {
        var header = new Element("span").injectAfter(handle);
        header.innerHTML = handle.title;
        handle.dispose();
    });
}

function jlCalmod_injectContent(sourceid, destinationid, modid) {
    //alert ('destinationid -> ' + destinationid);
    //alert ('sourceid -> ' + sourceid);

    //	var tmp = document.getElementById(destinationid).innerHTML;

    //	if (!document.getElementById('temp_jlcal-' + modid))
    //  {
    //		document.getElementById(destinationid).innerHTML = '<div id="temp_jlcal-' + modid	+	'" class="jcal_inject"></div>' + tmp;
    //	}
    //	var closer = '<span class="jcal_inject_close" onclick="document.getElementById(\'temp_jlcal-'	+ modid + '\').style.display=\'none\';">x</span>';
    //	document.getElementById('temp_jlcal-' + modid).innerHTML = closer + document.getElementById(sourceid).innerHTML;

    // der text in der modalbox für bootstrap
    document.getElementById("myModalbody" + modid).innerHTML =
        document.getElementById(sourceid).innerHTML;

    // �ffnet die moadalbox für bootstrap
    jQuery("#myModal" + modid).modal();

    //jQuery('temp_jlcal-' + modid).css("display", "block");
}

function jlCalmod_showhide(targetid, sourceids, thistitle, inject, modid) {
    //alert ('sourceids -> ' + sourceids);

    if (jQuery(targetid)) {
        var targetcontent = document.getElementById(targetid).innerHTML;
        var sourcecontent = document.getElementById(sourceids)
            ? document.getElementById(sourceids).innerHTML
            : "Something went wrong this day";
        var tempcontentid = "jlCalList-" + modid + "_temp";

        // die �berschrift in der modalbox für bootstrap
        document.getElementById("myModalheader" + modid).innerHTML = thistitle;

        jlCalmod_setTitle(targetid, sourceids, thistitle, modid);

        jlCalmod_setContent(
            targetid,
            "jlCalList-" + modid + "_temp",
            sourcecontent,
            thistitle,
            modid
        );

        var incont = jlcinjectcontainer[modid];

        if (jQuery(incont) && inject > 0) {
            jlCalmod_injectContent(tempcontentid, incont, modid);
        }
        // if (jlcmodal[modid] == 1) {
        //     SqueezeBox.setContent("string", sourcecontent);
        // }
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
    if (jQuery("jlCalListDayTitle-" + modid))
        document.getElementById("jlCalListDayTitle-" + modid).innerHTML = "";
    if (jQuery("jlCalListTitle-" + modid))
        document.getElementById("jlCalListTitle-" + modid).innerHTML = "";
    if (jQuery("jlcteam" + modid))
        jQuery("jlcteam" + modid).toggleClass("jcalbox_hidden");
    if (jQuery("jlCalList-" + modid))
        document.getElementById("jlCalList-" + modid).innerHTML = "";
}

function jlcnewDate(month, year, modid, day) {
    if (!day) day = 0;
    var teamid = 0;
    if (jQuery("jlcteam" + modid)) teamid = jQuery("#jlcteam" + modid).val();
    //var myFx = new Fx.Morph('jlctableCalendar-' + modid);
    //myFx.start({
    //		'opacity' : 0
    //});
    loadHtml =
        "<p id='loadingDiv-" +
        modid +
        "' style='margin-left: 10px; margin-top: -10px; margin-bottom: 10px;'>";
    loadHtml +=
        "<img src='" +
        calendar_baseurl +
        "modules/mod_sportsmanagement_calendar/assets/images/loading.gif'>";
    loadHtml += "</p>";
    document.getElementById("jlccalendar-" + modid).innerHTML += loadHtml;
    jlcHide(modid);
    //var myFx = new Fx.Morph('jlctableCalendar-' + modid);
    //myFx.start({
    //		'opacity' : 1
    //	});

    if (month <= 0) {
        month += 12;
        year--;
    }
    if (month > 12) {
        month -= 12;
        year++;
    }

    // alert('jlcteam ' + teamid + ' year ' + year + ' month ' + month);

    var ajax = jlcnewAjax();
    ajax.open("POST", location.href, true);
    ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    ajax.send(
        "jlcteam=" +
            teamid +
            "&year=" +
            year +
            "&month=" +
            month +
            "&ajaxCalMod=1" +
            "&ajaxmodid=" +
            modid
    );
    ajax.onreadystatechange = function () {
        if (ajax.readyState == 4) {
            var response = ajax.responseText;
            var start = response.indexOf(
                "<!--jlccalendar-" + modid + " start-->"
            );
            var finish = response.indexOf(
                "<!--jlccalendar-" + modid + " end-->"
            );

            justTheCalendar = response.substring(start, finish);

            //var myFx = new Fx.Morph('jlctableCalendar-' + modid);
            //myFx.start({
            //				'opacity' : 1
            //			});
            document.getElementById("jlccalendar-" + modid).innerHTML =
                justTheCalendar;

            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth() + 1;
            var yy = today.getFullYear();
            mm = mm < 10 ? "0" + mm : mm;
            if (dd < 10) dd = "0" + dd;
            var sc = "jlCalList-" + modid;
            var tc = "jlcal_" + yy + "-" + mm + "-" + dd + "-" + modid;
            // if (jQuery(tc))
            //     if (jlcmodal[modid] == 1) {
            //         //jlCalmod_showhide(sc, tc, dd + '.' + mm + '.' + yy, 1, modid);
            //         SqueezeBox.initialize({});

            //         jQuery("a.jlcmodal" + modid).each(function (el) {
            //             el.addEvent("click", function (e) {
            //                 new Event(e).stop();
            //                 SqueezeBox.fromElement(el);
            //             });
            //         });
            //     }
            var JTooltips = new Tips(
                jQuery("#jlccalendar-" + modid + " .hasTip"),
                {
                    maxTitleChars: 50,
                    fixed: false,
                }
            );
        }
    };
}
