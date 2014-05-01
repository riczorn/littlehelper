/*!
 * jQuery rixxCropper
 * http://fasterjoomla.com/
 *
 * Simple jQuery plugin that complements JCrop with a drag and drop a client's image 
 * handles the upload and crop server side.
 *
 * Copyright 2013 Riccardo Zorn
 * Dual licensed under the MIT or GPL Version 2 licenses.
 */

(function($, window, undefined) {	
	
	var RixxCropper = function(targetElement) {
		this.target = targetElement;
	}
	
	RixxCropper.prototype = {
		jcrop_api:null,
		boundx:null,
		boundy:null,
		xsize:null,
		ysize:null,
		me:null,
		settings: {
			// image crop //
				enableCrop: true,
				previewPane:'#preview-pane', 		// where JCrop shows previews
				previewContainer:'#preview-pane .preview-container',
				previewContainerImage:'#preview-pane .preview-container img',
				jCropHolder:'.jcrop-holder', 		// the outer container JCrop dynamically creates
				
				// options to pass on to JCrop
				cropOptions: {
					minSize: [0,0],
					maxSize: [0,0],
					bgOpacity: 0.5,
					bgColor: 'white',
					addClass: '',
					aspectRatio: 1,
					boxWidth :0,
					boxHeight:0,
					
				},

			// drop defaults //
				enableDrop: true,
			
			// image paste defaults //
				enablePaste: true,

			// file upload //
				postUrl:'', // the url we're posting to
				extraParams:null, // an optional array of extra parameters to POST to the postUrl;
					// extraParams can contain objects with name-value pairs: {name:'name',value:'value'}
				uploadDone: null,  // a function to hold the results
				uploadFailed: null,

			// file progress
				progressId: 'uploadprogress',
				progressFile: 'upload',
		},
		
		/**
		*  Some quick-access handles of the jCrop preview pane, drop and paste elements 
		*/
		elems: { 				
			preview:null,		// JCrop preview, container and image:
			pcnt:null,
			pimg:null,
			holder:null,		// drop target
			pasteCatcher:null,	// alternative paste dropbox for browsers that don't support pasting i.e. FF
		},

		/**
		* File Dropper features, see initDrop()
		*/
		dropper: {
			tests:null,
			supports:null,
			acceptedTypes:null,
		},

		/**
		* Main initialization; invokes loadCrop which in turn invokes loadDrop, then loadPaste.
		*/
		init : function(settings) {
			me = this;
			me.settings = $.extend(me.settings,settings);
			me.elems.preview = $(me.settings.previewPane);
			me.elems.pcnt = $(me.settings.previewContainer);
			me.elems.pimg = $(me.settings.previewContainerImage);
			
			me.xsize = me.elems.pcnt.width();
			me.ysize = me.elems.pcnt.height();  
			
			me.loadCrop();
			me.loadPaste();
			return this;
		},
				
		destroyCrop: function () {
			me.jcrop_api.destroy();
		},
   
		loadCrop: function () {
			if (!me.settings.enableCrop) return;

			me.target.Jcrop({
				onChange: me.updateSelection,
				onSelect: me.updateSelection,
				onRelease: me.releaseSelection,
				bgOpacity: me.settings.cropOptions.bgOpacity,
				bgColor: me.settings.cropOptions.bgColor,
				boxWidth :me.settings.cropOptions.boxWidth,
				boxHeight:me.settings.cropOptions.boxHeight,
				minSize: me.settings.cropOptions.minSize,
				maxSize: me.settings.cropOptions.maxSize,
				addClass: me.settings.cropOptions.addClass,
				aspectRatio: me.settings.cropOptions.aspectRatio, //xsize / ysize
				},
				function() {
				// Use the API to get the real image size
				var bounds = this.getBounds();
				me.boundx = bounds[0];
				me.boundy = bounds[1];
				
				// Store the API in the jcrop_api variable for later use
				me.jcrop_api = this;
				var msize = Math.min(me.boundy, me.boundx);
				margin = parseInt(msize*10/100); // 10% margin for default selection
				me.jcrop_api.animateTo([margin, margin, msize -margin, msize-margin]); // note: boundy for both coords as we want square.

				// Move the preview into the jcrop container for css positioning
				me.elems.preview.appendTo(me.jcrop_api.ui.holder);
				me.elems.pcnt = $(me.settings.previewContainer);
				me.loadDrop();
				
				// Now run any user-defined callbacks;
				if (me.settings.jCropLoaded)
				{
					me.settings.jCropLoaded();
				}
			});
		},
		
		/* Hold the size of the current selection */
		imageData: null,

		/**
		* Update the current size of the selection
		*/
		updateSelection: function (c)
		{
		if (parseInt(c.w) > 0)
		{
			var rx = me.xsize / c.w;
			var ry = me.ysize / c.h;
			me.elems.pimg.css({
			width: Math.round(rx * me.boundx) + 'px',
			height: Math.round(ry * me.boundy) + 'px',
			marginLeft: '-' + Math.round(rx * c.x) + 'px',
			marginTop: '-' + Math.round(ry * c.y) + 'px'
			});
			me.imageData = c;
		}
		},
		
		/**
		* JCrop onRelease() event, let's just update the preview
		*/
		releaseSelection: function() {
			me.elems.pimg.css({
			width: Math.round(me.xsize) + 'px',
			height: Math.round(me.ysize) + 'px',
			marginLeft: '0px',
			marginTop: '0px'
			});

			me.imageData = {
				x:0,y:0,
				x2:me.boundx,
				y2:me.boundy,
				w:me.boundx,
				h:me.boundy
			}
		},

		/**
		* Drop behaviour 
		* https://github.com/remy/html5demos/demos/dnd-upload.html
		*/
		imageModified:false, // else why bother?
		imagesrc:null,		// this will be sent to the server

		/**
		* Load the image and reinit crop & drop
		*/
		previewfile: function (file) {
		if (me.dropper.tests.filereader === true && 
			me.dropper.acceptedTypes[file.type] === true) {

			var reader = new FileReader();
			me.destroyCrop();
			
			reader.onload = me.readerLoad;
			me.imagesrc = file;
			
			reader.readAsDataURL(file);
			
		}  else {
			me.elems.holder.innerHTML += '<p>Uploaded ' + file.name + ' ' + (file.size ? (file.size/1024|0) + 'K' : '');
			console.log('error: the browser does not support file drop.');
		}
		},

		/**
		* Callback from Drop and Paste
		*/
		readerLoad: function(event) {
			return me.genericLoad(event.target.result);
		},
		
		/**
		* An alias of genericLoad that also clears the object.
		* useful to replace the image from the page
		*/
		load: function(src) {
			me.destroyCrop();	
			me.imagesrc = src;
			me.genericLoad(src);
			me.imageModified = false;
		},
		
		/**
		* Additionally called by FF paste fallback
		*/
		genericLoad:function (result) {
			var $container = $(me.elems.holder);
			var width = $container.width();
			var image = new Image();
			me.imageModified = true;
			//me.imagesrc = file;
			image.onload = function(){
				var ratio = image.width / image.height;
				var height = width / ratio;
					$container.height( height);
					me.target.height(height);
					me.target.width(width);
					me.target.get(0).onload = function() {
						me.loadCrop();
						$(me.settings.previewContainerImage).get(0).src = result;
					};
					$container.find('img').height(height).attr('src','result');
					me.target.get(0).src = result;
				
			};
			image.src = result;	
		},
		
		/**
		* Drop: list dropped files
		*/
		readfiles: function(files) {
			if (files.length>0)
			{  
				me.previewfile(files[0]);
			}
		},

			/**
			* Drop: Initialize the Drop functionality
			*/
			loadDrop:function(){
				/*
				* makes use of the brilliant initialization script from https://github.com/remy/html5demos
				*/
				if (!me.settings.enableDrop) return;

				me.elems.holder = $(me.settings.jCropHolder).get(0);
					
					me.dropper.tests = {
					filereader: typeof FileReader != 'undefined',
					dnd: 'draggable' in document.createElement('span'),
					formdata: !!window.FormData,
					progress: "upload" in new XMLHttpRequest
					}, 
					me.dropper.acceptedTypes = {
					'image/png': true,
					'image/jpeg': true,
					'image/gif': true
					},

					progress = document.getElementById(me.settings.progressId);
					fileupload = document.getElementById(me.settings.progressFile);
					me.elems.holder.addEventListener('drop', function(event) {
						event.preventDefault();
						me.readfiles(event.dataTransfer.files);
					}, false);
					me.elems.holder.addEventListener('dragover', function(event) {
						event.preventDefault();},false);
			},

				/**
				* Paste data into canvas; 
				*/
				loadPaste: function () {
				/*
				* References:
				* http://joelb.me/blog/2011/code-snippet-accessing-clipboard-images-with-javascript/
				* https://developer.mozilla.org/en-US/docs/Web/API/FileReader
				* http://stackoverflow.com/questions/6333814/how-does-the-paste-image-from-clipboard-functionality-work-in-gmail-and-google-c
				*/
					if (!me.settings.enablePaste) return;
					if (!window.Clipboard) {
						me.elems.pasteCatcher = document.createElement("div");
							
						// Firefox allows images to be pasted into contenteditable elements
						me.elems.pasteCatcher.setAttribute("contenteditable", "");
							
						// We can hide the element and append it to the body,
						me.elems.pasteCatcher.style.opacity = 0;
						me.elems.pasteCatcher.style.position = "fixed";
						me.elems.pasteCatcher.style.top = "-10px";
						me.elems.pasteCatcher.style.left = "-10px";
						
							
							document.body.appendChild(me.elems.pasteCatcher);
						
						// as long as we make sure it is always in focus
						me.elems.pasteCatcher.focus();
						document.addEventListener("click", function() { me.elems.pasteCatcher.focus(); });
						}
					
						// Add the paste event listener
						window.addEventListener("paste", function(event) {
						// We need to check if event.clipboardData is supported (Chrome)

						var clipboardData = event.clipboardData;//  ||  event.originalEvent.clipboardData;
						if (clipboardData) {
							// Get the items from the clipboard
							var items = clipboardData.items;
							if (items) {
								// Loop through all items, looking for any kind of image
								
								var blob;
								for (var i = 0; i < items.length; i++) {
									if (items[i].type.indexOf("image") === 0) {
									blob = items[i].getAsFile();
									}
								}
								// load image if there is a pasted image
								if (blob !== null) {
									var reader = new FileReader();
									me.destroyCrop();
							
									reader.onload = me.readerLoad;
									
									me.imagesrc = blob;
									reader.readAsDataURL(blob);
								}
							} else {
								console.log('Browser does not support clipboard, using the fallback approach');
								setTimeout(me.checkInput, 30);
							}
						// If we can't handle clipboard data directly (Firefox),
						// we need to read what was pasted from the contenteditable element
						} else {
							// This is a cheap trick to make sure we read the data
							// AFTER it has been inserted.
							console.log('Browser does not support clipboard, using the fallback approach/2');
							setTimeout(me.checkInput, 30);
						}
						});
			},
			
			/**
			*  Parse the input in the paste catcher element (fallback for FF) 
			*/
			checkInput: function() {
			// Store the pasted content in a variable
			var child = me.elems.pasteCatcher.childNodes[0];
			
			// Clear the inner html to make sure we're always
			// getting the latest inserted content
			me.elems.pasteCatcher.innerHTML = "";
				
			if (child) {
				// If the user pastes an image, the src attribute
				// will represent the image as a base64 encoded string.
				if (child.tagName === "IMG") {
					console.log('Fallback pasted image');
					if (child.src !== null) {
							me.destroyCrop();	
							me.imagesrc = child.src;
							
							me.genericLoad(child.src);
						}
				} else {
					console.log('No images in clipboard');
					console.log(child);
				}
			}
			},
			
			/**
			* 2Do will need for later (administrator color replace)
			*/
			_imageMakeBW: function() {
				/*
				* Nice implementation by Gianluca Guarini: lines 204 - 210 and 148 - 183
				* https://github.com/GianlucaGuarini/jQuery.BlackAndWhite/blob/master/jquery.BlackAndWhite.js
				*/
			},

			_imageMakeBWAlt: function() {
				/*
				* http://spyrestudios.com/html5-canvas-image-effects-black-white/
				*/
				var canvas = document.getElementById("area");
				var context = canvas.getContext("2d");
				var image = document.getElementById("canvasSource");
				context.drawImage(image, 0, 0);
				var imgd = context.getImageData(0, 0, 500, 300);
				var pix = imgd.data;
				for (var i = 0, n = pix.length; i < n; i += 4) {
				var grayscale = pix[i  ] * .3 + pix[i+1] * .59 + pix[i+2] * .11;
				pix[i  ] = grayscale;   // red
				pix[i+1] = grayscale;   // green
				pix[i+2] = grayscale;   // blue
				// alpha
				}
				context.putImageData(imgd, 0, 0);
			},

			/**
			* Upload progress
			*/
			_addProgressMethodAdded : false, // ugly singleton

			_addProgressMethod: function() {
				/*
				* jQuery ajax does not natively support progress; thus a great hack is applied:
				* https://gist.github.com/db/966388
				* https://github.com/malsup/form/blob/master/jquery.form.js lines 297-308
				*/
				
				if (me._addProgressMethodAdded) return true;
				me._addProgressMethodAdded = true;

				var originalXhr = $.ajaxSettings.xhr;
				$.ajaxSetup({
					progress: function() { console.log("Progress callback not overridden"); },
					xhr: function() {
					var req = originalXhr(), that = this;
					if (req) {
						if (typeof req.addEventListener == "function") {
							req.addEventListener("progress", function(evt) {
								that.progress(evt);
							},false);
						}
					}
					return req;
					}
				});
			},

			/**
			* Image upload 
			*/
			save: function(targetSize, allbelow) {
				if (arguments.length<2)
				{
					allbelow = true;
				}
				if (arguments.length<1)
				{
					targetSize = 144;
				}
				var fd = new FormData;
				if (me.imageModified)
				{
					fd.append('image',me.imagesrc);
				}
				else {
					fd.append('noimage','true');
					fd.append('imagesrc',me.target.get(0).src);
				}
				// selection
					fd.append('w', me.imageData.w);
					fd.append('h', me.imageData.h);
					fd.append('x1', me.imageData.x);
					fd.append('y1', me.imageData.y);
					fd.append('x2', me.imageData.x2);
					fd.append('y2', me.imageData.y2);
					fd.append('scalewidth', me.target.width());
				// settings
					fd.append('targetSize', targetSize);
					fd.append('targetBelow', allbelow?'true':'false');
					if (me.settings.extraParams)
					{
						for (var i=0; i<me.settings.extraParams.length; i++)
						{
							ep = me.settings.extraParams[i];
							fd.append(ep.name, ep.value);
						}
					}
				me._addProgressMethod();
				
				$.ajax({
				url: me.settings.postUrl,
				data: fd,
				cache: false,
				processData: false,
				contentType: false, 
				type: 'POST',
				success: function(data){
					if (me.settings.uploadDone)
					{
						me.settings.uploadDone(data);
					}
				},
				error:function (req, status, error) {
					if (me.settings.uploadError)
					{
						me.settings.uploadError(req, status, error);
					}
				},
				progress:function(event) {
					if (me.settings.uploadProgress)
					{
						me.settings.uploadProgress(event);
					}
					/* i.e. in the callback: if (event.lengthComputable) {
						console.log("Loaded " + parseInt( (event.loaded / event.total * 100), 10) + "%");
						}*/
				},
				});
			}
	}

	$.fn.rixxcropper = function(settings) {
		if ($('body').data('rixxcroppersingleton')) {
			return $('body').data('rixxcroppersingleton');
		}
		$('body').data('rixxcroppersingleton', this);
		
		return new RixxCropper(this).init(settings);
	}
})(jQuery, window);
