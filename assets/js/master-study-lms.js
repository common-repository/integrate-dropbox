!function(t){const e=window.indboxFileSelector;let o;window.indbox={nonce:window.lmsApiSettings?.nonce,isPro:"1"},o={init:function(){o.addSelectFilesButton()},addSelectFilesButton:function(){const e=t("#ms_plugin_root");e?.each((function(){t(this).parent().addClass("indbox-input-wrapper"),t(this).parent().append('<button class="indbox-btn indbox-add-file-btn indbox-mts-lms-button"><span class="indbox-icon indbox-logo"></span>Add Dropbox File</button>')})),t(".indbox-add-file-btn").on("click",(function(t){t.preventDefault();const e=location.pathname;let n=null;if(n="/"===e[e.length-1]?e.split("/").slice(-3,-1):e.split("/").slice(-2),n.length<1)return void o.infoModal();const i="lessons"===n[0],l=Number(n[1]);i&&l?o.handleAddFile(l):o.infoModal()}))},handleAddFile:function(t){Swal.fire({html:'<div id="indbox-file-browser" class="indbox-indbox-file-browser-modal-wrap"></div>',showConfirmButton:!1,customClass:{container:"indbox-file-browser-container indbox-toplavel-wrapper"},didOpen:function(){document.getElementById("indbox-file-browser").addEventListener("click",(function(t){t.stopPropagation()})),e({id:"ms-lms",isMultipleSelect:!1,rootId:"indbox-file-browser",attributes:[],setAttributes:function(e){let n=null,i=null;const l=[];e?.forEach((t=>{"video"===t.indbox_type?i=t:"poster"===t.indbox_type?n=t:"material"===t.indbox_type&&l.push(t)}));const a={id:t,poster:n,video:i,materials:l,nonce:indbox.nonce};o.saveDataWithProgress(a)},closeModal:function(){Swal.close()}})}})},saveDataWithProgress:async function(t){Swal.fire({title:"Saving...",text:"Please wait while we save your data.",allowOutsideClick:!1,didOpen:()=>{Swal.showLoading()}});try{"ok"===(await o.saveData(t)).status?Swal.fire({title:"Saved!",text:"The video and poster were saved successfully.",icon:"success",confirmButtonColor:"#0160fe",showConfirmButton:!1,timer:500}).then((()=>window.location.reload())):Swal.fire({title:"Error",text:"An error occurred. Please try again later.",icon:"error",confirmButtonColor:"#0160fe"})}catch(t){Swal.fire({title:"Error",text:`Request failed: ${t.statusText} (${t.status})`,icon:"error",confirmButtonColor:"#0160fe"})}},saveData:async function(t){return await wp.ajax.post("indbox_set_master_lms_course_materials",t)},findExternalUrl:function(e=100,o=100){return new Promise((n=>{let i=0;const l=()=>{const a=t("[name='external_url']");a.length>0?n(a):i>=o?n(null):(i++,setTimeout(l,e))};l()}))},infoModal:function(){Swal.fire({title:"Action Required",text:"Please create a lesson before adding a Dropbox file.",icon:"info",confirmButtonText:"Understood",confirmButtonColor:"#0160fe"})}},t((function(){o.init()}))}(jQuery);