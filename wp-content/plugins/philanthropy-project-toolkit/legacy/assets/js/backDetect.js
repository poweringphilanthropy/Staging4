/*!
 * Back Detect - jQuery plugin for detecting when a user hits the back button
 *
 * Copyright (c) 2016 Ian Rogren
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Version:  1.0.2
 *
 */
(function($){
	"use strict";
	
 	var backDetectValues = {
 		frameLoaded: 0,
 		frameTry: 0,
 		frameTime: 0,
 		frameDetect: null,
 		frameSrc: null,
 		frameCallBack: null,
 		frameThis: null,
 		frameNavigator: window.navigator.userAgent,
 		frameDelay: 0,
 		frameDataSrc: "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC"
 	};

	$.fn.backDetect = function(callback, delay) {
		backDetectValues.frameThis = this;
		backDetectValues.frameCallBack = callback;
		if(delay !== null){
			backDetectValues.frameDelay = delay;
		}
		if(backDetectValues.frameNavigator.indexOf("MSIE ") > -1 || backDetectValues.frameNavigator.indexOf("Trident") > -1){
				setTimeout(function(){
					$("<iframe src='" + backDetectValues.frameDataSrc + "?loading' style='display:none;' id='backDetectFrame' onload='jQuery.fn.frameInit();'></iframe>").appendTo(backDetectValues.frameThis);
				}, backDetectValues.frameDelay);
		} else {
			setTimeout(function(){
				$("<iframe src='about:blank?loading' style='display:none;' id='backDetectFrame' onload='jQuery.fn.frameInit();'></iframe>").appendTo(backDetectValues.frameThis);
			}, backDetectValues.frameDelay);
		}		
	};

	$.fn.frameInit = function(){
		backDetectValues.frameDetect = document.getElementById("backDetectFrame");
		if(backDetectValues.frameLoaded > 1){
			if(backDetectValues.frameLoaded === 2){
				backDetectValues.frameCallBack.call(this);
				history.go(-1);
			}
		}
		backDetectValues.frameLoaded++;
		if(backDetectValues.frameLoaded === 1){
			backDetectValues.frameTime = setTimeout(function(){jQuery.fn.setupFrames();}, 500);
		}
  }; 

  $.fn.setupFrames = function(){
  	clearTimeout(backDetectValues.frameTime);
		backDetectValues.frameSrc = backDetectValues.frameDetect.src;
  	if(backDetectValues.frameLoaded == 1 && backDetectValues.frameSrc.indexOf("historyLoaded") == -1){
  		if(backDetectValues.frameNavigator.indexOf("MSIE ") > -1 || backDetectValues.frameNavigator.indexOf("Trident") > -1){
  			backDetectValues.frameDetect.src = backDetectValues.frameDataSrc + "?historyLoaded";
  		} else {
				backDetectValues.frameDetect.src = "about:blank?historyLoaded";
  		}
  	}
  };

}(jQuery));