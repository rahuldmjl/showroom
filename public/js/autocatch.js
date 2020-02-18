(function ( $ ) {

	$.fn.autocatch = function( options ) {
 
        // Default options
        var settings = $.extend({
        	currentSelector: '#'+$(this).attr('id'),
            jsonData: '#customSuggestionsJson',
            suggestionRenderer: '#customSuggestions',
            idElem: '#venID',
            txtElem: false,
            callback: false,
            callbackParam1: false,
            callbackParam2: false,
        }, options );

        $prevKeyDown = 0;
        $curKeyDown = 0;
        $prevKeyUp = 0;
        $curKeyUp = 0;
        $upKeyCode = 0;

        // Apply options
        //return this.append('Hello ' + settings.name + '!').css({ color: settings.color });

        //console.log($(settings.suggestionRenderer).length);
        if($(settings.suggestionRenderer).length <= 0){
        	suggElemId = settings.suggestionRenderer.slice(1);
            $(this).after('<div id="'+suggElemId+'" class="suggetion-dropdown"></div>');
        }

        //console.log(settings.currentSelector);

        $(this).bind('focus input propertychange paste', function() {
        	//console.log("dffgdfg");
        //$(this).on('input propertychange paste', function() {
	    //$("#search_text").change(function(){
	    	if($(settings.idElem).val().length <= 0){
	    		$(settings.idElem).val('');
	    		$(settings.idElem).attr('data-name', '');
        	}

	    	if($(this).val().length >= 0){
	            var arr = $.parseJSON($(settings.jsonData).val());
	            //console.log(arr);

	            var newArr = '';

	            arr = jQuery.grep(arr, function( n, i ) {
	                //return;
	                //console.log(n);
	                //console.log(i);
	                str = n.value.toLowerCase();
	                strReal = n.value;
	                id = n.id;
	                //console.log(str);
	                //console.log(jQuery('#search_text').val());
	                srchText = $(settings.currentSelector).val().toLowerCase();
	                //console.log(srchText);
	                //console.log(str.indexOf(srchText));
	                if(str.indexOf(srchText) !== -1){
	                    //console.log('inininininnin');
	                    //newArr[i] = [];
	                    //newArr[i].push(str);
	                    //var itemArr = {'value':str, 'id':id};
	                    //newArr.push(itemArr);
	                    if(newArr.length > 0){
	                    	newArr += '{"value":"'+strReal+'","id":'+id+'},';
	                	} else {
	                		newArr = '[';
	                		newArr += '{"value":"'+strReal+'","id":'+id+'},';
	                	}
	                }
	            });

	            if(newArr.length > 0){
	            	newArr = newArr.slice(0,-1);
		            newArr += ']';
		            var newArr2 = $.parseJSON(newArr);
		        } else {
		        	var newArr2 = false;
		        }
	            //console.log(newArr);

	            //console.log(newArr2);
	            var customSuggestionsHtml = '<div class="dropdown-menu-show" aria-labelledby="dropdownMenuButton">';
            	if(newArr2.length > 0){
	            	$(newArr2).each( function (key, obj) {
		                //console.log(key);
		                //console.log(obj);
		                customSuggestionsHtml += '<span class="autocatch-item dropdown-item" dropdown-id-selector="'+settings.idElem+'" dropdown-txt-selector="'+settings.txtElem+'" dropdown-current-selector="'+settings.currentSelector+'" dropdown-suggestion-renderer="'+settings.suggestionRenderer+'" dropdown-current-selector="'+settings.currentSelector+'" dropdown-data-id="'+obj.id+'">'+obj.value+'</span>';
		                // onclick="selectDropDownItem('+obj.id+', \''+obj.value+'\', \'search_text\', \'venID\', \'vendorName\', \'customSuggestions\');"
		            });
	            }
	            customSuggestionsHtml += '</div>';
	            $(settings.suggestionRenderer).html(customSuggestionsHtml);
	        }

	    });

	    $(this).keyup(function(e){
	    	$upKeyCode = e.keyCode;
	    });

	    $(this).keydown(function(e){

	    	console.log(e.keyCode);
	    	/*if(settings.multiple){
	    		var dataIndex = $(settings.currentSelector).attr('data-index');
	    		console.log('data-index:'+dataIndex);
	    	}*/

	    	if(e.keyCode == 40){

	    		if($(settings.suggestionRenderer+' .dropdown-menu-show .dropdown-item.active').length == 0){
	                //console.log('in first cond');
	                $(settings.suggestionRenderer+' .dropdown-menu-show .dropdown-item:first-child').addClass('active');
	                $curKeyDown = 0;
	                //console.log($curKeyDown);
	                $(settings.suggestionRenderer+ ' .dropdown-menu-show').scrollTop($curKeyDown);
	                $(this).val($(settings.suggestionRenderer+' .dropdown-menu-show .dropdown-item.active').text());
	            }else{
	            	//console.log('in else cond');
	                $(settings.suggestionRenderer+' .dropdown-menu-show .dropdown-item.active').removeClass('active').next().addClass('active');
	                //$(settings.suggestionRenderer).scrollTop($('.dropdown-menu-show .dropdown-item.active').offset().top-$(settings.suggestionRenderer).height());//then set equal to the position of the selected element minus the height of scrolling div
	                $prevKeyDown = $curKeyDown;
	                //$curKeyDown = $(settings.suggestionRenderer+' .autocatch-item.active').position().top;
	                $curKeyDown = $prevKeyDown + 32;
	                //console.log($prevKeyDown);
	                //console.log($curKeyDown);
	                $gapKeyDown = $curKeyDown - $prevKeyDown;
	                $(settings.suggestionRenderer+ ' .dropdown-menu-show').scrollTop($curKeyDown);
	                $(this).val($(settings.suggestionRenderer+' .dropdown-menu-show .dropdown-item.active').text())
	            }
	        }
	        if(e.keyCode == 38){
	            if($(settings.suggestionRenderer+' .dropdown-menu-show .dropdown-item.active').length == 0){
	                $(settings.suggestionRenderer+' .dropdown-menu-show .dropdown-item:last-child').addClass('active');
	                $(settings.suggestionRenderer+ ' .dropdown-menu-show').scrollTop(0);
	                $(this).val($(settings.suggestionRenderer+' .dropdown-menu-show .dropdown-item.active').text())
	            }else{
	            	$(settings.suggestionRenderer+' .dropdown-menu-show .dropdown-item.active').removeClass('active').prev().addClass('active');
	                $curKeyDown = $curKeyDown - 32;
	                //console.log($curKeyDown);
	                $(settings.suggestionRenderer+ ' .dropdown-menu-show').scrollTop($curKeyDown);
	                $(this).val($(settings.suggestionRenderer+' .dropdown-menu-show .dropdown-item.active').text())
	            }
	        }
	        if(e.keyCode == 13 || e.keyCode == 9 && ($upKeyCode !== e.keyCode)){
	            e.preventDefault();
	            var selTxt = $(settings.suggestionRenderer+' .dropdown-menu-show .dropdown-item.active').text();
	            var selId = $(settings.suggestionRenderer+' .dropdown-menu-show .dropdown-item.active').attr('dropdown-data-id');
	            if(selTxt !== undefined && selId !== undefined){
	            	console.log('testtttt');
		            if(selTxt.length > 0 && selId.length > 0){
			            console.log(selTxt);
			            console.log(selId);
			            //console.log(settings.idElem);
			            //selectDropDownItem(selId, selTxt, 'search_text', 'venID', 'vendorName', 'customSuggestions');
			            /*if(settings.multiple){
			            	$(settings.idElem+'_'+dataIndex).val(selId);
			            } else {*/
			            	$(settings.idElem).val(selId);
			            	$(settings.idElem).attr('data-name', selTxt);
			            //}
			            $(settings.txtElem).val(selTxt);
			            $(this).val(selTxt);
			            $(settings.suggestionRenderer+' .dropdown-menu-show').addClass('dropdown-menu').removeClass('dropdown-menu-show');

			            if(settings.callback !== false){
			            	if(settings.callbackParam1 !== false){
			            		eval(settings.callback + "('"+settings.callbackParam1+"')");
			            	} else {
			            		eval(settings.callback + "()");
			            	}
			            }
			            //console.log('again 9..!!!');
			    		//console.log($(settings.currentSelector).attr('id'));
						//console.log($('#'+curSelId).parent().parent().next().find(".form-control"));
			    		//console.log($('#'+curSelId).parent().parent().next().find(".form-control").attr('id'));
			        }
		    	}

		    	var curSelId = $(settings.currentSelector).attr('id');
	            var nextSelId = $('#'+curSelId).parent().parent().next().find(".form-control").attr('id');
		    	//setTimeout(function(){
		        	//$('#'+nextSelId).focus();
				//}, 1000);
		    	//$('#'+curSelId).nextUntil(".form-control").focus();

		    		//$('#search_diamond_quality_text_0').focus();
		    	/*if(e.keyCode == 9){
		    		console.log('again 9');
		    		$(this).next().focus();
		    	}*/
	        }

	        //scroll to element:
		    //$(".wrapper .inner_div").scrollTop(0);//set to top
		    //$(".wrapper .inner_div").scrollTop($('.element-hover:first').offset().top-$(".wrapper .inner_div").height());//then set equal to the position of the selected element minus the height of scrolling div
	    });

	    $(document).on('click', '.autocatch-item', function() {
	    //console.log("dfgdkgfjnfg");
	    //$('.autocatch-item').click(function(){

	    	/*if(settings.multiple){
	    		var dataIndex = $(settings.currentSelector).attr('data-index');
	    		console.log('data-index:'+dataIndex);
	    	}*/

	    	//console.log($(this).text());
	    	//console.log($(this).attr('dropdown-data-id'));
	    	/*if(settings.multiple){
            	$(settings.idElem+'_'+dataIndex).val($(this).attr('dropdown-data-id'));
            } else {*/
            	$($(this).attr('dropdown-id-selector')).val($(this).attr('dropdown-data-id'));
            	$($(this).attr('dropdown-id-selector')).attr('data-name', $(this).text());
            //}
            if($(this).attr('dropdown-txt-selector').length > 0){
            	$($(this).attr('dropdown-txt-selector')).val($(this).text());
            }

            //$(settings.idElem).val($(this).attr('dropdown-data-id'));

            $($(this).attr('dropdown-current-selector')).val($(this).text());
            $($(this).attr('dropdown-suggestion-renderer')+' .dropdown-menu-show').addClass('dropdown-menu').removeClass('dropdown-menu-show');

            /*var curSelId = $(settings.currentSelector).attr('id');
	    	//console.log($('#'+curSelId).parent().parent().next().find(".form-control"));
	    	//console.log($('#'+curSelId).parent().parent().next().find(".form-control").attr('id'));
	    	var nextSelId = $('#'+curSelId).parent().parent().next().find(".form-control").attr('id');
	    	$('#'+nextSelId).focus();*/

            if(settings.callback !== false){
            	if(settings.callbackParam1 !== false){
            		eval(settings.callback + "('"+settings.callbackParam1+"')");
            	} else {
            		eval(settings.callback + "()");
            	}
            }
            
	    });

	    $(document).on('blur', settings.currentSelector, function($prevRenderer = settings.suggestionRenderer){
	    	//console.log('blurrrr');
	    	if($(settings.idElem).val().length <= 0){
	        	$(settings.currentSelector).val('');
	        } else if($(settings.currentSelector).val().length <= 0){
	        	$(settings.idElem).val('');
	        } else if($(settings.idElem).val().length >= 0){
	        	$(settings.currentSelector).val($(settings.idElem).attr('data-name'));
	        }
	        //console.log($(this).attr('dropdown-suggestion-renderer'));
	        //$($(this).attr('dropdown-suggestion-renderer')+' .dropdown-menu-show').addClass('dropdown-menu').removeClass('dropdown-menu-show');
	        setTimeout(function(){
	        	//console.log(settings.suggestionRenderer);
				//console.log('focusing out...');
				$(settings.suggestionRenderer+' .dropdown-menu-show').addClass('dropdown-menu').removeClass('dropdown-menu-show');
			}, 200);
	    });

		/*$(document).on('focusout', '.form-group', function(){
			setTimeout(function(){
				console.log('focusing out...');
				$(settings.suggestionRenderer+' .dropdown-menu-show').addClass('dropdown-menu').removeClass('dropdown-menu-show');
			}, 500);		
		})*/ 
    };
 
}( jQuery ));