(()=>{"use strict";var e={n:t=>{var o=t&&t.__esModule?()=>t.default:()=>t;return e.d(o,{a:o}),o},d:(t,o)=>{for(var a in o)e.o(o,a)&&!e.o(t,a)&&Object.defineProperty(t,a,{enumerable:!0,get:o[a]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.wp.element,o=window.wp.domReady;var a=e.n(o);const r=window.wp.hooks,s=window.React;let i={data:""},n=e=>{if("object"==typeof window){let t=(e?e.querySelector("#_goober"):window._goober)||Object.assign(document.createElement("style"),{innerHTML:" ",id:"_goober"});return t.nonce=window.__nonce__,t.parentNode||(e||document.head).appendChild(t),t.firstChild}return e||i},l=/(?:([\u0080-\uFFFF\w-%@]+) *:? *([^{;]+?);|([^;}{]*?) *{)|(}\s*)/g,d=/\/\*[^]*?\*\/|  +/g,c=/\n+/g,u=(e,t)=>{let o="",a="",r="";for(let s in e){let i=e[s];"@"==s[0]?"i"==s[1]?o=s+" "+i+";":a+="f"==s[1]?u(i,s):s+"{"+u(i,"k"==s[1]?"":t)+"}":"object"==typeof i?a+=u(i,t?t.replace(/([^,])+/g,e=>s.replace(/([^,]*:\S+\([^)]*\))|([^,])+/g,t=>/&/.test(t)?t.replace(/&/g,e):e?e+" "+t:t)):s):null!=i&&(s=/^--/.test(s)?s:s.replace(/[A-Z]/g,"-$&").toLowerCase(),r+=u.p?u.p(s,i):s+":"+i+";")}return o+(t&&r?t+"{"+r+"}":r)+a},p={},m=e=>{if("object"==typeof e){let t="";for(let o in e)t+=o+m(e[o]);return t}return e},f=(e,t,o,a,r)=>{let s=m(e),i=p[s]||(p[s]=(e=>{let t=0,o=11;for(;t<e.length;)o=101*o+e.charCodeAt(t++)>>>0;return"go"+o})(s));if(!p[i]){let t=s!==e?e:(e=>{let t,o,a=[{}];for(;t=l.exec(e.replace(d,""));)t[4]?a.shift():t[3]?(o=t[3].replace(c," ").trim(),a.unshift(a[0][o]=a[0][o]||{})):a[0][t[1]]=t[2].replace(c," ").trim();return a[0]})(e);p[i]=u(r?{["@keyframes "+i]:t}:t,o?"":"."+i)}let n=o&&p.g?p.g:null;return o&&(p.g=p[i]),((e,t,o,a)=>{a?t.data=t.data.replace(a,e):-1===t.data.indexOf(e)&&(t.data=o?e+t.data:t.data+e)})(p[i],t,a,n),i};function g(e){let t=this||{},o=e.call?e(t.p):e;return f(o.unshift?o.raw?((e,t,o)=>e.reduce((e,a,r)=>{let s=t[r];if(s&&s.call){let e=s(o),t=e&&e.props&&e.props.className||/^go/.test(e)&&e;s=t?"."+t:e&&"object"==typeof e?e.props?"":u(e,""):!1===e?"":e}return e+a+(null==s?"":s)},""))(o,[].slice.call(arguments,1),t.p):o.reduce((e,o)=>Object.assign(e,o&&o.call?o(t.p):o),{}):o,n(t.target),t.g,t.o,t.k)}g.bind({g:1});let y,b,h,v=g.bind({k:1});function x(e,t){let o=this||{};return function(){let a=arguments;function r(s,i){let n=Object.assign({},s),l=n.className||r.className;o.p=Object.assign({theme:b&&b()},n),o.o=/ *go\d+/.test(l),n.className=g.apply(o,a)+(l?" "+l:""),t&&(n.ref=i);let d=e;return e[0]&&(d=n.as||e,delete n.as),h&&d[0]&&h(n),y(d,n)}return t?t(r):r}}var w=(e,t)=>(e=>"function"==typeof e)(e)?e(t):e,E=(()=>{let e=0;return()=>(++e).toString()})(),k=(()=>{let e;return()=>{if(void 0===e&&typeof window<"u"){let t=matchMedia("(prefers-reduced-motion: reduce)");e=!t||t.matches}return e}})(),j="default",$=(e,t)=>{let{toastLimit:o}=e.settings;switch(t.type){case 0:return{...e,toasts:[t.toast,...e.toasts].slice(0,o)};case 1:return{...e,toasts:e.toasts.map(e=>e.id===t.toast.id?{...e,...t.toast}:e)};case 2:let{toast:a}=t;return $(e,{type:e.toasts.find(e=>e.id===a.id)?1:0,toast:a});case 3:let{toastId:r}=t;return{...e,toasts:e.toasts.map(e=>e.id===r||void 0===r?{...e,dismissed:!0,visible:!1}:e)};case 4:return void 0===t.toastId?{...e,toasts:[]}:{...e,toasts:e.toasts.filter(e=>e.id!==t.toastId)};case 5:return{...e,pausedAt:t.time};case 6:let s=t.time-(e.pausedAt||0);return{...e,pausedAt:void 0,toasts:e.toasts.map(e=>({...e,pauseDuration:e.pauseDuration+s}))}}},O=[],C={toasts:[],pausedAt:void 0,settings:{toastLimit:20}},A={},D=(e,t=j)=>{A[t]=$(A[t]||C,e),O.forEach(([e,o])=>{e===t&&o(A[t])})},N=e=>Object.keys(A).forEach(t=>D(e,t)),z=(e=j)=>t=>{D(t,e)},I={blank:4e3,error:4e3,success:2e3,loading:1/0,custom:4e3},P=e=>(t,o)=>{let a=((e,t="blank",o)=>({createdAt:Date.now(),visible:!0,dismissed:!1,type:t,ariaProps:{role:"status","aria-live":"polite"},message:e,pauseDuration:0,...o,id:(null==o?void 0:o.id)||E()}))(t,e,o);return z(a.toasterId||(e=>Object.keys(A).find(t=>A[t].toasts.some(t=>t.id===e)))(a.id))({type:2,toast:a}),a.id},_=(e,t)=>P("blank")(e,t);_.error=P("error"),_.success=P("success"),_.loading=P("loading"),_.custom=P("custom"),_.dismiss=(e,t)=>{let o={type:3,toastId:e};t?z(t)(o):N(o)},_.dismissAll=e=>_.dismiss(void 0,e),_.remove=(e,t)=>{let o={type:4,toastId:e};t?z(t)(o):N(o)},_.removeAll=e=>_.remove(void 0,e),_.promise=(e,t,o)=>{let a=_.loading(t.loading,{...o,...null==o?void 0:o.loading});return"function"==typeof e&&(e=e()),e.then(e=>{let r=t.success?w(t.success,e):void 0;return r?_.success(r,{id:a,...o,...null==o?void 0:o.success}):_.dismiss(a),e}).catch(e=>{let r=t.error?w(t.error,e):void 0;r?_.error(r,{id:a,...o,...null==o?void 0:o.error}):_.dismiss(a)}),e};var R=v`
from {
  transform: scale(0) rotate(45deg);
	opacity: 0;
}
to {
 transform: scale(1) rotate(45deg);
  opacity: 1;
}`,M=v`
from {
  transform: scale(0);
  opacity: 0;
}
to {
  transform: scale(1);
  opacity: 1;
}`,F=v`
from {
  transform: scale(0) rotate(90deg);
	opacity: 0;
}
to {
  transform: scale(1) rotate(90deg);
	opacity: 1;
}`,L=x("div")`
  width: 20px;
  opacity: 0;
  height: 20px;
  border-radius: 10px;
  background: ${e=>e.primary||"#ff4b4b"};
  position: relative;
  transform: rotate(45deg);

  animation: ${R} 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)
    forwards;
  animation-delay: 100ms;

  &:after,
  &:before {
    content: '';
    animation: ${M} 0.15s ease-out forwards;
    animation-delay: 150ms;
    position: absolute;
    border-radius: 3px;
    opacity: 0;
    background: ${e=>e.secondary||"#fff"};
    bottom: 9px;
    left: 4px;
    height: 2px;
    width: 12px;
  }

  &:before {
    animation: ${F} 0.15s ease-out forwards;
    animation-delay: 180ms;
    transform: rotate(90deg);
  }
`,S=v`
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
`,T=x("div")`
  width: 12px;
  height: 12px;
  box-sizing: border-box;
  border: 2px solid;
  border-radius: 100%;
  border-color: ${e=>e.secondary||"#e0e0e0"};
  border-right-color: ${e=>e.primary||"#616161"};
  animation: ${S} 1s linear infinite;
`,H=v`
from {
  transform: scale(0) rotate(45deg);
	opacity: 0;
}
to {
  transform: scale(1) rotate(45deg);
	opacity: 1;
}`,U=v`
0% {
	height: 0;
	width: 0;
	opacity: 0;
}
40% {
  height: 0;
	width: 6px;
	opacity: 1;
}
100% {
  opacity: 1;
  height: 10px;
}`,q=x("div")`
  width: 20px;
  opacity: 0;
  height: 20px;
  border-radius: 10px;
  background: ${e=>e.primary||"#61d345"};
  position: relative;
  transform: rotate(45deg);

  animation: ${H} 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)
    forwards;
  animation-delay: 100ms;
  &:after {
    content: '';
    box-sizing: border-box;
    animation: ${U} 0.2s ease-out forwards;
    opacity: 0;
    animation-delay: 200ms;
    position: absolute;
    border-right: 2px solid;
    border-bottom: 2px solid;
    border-color: ${e=>e.secondary||"#fff"};
    bottom: 6px;
    left: 6px;
    height: 10px;
    width: 6px;
  }
`,B=x("div")`
  position: absolute;
`,J=x("div")`
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  min-width: 20px;
  min-height: 20px;
`,X=v`
from {
  transform: scale(0.6);
  opacity: 0.4;
}
to {
  transform: scale(1);
  opacity: 1;
}`,Y=x("div")`
  position: relative;
  transform: scale(0.6);
  opacity: 0.4;
  min-width: 20px;
  animation: ${X} 0.3s 0.12s cubic-bezier(0.175, 0.885, 0.32, 1.275)
    forwards;
`,Z=({toast:e})=>{let{icon:t,type:o,iconTheme:a}=e;return void 0!==t?"string"==typeof t?s.createElement(Y,null,t):t:"blank"===o?null:s.createElement(J,null,s.createElement(T,{...a}),"loading"!==o&&s.createElement(B,null,"error"===o?s.createElement(L,{...a}):s.createElement(q,{...a})))},G=e=>`\n0% {transform: translate3d(0,${-200*e}%,0) scale(.6); opacity:.5;}\n100% {transform: translate3d(0,0,0) scale(1); opacity:1;}\n`,K=e=>`\n0% {transform: translate3d(0,0,-1px) scale(1); opacity:1;}\n100% {transform: translate3d(0,${-150*e}%,-1px) scale(.6); opacity:0;}\n`,Q=x("div")`
  display: flex;
  align-items: center;
  background: #fff;
  color: #363636;
  line-height: 1.3;
  will-change: transform;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1), 0 3px 3px rgba(0, 0, 0, 0.05);
  max-width: 350px;
  pointer-events: auto;
  padding: 8px 10px;
  border-radius: 8px;
`,V=x("div")`
  display: flex;
  justify-content: center;
  margin: 4px 10px;
  color: inherit;
  flex: 1 1 auto;
  white-space: pre-line;
`,W=s.memo(({toast:e,position:t,style:o,children:a})=>{let r=e.height?((e,t)=>{let o=e.includes("top")?1:-1,[a,r]=k()?["0%{opacity:0;} 100%{opacity:1;}","0%{opacity:1;} 100%{opacity:0;}"]:[G(o),K(o)];return{animation:t?`${v(a)} 0.35s cubic-bezier(.21,1.02,.73,1) forwards`:`${v(r)} 0.4s forwards cubic-bezier(.06,.71,.55,1)`}})(e.position||t||"top-center",e.visible):{opacity:0},i=s.createElement(Z,{toast:e}),n=s.createElement(V,{...e.ariaProps},w(e.message,e));return s.createElement(Q,{className:e.className,style:{...r,...o,...e.style}},"function"==typeof a?a({icon:i,message:n}):s.createElement(s.Fragment,null,i,n))});!function(e){u.p=void 0,y=e,b=void 0,h=void 0}(s.createElement);var ee=({id:e,className:t,style:o,onHeightUpdate:a,children:r})=>{let i=s.useCallback(t=>{if(t){let o=()=>{let o=t.getBoundingClientRect().height;a(e,o)};o(),new MutationObserver(o).observe(t,{subtree:!0,childList:!0,characterData:!0})}},[e,a]);return s.createElement("div",{ref:i,className:t,style:o},r)},te=g`
  z-index: 9999;
  > * {
    pointer-events: auto;
  }
`,oe=({reverseOrder:e,position:t="top-center",toastOptions:o,gutter:a,children:r,toasterId:i,containerStyle:n,containerClassName:l})=>{let{toasts:d,handlers:c}=((e,t="default")=>{let{toasts:o,pausedAt:a}=((e={},t=j)=>{let[o,a]=(0,s.useState)(A[t]||C),r=(0,s.useRef)(A[t]);(0,s.useEffect)(()=>(r.current!==A[t]&&a(A[t]),O.push([t,a]),()=>{let e=O.findIndex(([e])=>e===t);e>-1&&O.splice(e,1)}),[t]);let i=o.toasts.map(t=>{var o,a,r;return{...e,...e[t.type],...t,removeDelay:t.removeDelay||(null==(o=e[t.type])?void 0:o.removeDelay)||(null==e?void 0:e.removeDelay),duration:t.duration||(null==(a=e[t.type])?void 0:a.duration)||(null==e?void 0:e.duration)||I[t.type],style:{...e.style,...null==(r=e[t.type])?void 0:r.style,...t.style}}});return{...o,toasts:i}})(e,t),r=(0,s.useRef)(new Map).current,i=(0,s.useCallback)((e,t=1e3)=>{if(r.has(e))return;let o=setTimeout(()=>{r.delete(e),n({type:4,toastId:e})},t);r.set(e,o)},[]);(0,s.useEffect)(()=>{if(a)return;let e=Date.now(),r=o.map(o=>{if(o.duration===1/0)return;let a=(o.duration||0)+o.pauseDuration-(e-o.createdAt);if(!(a<0))return setTimeout(()=>_.dismiss(o.id,t),a);o.visible&&_.dismiss(o.id)});return()=>{r.forEach(e=>e&&clearTimeout(e))}},[o,a,t]);let n=(0,s.useCallback)(z(t),[t]),l=(0,s.useCallback)(()=>{n({type:5,time:Date.now()})},[n]),d=(0,s.useCallback)((e,t)=>{n({type:1,toast:{id:e,height:t}})},[n]),c=(0,s.useCallback)(()=>{a&&n({type:6,time:Date.now()})},[a,n]),u=(0,s.useCallback)((e,t)=>{let{reverseOrder:a=!1,gutter:r=8,defaultPosition:s}=t||{},i=o.filter(t=>(t.position||s)===(e.position||s)&&t.height),n=i.findIndex(t=>t.id===e.id),l=i.filter((e,t)=>t<n&&e.visible).length;return i.filter(e=>e.visible).slice(...a?[l+1]:[0,l]).reduce((e,t)=>e+(t.height||0)+r,0)},[o]);return(0,s.useEffect)(()=>{o.forEach(e=>{if(e.dismissed)i(e.id,e.removeDelay);else{let t=r.get(e.id);t&&(clearTimeout(t),r.delete(e.id))}})},[o,i]),{toasts:o,handlers:{updateHeight:d,startPause:l,endPause:c,calculateOffset:u}}})(o,i);return s.createElement("div",{"data-rht-toaster":i||"",style:{position:"fixed",zIndex:9999,top:16,left:16,right:16,bottom:16,pointerEvents:"none",...n},className:l,onMouseEnter:c.startPause,onMouseLeave:c.endPause},d.map(o=>{let i=o.position||t,n=((e,t)=>{let o=e.includes("top"),a=o?{top:0}:{bottom:0},r=e.includes("center")?{justifyContent:"center"}:e.includes("right")?{justifyContent:"flex-end"}:{};return{left:0,right:0,display:"flex",position:"absolute",transition:k()?void 0:"all 230ms cubic-bezier(.21,1.02,.73,1)",transform:`translateY(${t*(o?1:-1)}px)`,...a,...r}})(i,c.calculateOffset(o,{reverseOrder:e,gutter:a,defaultPosition:t}));return s.createElement(ee,{id:o.id,key:o.id,onHeightUpdate:c.updateHeight,className:o.visible?te:"",style:n},"custom"===o.type?w(o.message,o):r?r(o):s.createElement(W,{toast:o,position:i}))}))},ae=_;const re=window.ReactJSXRuntime,se=()=>{const e=(0,t.useCallback)(e=>{const t=Object.assign({message:"Formgent Notification",type:"success"},e||{});"success"===t.type?ae.success(t.message):ae.error(t.message)},[]);return(0,t.useEffect)(()=>((0,r.addAction)("formgent-toast","component-formgent-toast",e),()=>{(0,r.removeAction)("formgent-toast","component-formgent-toast")}),[e]),(0,re.jsx)(oe,{})};a()(()=>{const e=document.createElement("div");e.setAttribute("id","formgent-toast"),e.setAttribute("style","position: absolute; z-index: 9999999999"),document.body.appendChild(e),(0,t.createRoot)(e).render((0,re.jsx)(se,{}))})})();