var CheckNan = function(variable) {
	var output = (isNaN(output = parseFloat($(variable).val())) ? 0 : output);
	return output;
}

var ajaxdataproductlist = function(type,url,token,id,show){

  jQuery.ajax({
      type: type,
      dataType: "json",
      url:url,
      data: {
      "_token": token,
      "id": id
      },
      success: function(data) {
          $('.modal-body').html(data.html);
          $('.ProductDetail').modal(show);
      }
   });
}  


$(function() {
    //$("input[type='number']").prop('min',0);
    $(':input[type="number"]').each(function(data, inp){
        if ($(this).hasClass("sieve_size")) {
            var invalidChars = [
                      "e",
                      "E",
                  ];
                }else{
                  var invalidChars = [
                "-",
                "+",
                "e",
                "E",
            ];

                }              
   
         
        inp.addEventListener("keydown", function(e) {
            if (invalidChars.includes(e.key)) {
               
                e.preventDefault();
            }
        });
    })
});

function goBack() {
  window.history.back();
}

function checkDiamondValidation(i) {

    var shape = $("#search_stone_shape_text_"+i).val();
    var qlty = $("#search_diamond_quality_text_"+i).val();
    var mmSize = $("#mm_size_"+i).val();
    var seiveSize = $("#sieve_size_"+i).val();
    var combIsValid = true;
    if(mmSize != "") {

        $('.stone_shape').each(function($key , $val) {
            if($key < i){
                $shapeprev = $("#search_stone_shape_text_"+$key).val();
                $quaprev = $("#search_diamond_quality_text_"+$key).val();
                $mmsizeprev = $("#mm_size_"+$key).val();
                if($shapeprev == shape && $quaprev == qlty && $mmsizeprev == mmSize){
                    combIsValid = false;
                }
            }
        });
    } else {

        $('.stone_shape').each(function($key , $val) {
            if($key < i){
                $shapeprev = $("#search_stone_shape_text_"+$key).val();
                $quaprev = $("#search_diamond_quality_text_"+$key).val();
                $sievesizeprev = $("#sieve_size_"+$key).val();
                if($shapeprev == shape && $quaprev == qlty && $sievesizeprev == seiveSize){
                    combIsValid = false;
                }
            }
        });
    }

    if(!combIsValid) {

        var repeted_value =$("#diamond_combination_are_repeated").val();
        swal(""+repeted_value+"");
        var shape = $("#search_stone_shape_text_"+i).val("");
        var qlty = $("#search_diamond_quality_text_"+i).val("");
        var mmSize = $("#mm_size_"+i).val("");
        var seiveSize = $("#sieve_size_"+i).val("");
        return;
    }
    return combIsValid;
}



function InputsValidation(i) {
    var inputValid = false;
    var mmSize = $("#mm_size_"+i).val();
    var seiveSize = $("#sieve_size_"+i).val();
    var shapeValid = $('#myform').validate().element("#search_stone_shape_text_"+i);
    var qltValid = $('#myform').validate().element("#search_diamond_quality_text_"+i);
    var wgtValid = $('#myform').validate().element("#search_diamond_weight_text_"+i);
    var rateValid = $('#myform').validate().element("#rate_"+i);
    var MMSizeValid = $('#myform').validate().element("#mm_size_"+i);

    var sieve_size = $('#myform').validate().element("#sieve_size_"+i);
    if(mmSize.length == 0 && seiveSize.length == 0)  {
        $("#mm_size_"+i).addClass("required");
        var MMSizeValid = $('#myform').validate().element("#mm_size_"+i);
    }
    else if(seiveSize.length != 0)  {

        $("#mm_size_"+i).removeClass("required");
        $("#mm_size_"+i).removeAttr('aria-invalid');
        $("#mm_size_"+i+"-error").remove();

    }
    if(!inputValid) {
        if(shapeValid && qltValid && wgtValid && rateValid && MMSizeValid) {
            inputValid = true;
        }
    }
    if(!shapeValid){
        $("#search_stone_shape_text_"+i).focus();
    } else if(!qltValid){
        $("#search_diamond_quality_text_"+i).focus();
    } else if(!wgtValid){
        $("#search_diamond_weight_text_"+i).focus();
    } else if(!MMSizeValid && !sieve_size){
        $("#mm_size_"+i).focus();
    }
    return inputValid;
}


function getHtml(i) {

    var html = '<hr class="w-100"><div id="row'+i+'" class="row">'+

    '<div class="col-lg-3 col-md-3 col-sm-12">'+
    '<div class="form-group"><label for="l30">Diamond Shape</label>'+
    '<input data-commonid="'+i+'" type="text" name="stone_shape[]" autocomplete="off" class="common_input required form-control stone_shape autocomplete_shape_txt" id="search_stone_shape_text_'+i+'" >'+
    '</div></div>'+

    '<div class="col-lg-3 col-md-3 col-sm-12">'+
    '<div class="form-group">'+
    '<label for="l30">Diamond Quality</label>'+
    '<input type="text" name="diamond_quality[]" autocomplete="off" class="required form-control diamond_quality autocomplete_quality_txt" id="search_diamond_quality_text_'+i+'" >'+
    '</div></div>'+

    '<div class="col-lg-3 col-md-3 col-sm-12">'+
    '<div class="form-group error-file">'+
    '<label for="l30">MM Size</label>'+
    '<input placeholder="MM Size" class="form-control mm_size " step="0.01" id="mm_size_'+i+'" name="mm_size[]" min ="0.000" type="number">'+
    '</div></div>'+

    '<div class="col-lg-3 col-md-3 col-sm-12">'+
    '<div class="form-group"><label for="l30">Sieve Size</label>'+
    '<input placeholder="Sieve Size" class="form-control sieve_size  " step="0.01" id="sieve_size_'+i+'" name="sieve_size[]" type="number">'+
    '</div></div>'+

    '<div class="col-lg-3 col-md-3 col-sm-12"><div class="form-group error-file">'+
    '<label for="l30">Diamond Weight</label>'+
    '<input placeholder="Diamond Weight" class="form-control required weight_count" step="0.001" id="search_diamond_weight_text_'+i+'" name="diamond_weight[]" min ="0.001" type="number">'+
    '</div></div>'
    return html;
}


function getRate(i,url,elem) {

        var id = $(elem).attr('data-rate');
        $(elem).find("option:selected").each(function(){
            var optionValue = $(this).attr("value");
            if(optionValue == "") {
                $('#custom_rate_'+i).hide();
                $('#existing_rate_'+i).hide();
            }
            if(optionValue == "Existing"){
                $('#existing_rate_'+id).show();
                $('#custom_rate_'+id).hide();
                $('#custom_'+i).attr('disabled','disabled');
                $('#rate_'+i).attr('required', 'required');
                $('#rate_'+i).removeAttr("disabled");

                var stshape = $('#search_stone_shape_text_'+i).val();
                var dmquality = $('#search_diamond_quality_text_'+i).val();
                var dmsieve_size = $('#sieve_size_'+i).val();
                var dmmm_size = $('#mm_size_'+i).val();
                $.ajax({url: url,

                    data: {shape:stshape,
                    quality:dmquality,
                    sieve_size:dmsieve_size,
                    mm_size:dmmm_size,
                },
                    success: function(result){
                    $("#rate_"+i).val(result.result);
                    if(!$("#rate_"+i).val()) {
                       $("#custom_"+i).val("");
                    }
                    }
                });
            }
            if(optionValue == "Custom"){

                $('#custom_rate_'+id).show();
                $('#rate_'+i).attr('disabled','disabled');
                $('#rate_'+i).removeAttr('required');
                $('#custom_'+i).removeAttr("disabled");
                $('#existing_rate_'+id).hide();
                if($("#custom_"+i).val() != ""){
                   $("#rate_"+i).val("");
                }
            }
        });
    
}
function MmToSiveAjax(shapeid ,mm_size,i,url){
    if (shapeid == 36) {
        $.ajax({
            url: url,
            dataType: "json",
            data: {
                'Shape' : shapeid,
                'Mm_size': mm_size,
                _token:"{{ csrf_token() }}"
               
            },
            success: function(value) {
                if(value.data == null){
                    $('#mm_size_'+i).val(mm_size);
                    $('#sieve_size_'+i).val('');
                }else{
                    /*if(mm_size == '0.75'){
                        $('#sieve_size_'+i).val('-0000');
                    }else{*/
                        $('#sieve_size_'+i).val(value.data);
                    /*}*/
                    
                }
            }
        }); 
    }
}

function SiveToMmAjax(shapeid ,sieve_size,mm_size,i,url){
    if (shapeid == 36) {
        $.ajax({
            url: url,
            dataType: "json",
            data: {
                'Shape' : shapeid,
                'Sieve_size': sieve_size,
                _token:"{{ csrf_token() }}"
               
            },
            success: function(value) {
                if(value.data == null){
                    $('#mm_size_'+i).val(mm_size);
                    $('#sieve_size_'+i).val(sieve_size);
                }else{
                    $('#mm_size_'+i).val(value.data);
                }

               
            }
        }); 
    }
}
function validationWeight(stoneShape,diamondQuality,diamondSieveSize,diamondMmSize,totalWieght,url,numberCount){
    $.ajax({
            url: url,
            data: {shape:stoneShape,
            quality:diamondQuality,
            sieve_size:diamondSieveSize,
            mm_size:diamondMmSize,
        },
        success: function(result){
            if (result.success == true) {
                var weight_ajax= parseFloat(result.result);
               if ( weight_ajax >= totalWieght) {

               }else{
                    swal({title:"You have added   "+totalWieght+" weight , instead of  "+weight_ajax});
                     $('#search_diamond_weight_text_'+numberCount).val('');
                 
               }
              
            } 
        }
    });
}
// scroll top method
$(document).ready(function(){

  var scrollTop = $(".scrollTop");

  $(window).scroll(function() {

    var topPos = $(this).scrollTop();

    if (topPos > 100) {
      $(scrollTop).css("opacity", "1");

    } else {
      $(scrollTop).css("opacity", "0");
    }

  }); 

  $(scrollTop).click(function() {
    $('html, body').animate({
      scrollTop: 0
    }, 800);
    return false;

  }); 

});


