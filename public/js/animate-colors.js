"use strict";!function(r){function e(e,a,n){var o="rgb"+(r.support.rgba?"a":"")+"("+parseInt(e[0]+n*(a[0]-e[0]),10)+","+parseInt(e[1]+n*(a[1]-e[1]),10)+","+parseInt(e[2]+n*(a[2]-e[2]),10);return r.support.rgba&&(o+=","+(e&&a?parseFloat(e[3]+n*(a[3]-e[3])):1)),o+")"}function a(r){var e;return(e=/#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/.exec(r))?[parseInt(e[1],16),parseInt(e[2],16),parseInt(e[3],16),1]:(e=/#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])/.exec(r))?[17*parseInt(e[1],16),17*parseInt(e[2],16),17*parseInt(e[3],16),1]:(e=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(r))?[parseInt(e[1]),parseInt(e[2]),parseInt(e[3]),1]:(e=/rgba\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9\.]*)\s*\)/.exec(r))?[parseInt(e[1],10),parseInt(e[2],10),parseInt(e[3],10),parseFloat(e[4])]:o[r]}r.extend(!0,r,{support:{rgba:function(){var e=r("script:first"),a=e.css("color"),n=!1;if(/^rgba/.test(a))n=!0;else try{n=a!=e.css("color","rgba(0, 0, 0, 0.5)").css("color"),e.css("color",a)}catch(o){}return n}()}});var n="color backgroundColor borderBottomColor borderLeftColor borderRightColor borderTopColor outlineColor".split(" ");r.each(n,function(n,o){r.Tween.propHooks[o]={get:function(e){return r(e.elem).css(o)},set:function(n){var t=n.elem.style,s=a(r(n.elem).css(o)),l=a(n.end);n.run=function(r){t[o]=e(s,l,r)}}}}),r.Tween.propHooks.borderColor={set:function(o){var t=o.elem.style,s=[],l=n.slice(2,6);r.each(l,function(e,n){s[n]=a(r(o.elem).css(n))});var c=a(o.end);o.run=function(a){r.each(l,function(r,n){t[n]=e(s[n],c,a)})}}};var o={aqua:[0,255,255,1],azure:[240,255,255,1],beige:[245,245,220,1],black:[0,0,0,1],blue:[0,0,255,1],brown:[165,42,42,1],cyan:[0,255,255,1],darkblue:[0,0,139,1],darkcyan:[0,139,139,1],darkgrey:[169,169,169,1],darkgreen:[0,100,0,1],darkkhaki:[189,183,107,1],darkmagenta:[139,0,139,1],darkolivegreen:[85,107,47,1],darkorange:[255,140,0,1],darkorchid:[153,50,204,1],darkred:[139,0,0,1],darksalmon:[233,150,122,1],darkviolet:[148,0,211,1],fuchsia:[255,0,255,1],gold:[255,215,0,1],green:[0,128,0,1],indigo:[75,0,130,1],khaki:[240,230,140,1],lightblue:[173,216,230,1],lightcyan:[224,255,255,1],lightgreen:[144,238,144,1],lightgrey:[211,211,211,1],lightpink:[255,182,193,1],lightyellow:[255,255,224,1],lime:[0,255,0,1],magenta:[255,0,255,1],maroon:[128,0,0,1],navy:[0,0,128,1],olive:[128,128,0,1],orange:[255,165,0,1],pink:[255,192,203,1],purple:[128,0,128,1],violet:[128,0,128,1],red:[255,0,0,1],silver:[192,192,192,1],white:[255,255,255,1],yellow:[255,255,0,1],transparent:[255,255,255,0]}}(jQuery);
//# sourceMappingURL=animate-colors.js.map