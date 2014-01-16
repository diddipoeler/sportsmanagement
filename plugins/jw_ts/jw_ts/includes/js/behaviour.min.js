/**
 * @version		2.7
 * @package		Tabs & Sliders (plugin)
 * @author    JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2012 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('6 J={\'1c\':R,\'r\':"1C",\'S\':5(a){6 t=a.s;6 i;4(t.x){t.r=t.x+t.r}i=1D(T(t.r));4(1E(i)){7}t.K(i)},\'U\':5(a){6 c=a.s.r;6 i=a.V;1d(c,i)}};5 1d(a,b,c,d,e,f){n.r=a+"="+1F(b)+((c)?"; 1e="+c.1G():"")+((d)?"; 1f="+d:"")+((e)?"; 1g="+e:"")+((f)?"; 1H":"")}5 T(a){6 b=n.r;6 c=a+"=";6 d=b.W("; "+c);4(d==-1){d=b.W(c);4(d!=0)7 L}X{d+=2}6 e=n.r.W(";",d);4(e==-1){e=b.o}7 1I(b.1J(d+c.o,e))}5 1K(a,b,c){4(T(a)){n.r=a+"="+((b)?"; 1f="+b:"")+((c)?"; 1g="+c:"")+"; 1e=1L, 1h-1M-1N 1i:1i:1h 1O"}}5 p(a){6 b;3.j=L;3.1j="1P";3.Y="1Q";3.1k="1R";3.1l="1S";3.1m="1T";3.Z="1U";3.1n="1V";3.10=[\'1W\',\'1X\',\'1Y\',\'1Z\',\'20\'];3.1o=R;3.1p=R;3.1q=w;3.11=\'<1r>21<1s>\';A(b 22 a){3[b]=a[b]}3.12=u D(\'\\\\b\'+3.1j+\'\\\\b\',\'k\');3.23=u D(\'\\\\b\'+3.Y+\'\\\\b\',\'k\');3.1t=u D(\'\\\\b\'+3.1k+\'\\\\b\',\'k\');3.1u=u D(\'\\\\b\'+3.1l+\'\\\\b\',\'k\');3.13=u D(\'\\\\b\'+3.Z+\'\\\\b\',\'k\');3.8=u 24();4(3.j){3.1v(3.j);3.j=L}}p.y.1v=5(e){6 a,i,E,t,14=0,F,G,q,l,M;4(!n.15){7 w}4(e.x){3.x=e.x}3.8.o=0;a=e.25;A(i=0;i<a.o;i++){4(a[i].h&&a[i].h.N(3.1t)){t=u 26();t.j=a[i];3.8[3.8.o]=t;4(a[i].h.N(3.1u)){14=3.8.o-1}}}F=n.16("27");F.h=3.1m;A(i=0;i<3.8.o;i++){t=3.8[i];t.m=t.j.17;4(3.1p){t.j.17=\'\'}4(!t.m){A(E=0;E<3.10.o;E++){M=t.j.15(3.10[E])[0];4(M){t.m=M.28;4(3.1o){t.m.v(/<29>/k," ");t.m=t.m.v(/<[^>]+>/g,"")}2a}}}4(!t.m){t.m=i+1}G=n.16("O");t.O=G;q=n.16("a");q.18(n.2b(t.m));q.2c="2d:2e(L);";q.17=t.m;q.2f=3.1w;q.s=3;q.B=i;4(3.1q&&3.11){l=3.11;l=l.v(/<1r>/k,3.x);l=l.v(/<2g>/k,i);l=l.v(/<1s>/k,i+1);l=l.v(/<2h>/k,t.m.v(/[^a-2i-2j-9\\-]/k,\'\'));q.x=l}G.18(q);F.18(G)}e.2k(F,e.2l);e.h=e.h.v(3.12,3.Y);3.K(14);4(H 3.S==\'5\'){3.S({s:3})}7 3};p.y.1w=5(b){6 c,a,C,B,P;a=3;4(!a.s){7 w}C=a.s;B=a.B;a.2m();4(H C.U==\'5\'){P={\'s\':C,\'V\':B,\'19\':b};4(!b){P.19=I.19}c=C.U(P);4(c===w){7 w}}C.K(B);7 w};p.y.1x=5(){6 i;A(i=0;i<3.8.o;i++){3.1y(i)}};p.y.1y=5(a){6 b;4(!3.8[a]){7 w}b=3.8[a].j;4(!b.h.N(3.13)){b.h+=\' \'+3.Z}3.1z(a);7 3};p.y.K=5(a){6 b;4(!3.8[a]){7 w}3.1x();b=3.8[a].j;b.h=b.h.v(3.13,\'\');3.1A(a);4(H 3.1B==\'5\'){3.1B({\'s\':3,\'V\':a})}7 3};p.y.1A=5(a){3.8[a].O.h=3.1n;7 3};p.y.1z=5(a){3.8[a].O.h=\'\';7 3};5 1a(a){6 b,z,i;4(!a){a={}}b=u p(a);z=n.15("j");A(i=0;i<z.o;i++){4(z[i].h&&z[i].h.N(b.12)){a.j=z[i];z[i].s=u p(a)}}7 3}5 1b(a){6 b;4(!a){a={}}b=I.Q;4(H I.Q!=\'5\'){I.Q=5(){1a(a)}}X{I.Q=5(){b();1a(a)}}}4(H J==\'2n\'){1b()}X{4(!J[\'1c\']){1b(J)}}',62,148,'|||this|if|function|var|return|tabs|||||||||className||div|gi|aId|headingText|document|length|tabberObj|DOM_a|cookie|tabber||new|replace|false|id|prototype|divs|for|tabberIndex|self|RegExp|i2|DOM_ul|DOM_li|typeof|window|tabberOptions|tabShow|null|headingElement|match|li|onClickArgs|onload|true|onLoad|getCookie|onClick|index|indexOf|else|classMainLive|classTabHide|titleElements|linkIdFormat|REclassMain|REclassTabHide|defaultTab|getElementsByTagName|createElement|title|appendChild|event|tabberAutomatic|tabberAutomaticOnLoad|manualStartup|setCookie|expires|path|domain|01|00|classMain|classTab|classTabDefault|classNav|classNavActive|titleElementsStripHTML|removeTitle|addLinkId|tabberid|tabnumberone|REclassTab|REclassTabDefault|init|navClick|tabHideAll|tabHide|navClearActive|navSetActive|onTabDisplay|jwTabsCookie|parseInt|isNaN|escape|toGMTString|secure|unescape|substring|deleteCookie|Thu|Jan|70|GMT|jwts_tabber|jwts_tabberlive|jwts_tabbertab|jwts_tabbertabdefault|jwts_tabbernav|jwts_tabbertabhide|jwts_tabberactive|h2|h3|h4|h5|h6|nav|in|REclassMainLive|Array|childNodes|Object|ul|innerHTML|br|break|createTextNode|href|javascript|void|onclick|tabnumberzero|tabtitle|zA|Z0|insertBefore|firstChild|blur|undefined'.split('|'),0,{}));

// Initiatize everything
window.addEvent('domready', function() {

	// Append an IE specific class to the body tag
	var bodyClass = document.getElementsByTagName("body")[0].className;
	var isIE6 = navigator.userAgent.toLowerCase().indexOf('msie 6') != -1;
	var isIE7 = navigator.userAgent.toLowerCase().indexOf('msie 7') != -1;
	var isIE8 = navigator.userAgent.toLowerCase().indexOf('msie 8') != -1;
	if(isIE6) document.getElementsByTagName("body")[0].className = bodyClass + ' jwts_IsIE6';
	if(isIE7) document.getElementsByTagName("body")[0].className = bodyClass + ' jwts_IsIE7';
	if(isIE8) document.getElementsByTagName("body")[0].className = bodyClass + ' jwts_IsIE8';

	// Tabs
	tabberAutomatic(tabberOptions);
	
	// Sliders (accordions)
	$$('.jwts_toggleControl').addEvent('click',function(e){
		//e.stop();
		e.preventDefault();
	});
	
	var jwSliders = new Accordion($$('.jwts_toggleControl'),$$('.jwts_toggleContent'), {
		display: -1,
		opacity: false,
		alwaysHide: true,
		onActive: function(toggler) {
			toggler.addClass('jwts_toggleOn');
			toggler.removeClass('jwts_toggleOff');
		},
		onBackground: function(toggler) {
			toggler.addClass('jwts_toggleOff');
			toggler.removeClass('jwts_toggleOn');
		}
	});
	
});
