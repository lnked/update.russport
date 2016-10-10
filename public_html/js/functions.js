var css = function(element, style) {
    for (var prop in style) {
        element.style[prop] = style[prop];
    }
}

var addClass = function(element, classname) {
    var cn = element.className;
    if(cn.indexOf(classname) != -1) {
        return;
    }
    if(cn != '') {
        classname = ' '+classname;
    }
    element.className = cn+classname;
}

var removeClass = function(element, classname) {
    var cn = element.className;
    var rxp = new RegExp("\\s?\\b"+classname+"\\b", "g");
    cn = cn.replace(rxp, '');
    element.className = cn;
}