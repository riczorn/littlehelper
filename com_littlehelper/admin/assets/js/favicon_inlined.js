/**
 * This script initializes the rixxcropper for the favicon view of LittleHelper
 * (c) fasterjoomla.com
 */

/**
 * Transparent test - color background fader
 */
var currColor = 1;
		var backArray = new Array("#F2F2FE","#DD0020","#202080","#FFFF99","#101010","#808080","transparent");
		var colorArray = new Array("#101010","#F2F2FE","#F2F2FE","#101010","#F2F2FE","#F2F2FE","#101010");
		function changeBackground() {
			if (++currColor>=colorArray.length) {currColor = 0}
			jQuery("#chosenImages li").stop().animate({
				backgroundColor: backArray[currColor],
				color:colorArray[currColor],
				border:"2px dashed green",
			},1500);
			//document.getElementById("chosenImages").style.color = colorArray[currColor];
		}
		
		var cropper;
		var progress;
		/**
		 * select a different image for cropping
		 * @param elem
		 */
		function switchImage(elem) {
			cropper.load(elem.src);
			jQuery('#gallery').slideUp("fast");
			jQuery('#dcrop').slideDown("slow");
		}
		
		function saveAll() {
//			var sel = document.getElementById('imagesizes').items[document.getElementById('imagesizes').index].value;
			var sel =	144;
			cropper.save(sel,true);
		}
		
		/**
		 * Save image;
		 */
		function saveSize() {
//			var sel = document.getElementById('imagesizes').items[document.getElementById('imagesizes').index].value;
			var sel =	jQuery("#imagesizes").val();
			cropper.save(sel,false);
		}
		
		/**
		 * Show Message Utility to display messages to the user.
		 * @param message
		 */
		function showMessage(message) {
			var winheight = $(window).innerHeight();
			message = "<p>"+message.replace( /\n/gm, "</p><p>")+"</p>";
			jQuery("<div class=\'rixxmessage\'><div >"+
				message + "</div></div>").click(
						function() {
							
							$(this).fadeOut("slow",function() {$(this).remove();});
						}
					).appendTo("body").children().css({
					"height":winheight/2,
					"margin-top":winheight/4,
					}).parent().fadeIn("slow");
		}
		
		function updateThumbnails() {
			// now update the thumbnails below:
			//console.log('updating thumbs')
			jQuery('#chosenImages img').each(function() {
				var src = jQuery(this).get(0).src;
				//console.log('  image '+src)
				if (src ) {
					if (src.indexOf('?')>0) {
						src = src.substr(0,src.indexOf('?'))
					}
					src = src + "?" + parseInt(Math.random()*100000);
					//console.log('    image::new  '+src)
					jQuery(this).get(0).src = src;
				}
			})	
		}
		
		jQuery(function($) {
			/**
			 * Accordion
			 */
			$('h3.step').click(function() {
				$(this).next().slideToggle("slow");
			});
			
			
			/**
			 * Initialize cropper and callbacks:
			 */
			
			progress = $("#progressdiv");
			cropper = $("#target").rixxcropper({
				cropOptions: {
					bgOpacity: 0.5,
					bgColor: 'white',
					addClass: 'jcrop-light',
					aspectRatio: 1,
					minSize:[2,2],
					//maxSize:[500,500],
					boxWidth :500,
					//boxHeight:500,
				},
				// note: the postUrl contains the option and task because in case of
				// post_max_size exceeded the files and post will be empty!
				postUrl:"index.php?option=com_littlehelper&task=favicon.saveImageCrop",
				extraParams:[{option:"com_component"},{task:"favicon.saveImageCrop"}],
				uploadDone:function(data) {
					$(progress).hide();
					jdata = JSON.parse(data);
					
					if (jdata.error==0) {
						if (jdata.content.length>0)
							//showMessage(jdata.message + "\n" + jdata.content);
							updateThumbnails();
						else
							console.log("no files uploaded");
					} else {
						showMessage(jdata.message + "\nError code:"+jdata.error);
					}
				},
				uploadError:function(req, status, error) {
					$(progress).hide();
					showMessage("There was an error processing your request\n" +  "status: " + status +
					";\nerror: " + error + ";\nYou can find more information in the server error log." + 
					"\nRequest:\n" + JSON.stringify(req));
				},
				
				uploadProgress:function(event) {
					$(progress).show();
					if (event.lengthComputable) {
						 var complete = (event.loaded / event.total * 100 | 0);
						progress.value = progress.innerHTML = complete;
						console.log("Loaded " + parseInt( (event.loaded / event.total * 100), 10) + "%");
					}
					else {
						console.log("Length not computable.");
					}
				},
				fileDropped: function() {
					jQuery('#dcrop').slideDown("slow");
				},
				jCropLoaded: function() {
					//console.log('jCrop was loaded');
				},

			});
			//cropper.destroyCrop();
		});	