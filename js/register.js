/**
  * Register Module Script
  * @author > Anubir Singh <eddytheemddy@gmail.com>
*/
(function($) {


    $("#datepicker").mask("99/99/9999",{placeholder:"dd/mm/yyyy"});

    	$("#status").select2({
		    minimumResultsForSearch: -1
		}).on('change',function(e) {

	        if($("#status").val()=="premium") {
	            $('#rootwizard ul li.non-player').show();
	        } else {
	            $('#rootwizard ul li.non-player').hide();
	            if($("#status").val()=="agent") {
	            	$('#scout-details').show();
	            } else {
	            	$('#scout-details').hide();
	            }
	        }

            $.when(
                $(".reg-type").fadeOut('fast')
            ).done(function() {
                var status = $("#status").val();
                if(status == "") {
                    $(".reg-type.intro").fadeIn("fast");
                } else {
        	        $(".reg-type." + status).fadeIn("fast");
                }
            });
	    });

	var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };

    var a = getUrlParameter('type');
	if(a != undefined) {
		$("#status").val(a).trigger('change');
		setTimeout(function(){
			$(".fa-truck").click();
		},300);
	}

    $.fn.serializeObject = function(){
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

    $(document).on('change','#country',function(){
    	if($('#country').val()!=""){
            $.when(
                $('.pac-container').remove()
            ).then(function(){
                $("#locality").val('').removeAttr("disabled").focus();
                var a = $('#country').val().toLowerCase();
                var autocomplete = new google.maps.places.Autocomplete(
                    (document.getElementById('locality')), {
                    types: ['(cities)'],
                    componentRestrictions: {country: a}
                });
                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    var place = autocomplete.getPlace();
                    $('#lat').val(place.geometry.location.lat());
                    $('#long').val(place.geometry.location.lng());
                });
            });
    	}else {
            $("#locality").val('');
    		$("#locality").attr("disabled","disabled");
    	}
    });

    $('#locality').keyup(function(){
    	$("#long").val('');
    	$("#lat").val('');
    });
    
    $(document).ready(function() {

        $.validator.setDefaults({ ignore: ":hidden:not(select)" }); //for all select

        $.validator.addMethod("checkdob", function (value, element) {
            if (this.optional(element)) {
                return true;
            }

            var dateOfBirth = value;
            var arr_dateText = dateOfBirth.split("/"),
                day = arr_dateText[0],
                month = arr_dateText[1],
                year = arr_dateText[2];

            var mydate = new Date();
                mydate.setFullYear(year, month - 1, day);

            var maxDate = new Date();
                maxDate.setYear(maxDate.getFullYear() - 13);

            if (maxDate < mydate) {
                return false;
            }
            return true;
        }, 'Sorry, you must be at least 13 years of age.');


        var $validator1 = $("#register-part-1").validate({
            ignore: []
        });

        var $validator = $("#detailsForm").validate({
            ignore: [],
            errorElement: 'span',
            errorPlacement: function(error, element) {
                // console.log(element.attr('name'));
              
                if (element.attr("name") == "long" || element.attr("name") == "lat" ) {
                    if(!$("#locality-error2").length) {
                        var a = $('<span id="locality-error2" class="error" style="">Must be chosen from list</span>');
                        a.insertBefore($("#locality"));
                    }
                } else {
                    error.insertBefore(element);
                }
            },
            invalidHandler: function(form, validator) {

                if(!validator.numberOfInvalids()){
                    return;
                }
            },
            rules: {
                fname: {
                    required: true,
                    minlength: 3
                },
                lname: {
                    required: true,
                    minlength: 3
                },
                email: {
                    email: true,
                    required: true,
                },
                dob: {
                    required: true,
                    dateITA: true,
                    checkdob: true
                },
                gender: {
                    required: true,
                    },
                email: {
                    required: true,
                        email: true
                },
                username: {
                    required: true,
                    maxlength: 10,
                    alphanumeric: true
                },
                lat: {
                    required: true,
                },
                long: {
                    required: true,
                },
                password: "required",
                passwordTwo: {
                  equalTo: "#password"
                },
                address: {
                    required: true,
                },
                city: {
                    required: true,
                },
                tel: {
                    required: true,
                    number: true
                },
                'numclients': {
                  number: true,
                  required: function(element) {
                    if ($("#status").val()=="agent") {
                        return true;
                    } else {
                        return false;
                    }
                  },
                },
                'intentions': {
                  required: function(element) {
                    if ($("#status").val()=="agent") {
                        return true;
                    } else {
                        return false;
                    }
                  },
                },
            },
            messages: {
                dob: {
                    required: "Date must be in dd/mm/yyyy",
                    dateITA: "Invalid Date"
                }
            },
        });
        // remove fontAwesome icon classes
        function removeIcons(btn) {
            btn.removeClass(function(index, css) {
                return (css.match(/(^|\s)fa-\S+/g) || []).join(' ');
            });
        }
        var btnNext = $('#rootwizard').find('.pager .next').find('button');
        var btnPrev = $('#rootwizard').find('.pager .previous').find('button');


        $('#rootwizard').bootstrapWizard({
            onTabShow: function(tab, navigation, index) {

                var $total = navigation.find('li').length;
                var $current = index + 1;

                // If it's the last tab then hide the last button and show the finish instead
                if ($current >= $total) {
                    $('#rootwizard').find('.pager .next').hide();
                    $('#rootwizard').find('.pager .finish').show().removeClass('disabled hidden');
                } else {
                    $('#rootwizard').find('.pager .next').show();
                    $('#rootwizard').find('.pager .finish').hide();
                }

                var li = navigation.find('li.active');

                var btnNext = $('#rootwizard').find('.pager .next').find('button');
                var btnPrev = $('#rootwizard').find('.pager .previous').find('button');

                if ($current > 1 && $current < $total) {

                    var nextIcon = li.next().find('.fa');
                    var nextIconClass = nextIcon.attr('class').match(/fa-[\w-]*/).join();

                    removeIcons(btnNext);
                    btnNext.addClass(nextIconClass + ' btn-animated from-left fa');

                    var prevIcon = li.prev().find('.fa');
                    var prevIconClass = prevIcon.attr('class').match(/fa-[\w-]*/).join();

                    removeIcons(btnPrev);
                    btnPrev.addClass(prevIconClass + ' btn-animated from-left fa');
                } else if ($current == 1) {
                    // console.log($current);
                    // remove classes needed for button animations from previous button
                    btnPrev.removeClass('btn-animated from-left fa');
                    btnNext.parent().removeClass('disabled');
                    removeIcons(btnPrev);
                } else {
                    // remove classes needed for button animations from next button
                    btnNext.removeClass('btn-animated from-left fa');
                    removeIcons(btnNext);
                }
            },
            onNext: function(tab, navigation, index) {
                if(index==1) {
                    var $valid = $('#register-part-1').valid();
                	//on step 1
                    if(!$valid) {
                        $validator.focusInvalid();
                        return false;
                    }
                }
                if(index==2) {
                	//on step 2
                    $('#locality-error2').remove();
                    if($('#status').val() == "premium") {
                        //is player, make next button read finished
                        $('.next').not('.finish').find('button span').text('Next');
                        $('.next').not('.finish').find('button').removeClass('fa-thumbs-o-up').addClass('fa-credit-card');
                    } else {
                        $('.next').not('.finish').find('button span').text('Finished');

                        var nextIconClass = "fa-thumbs-o-up";
                        
                        removeIcons(btnNext);
                        btnNext.addClass(nextIconClass + ' btn-animated from-left fa');
                        $('.next.finish').find('button').removeClass('fa-credit-card').addClass('fa-thumbs-o-up');
                    }
                    var $valid = $('#detailsForm').valid();
                    if(!$valid) {
                        $validator.focusInvalid();
                        return false;
                    } else {
                        $('.next button').addClass('is-waiting');
                        var validate = $.ajax({
                            url: '/register/validateEmail',
                            type: 'POST',
                            dataType: 'json',
                            beforeSend: function(){
                                $('.email-validation-error').remove();
                                $('.form-group').removeClass('error');
                            },
                            data: {
                                email: $("#userEmail").val(),
                                username: $("#username").val(),
                                dob:  $("#datepicker").val()
                            },
                            success:function(){
                                $('.next button').removeClass('is-waiting');
                            },
                            async: false
                        }).responseText;
                        var a = JSON.parse(validate);
                        if(a.r == "fail"){
                            var str = "";
                            $.each(a.fails,function(i,e){
                                $('.form-control[name="' + e + '"]').parent().addClass("error");
                                if(e == "dob"){
                                    str += "Sorry, you must be at least 13 years old";
                                } else {
                                    str += (i>0 ?", and ":"") + "\"" + $('.form-control[name="' + e + '"]').val() + "\" is already being used as " + e;
                                }
                            });
                            $("#detailsForm").prepend("<div class=\"alert alert-danger email-validation-error\" role=\"alert\">" +
                                  "<button class=\"close\" data-dismiss=\"alert\"></button>" +
                                  "<strong>Error: </strong>" + str +
                                "</div>");
                            $('html, body').animate({
                                scrollTop: $('.email-validation-error').offset().top -70
                            }, 'slow');
                            return false;
                        } else {
                            var status = $('#status').val();
                            $.ajax({
                                url: "/register/newMember",
                                type: "POST",
                                dataType: "json",
                                data: $("#detailsForm").serialize() + '&' + $("#detailsForm2").serialize() + '&status=' + status,
                                success:function(d) {
                                    location.href = d.loc;
                                    return false;                                },
                                error:function(){
                                    console.log('error in new member');
                                }
                            });
                            return false;
                        }
                    }
                }
                if(index==3) {
                	//on step 3
                }
            },
            onPrevious: function(tab, navigation, index) {
                console.log("Showing previous tab");
            },
            onInit: function() {
                $('#rootwizard ul').removeClass('nav-pills');
            },
            onTabClick: function(tab, navigation, index) {
                // alert('on tab click disabled');
                return false;
            }
        });

        $('.remove-item').click(function() {
            $(this).parents('tr').fadeOut(function() {
                $(this).remove();
            });
        });

    });

	var getUrlParameter = function getUrlParameter(sParam) {
	    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
	        sURLVariables = sPageURL.split('&'),
	        sParameterName,
	        i;

	    for (i = 0; i < sURLVariables.length; i++) {
	        sParameterName = sURLVariables[i].split('=');

	        if (sParameterName[0] === sParam) {
	            return sParameterName[1] === undefined ? true : sParameterName[1];
	        }
	    }
	};

})(window.jQuery);