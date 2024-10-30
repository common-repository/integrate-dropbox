(()=>{"use strict";function e(e){return"function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?typeof e:e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e}function t(e,t){let o=Object.keys(e);if(Object.getOwnPropertySymbols){let n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((t=>Object.getOwnPropertyDescriptor(e,t).enumerable))),o.push(...n)}return o}function o(t,o,n){const r=function(t,o){if("object"!==e(t)||!t)return t;const n=t[Symbol.toPrimitive];if(void 0!==n){const o=n.call(t,"string");if("object"!==e(o))return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(t)}(o);return(o="symbol"===e(r)?r:String(r))in t?Object.defineProperty(t,o,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[o]=n,t}const n=window.jQuery,r=window.indboxModuleBuilder,i={init:function(){n(window).on("elementor/frontend/init",(function(){"undefined"!=typeof elementor&&(window.elementor.channels.editor.on("indbox:editor:edit_module",i.initModuleBuilder),window.elementor.channels.editor.on("indbox:editor:upgrade_to_pro",(function(){window.indbox?.upgradeUrl&&window.open(window.indbox.upgradeUrl,"_blank")}))),i.initPromotion()}))},ready:function(){"undefined"!=typeof elementor&&window.elementor.hooks.addFilter("elementor_pro/forms/content_template/field/google_drive_upload",(function(e,t){let o=t.module_data||JSON.stringify({type:"uploader",isFormUploader:"elementor",isRequired:t.required,width:"100%",height:""});return`<div class="integrate-dropbox-preview-wrapper"  data-content='${n=o,btoa(encodeURIComponent(n).replace(/%([0-9A-F]{2})/g,((e,t)=>String.fromCharCode("0x"+t))))}'></div>`;var n}),10)},initModuleBuilder:function(e){Swal.fire({html:'<div id="indbox-module-builder" class="indbox-module-builder-modal-wrap"></div>',showConfirmButton:!1,customClass:{container:"indbox-module-builder-modal-container indbox-toplavel-wrapper"},didOpen:function(){let i={};(e.$el.hasClass("elementor-control-edit_field_form")||e.$el.hasClass("elementor-control-edit_field_metform"))&&(i={type:"uploader",isFormUploader:e.$el.hasClass("elementor-control-edit_field_form")?"elementor":"metform"});const l=window.parent.jQuery('[data-setting="module_data"]'),d=n(l).val();if(d)try{i=JSON.parse(d)}catch{i={}}document.getElementById("indbox-module-builder").addEventListener("click",(function(e){e.stopPropagation()})),r({rootId:"indbox-module-builder",attributes:i,setAttributes:function(e){const n=function(e,...n){for(let r=0;r<n.length;r++){const i=null!=n[r]?n[r]:{};r%2?t(Object(i),!0).forEach((t=>{o(e,t,i[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(i)):t(Object(i)).forEach((t=>{Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(i,t))}))}return e}({isInit:!0},e);l.length>0?l.val(JSON.stringify(n)).trigger("input"):console.error("Module data setting element not found"),Swal.close()},closeModal:function(){Swal.close()}})},willClose:function(){ReactDOM.unmountComponentAtNode(document.getElementById("indbox-module-builder"))}})},initPromotion:function(){if(void 0===parent.document)return!1;parent.document.addEventListener("mousedown",(function(e){const t=parent.document.querySelectorAll(".elementor-element--promotion");if(t.length>0)for(let o=0;o<t.length;o++)if(t[o].contains(e.target)){const n=parent.document.querySelector("#elementor-element--promotion__dialog");if(t[o].querySelector(".icon > i").classList.toString().includes("indbox-pro"))if(e.stopImmediatePropagation(),n.querySelector(".dialog-buttons-action").style.display="none",null===n.querySelector(".indbox-dialog-action")){const e=document.createElement("a"),t=document.createTextNode(wp.i18n.__("Upgrade Now","integrate-dropbox"));e.setAttribute("href",`${window.indbox?.upgradeUrl}`),e.classList.add("elementor-button","go-pro","dialog-button","dialog-action","indbox-dialog-action"),e.appendChild(t),n.querySelector(".dialog-buttons-action").insertAdjacentHTML("afterend",e.outerHTML)}else n.querySelector(".dialog-buttons-action").style.display="none",n.querySelector(".indbox-dialog-action").style.display="";else{n.querySelector(".dialog-buttons-action").style.display="";const e=n.querySelector(".indbox-dialog-action");e&&(e.style.display="none")}break}}))}};i.init(),n(document).ready(i.ready)})();