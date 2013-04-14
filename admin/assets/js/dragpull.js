var drag_obj = null;
var min_h = 10; // minimale Höhe
var min_w = 10; // minimale Breite

function mouseDownBody(ev){
    setDragElement(ev);

    if( !drag_obj ) return ;

    document.onmousemove = mouseMoveBody;
    mouseMoveBody(ev)
    return false;
}
function mouseUpBody(ev) {
    if( drag_obj) drag_obj.style.cursor = 'move';
    drag_obj = null;
    document.onmousemove = setDragElement;
    return false;
}

// Das Drag Element initialisieren
function setDragElement(e) {
    if(!e) e = window.event;
    drag_obj = e.target || e.srcElement;

    while(drag_obj) {
         if( drag_obj.id && drag_obj.id.indexOf('img') != -1 ) break;
         drag_obj = drag_obj.parentNode;
    }
    var mouse = mouse_pos(e);
    if(drag_obj) {
         var p = get_rect(drag_obj);
         drag_obj.startLeft = p.left - mouse.left;
         drag_obj.startTop = p.top - mouse.top;
         drag_obj.startW = p.width;
         drag_obj.startH = p.height;
         drag_obj.mouseTop = mouse.top;
         drag_obj.mouseLeft = mouse.left;
         // berechne Cursor-Type;
         drag_obj.drittelX = Math.floor( ( mouse.left - p.left ) * 3 / p.width);
         drag_obj.drittelY = Math.floor( ( mouse.top - p.top ) * 3 / p.height) ;
         if(cursor[drag_obj.drittelY] && cursor[drag_obj.drittelY][drag_obj.drittelX]  ) 
         drag_obj.style['cursor'] = cursor[drag_obj.drittelY][drag_obj.drittelX];
    }
    return mouse;
}
// Mausposition im Dokument bestimmen
function mouse_pos(e) {
    if(!e) e = window.event;
    var body = (window.document.compatMode && window.document.compatMode == "CSS1Compat") ?
    window.document.documentElement : window.document.body || null;

    var mouse = { left:0, top:0 };
    mouse.left = e.pageX ? e.pageX : e.clientX + body.scrollLeft;
    mouse.top = e.pageY ? e.pageY : e.clientY + body.scrollTop;
    return mouse;
}
var cursor = [
['nw-resize', 'n-resize', 'ne-resize'],
['w-resize', 'move', 'e-resize'],
['sw-resize', 's-resize', 'se-resize'],
];

function mouseMoveBody(ev) {
    if(!drag_obj) return ;
    var m = mouse_pos(ev);
    var h = drag_obj.startH;
    var w = drag_obj.startW;
    var top = drag_obj.startTop;
    var left = drag_obj.startLeft;
    if( drag_obj.drittelY == 1 && drag_obj.drittelX == 1){
        drag_obj.style.top = top + m.top + 'px';
        drag_obj.style.left = left + m.left + 'px';
    }

    if(drag_obj.drittelY == 0) {
        // Größe nach oben verändern
        h += drag_obj.mouseTop - m.top;
        if(h > min_h)  drag_obj.style.top = top + m.top + 'px';

    } else if(drag_obj.drittelY == 2) {
        // Größe nach unten verändern
         h -= drag_obj.mouseTop - m.top;
    }
    if(drag_obj.drittelX == 0){
        // Größe nach links verändern
        w +=  drag_obj.mouseLeft - m.left;
        if(w > min_w) drag_obj.style.left = left + m.left + 'px';
    } else if(drag_obj.drittelX == 2) {
        // Größe nach rechts verändern
         w -= drag_obj.mouseLeft - m.left;
    }

    if(h > min_h) drag_obj.style.height = h + 'px';
    if(w > min_w) drag_obj.style.width =  w + 'px';

    return false;
}

function get_rect(o) {
    var rect = {
    left: 0,
    top: 0,
    height: o.offsetHeight,
    width: o.offsetWidth
    }
    while (o) {
         rect.top += parseInt(o.offsetTop );
         rect.left += parseInt(o.offsetLeft );
         o = o.offsetParent;
    }
    return rect;
}
document.onmousedown = mouseDownBody;
document.onmouseup = mouseUpBody;
document.onmousemove = setDragElement;