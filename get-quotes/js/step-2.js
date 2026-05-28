// $.fn.togglePlaceholder = function() {
// 	return this.each(function() {
// 		$(this)
// 			.data("holder", $(this).attr("placeholder"))
// 			.focusin(function() {
// 				$(this).attr('placeholder', '');
// 			})
// 			.focusout(function() {
// 				$(this).attr('placeholder', $(this).data('holder'));
// 			});
// 	});
// };
// $("[placeholder]").togglePlaceholder();
jQuery.validator.addMethod("email", function(value, element) {
	var emailRegex = /^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
	return emailRegex.test(value);
}, "Enter Valid Email");
jQuery.extend(jQuery.validator.messages, {
	required: "Required"
});
jQuery.validator.addMethod("alpha", function(value, element) {
	alpha = /^[a-zA-Z ]*$/;
	return alpha.test(value);
}, "Letters Only");
jQuery.validator.addMethod("zipcode", function(value, element) {
	setLocationFromZip(value)
	return alpha.test(value);
}, "Enter Valid Zipcode");
jQuery.validator.addMethod("phone", function(value, element) {
	if (value.length > 10) {
		if (value.charAt(0) == 1) {
			value = value.substr(1)
		}

		var value = value.replace(/\D/ig, '');

		$(element).val(value)
	}
	var list = [201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 224, 225, 226, 228, 229, 231, 234, 236, 239, 240, 242, 246, 248, 250, 251, 252, 253, 254, 256, 260, 262, 264, 267, 268, 269, 270, 272, 276, 278, 281, 283, 284, 289, 301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 311, 312, 313, 314, 315, 316, 317, 318, 319, 320, 321, 323, 325, 330, 331, 334, 336, 337, 339, 340, 341, 343, 345, 347, 351, 352, 360, 361, 365, 369, 380, 385, 386, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 418, 419, 423, 424, 425, 430, 431, 432, 434, 435, 437, 438, 440, 441, 442, 443, 450, 456, 464, 469, 470, 473, 475, 478, 479, 480, 481, 484, 500, 501, 502, 503, 504, 505, 506, 507, 508, 509, 510, 511, 512, 513, 514, 515, 516, 517, 518, 519, 520, 530, 539, 540, 541, 548, 551, 557, 559, 561, 562, 563, 564, 567, 570, 571, 573, 574, 575, 579, 580, 585, 586, 587, 601, 602, 603, 604, 605, 606, 607, 608, 609, 610, 611, 612, 613, 614, 615, 616, 617, 618, 619, 620, 623, 626, 627, 628, 629, 630, 631, 636, 639, 641, 646, 647, 649, 650, 651, 657, 660, 661, 662, 664, 669, 670, 671, 678, 679, 681, 682, 684, 689, 700, 701, 702, 703, 704, 705, 706, 707, 708, 709, 710, 711, 712, 713, 714, 715, 716, 717, 718, 719, 720, 721, 724, 725, 727, 731, 732, 734, 737, 740, 747, 754, 757, 758, 760, 762, 763, 764, 765, 767, 769, 770, 772, 773, 774, 775, 778, 779, 780, 781, 782, 784, 785, 786, 787, 800, 801, 802, 803, 804, 805, 806, 807, 808, 809, 810, 811, 812, 813, 814, 815, 816, 817, 818, 819, 822, 825, 828, 829, 830, 831, 832, 833, 835, 843, 844, 845, 847, 848, 849, 850, 855, 856, 857, 858, 859, 860, 862, 863, 864, 865, 866, 867, 868, 869, 870, 872, 873, 876, 877, 878, 880, 881, 882, 888, 898, 900, 901, 902, 903, 904, 905, 906, 907, 908, 909, 910, 912, 913, 914, 915, 916, 917, 918, 919, 920, 925, 927, 928, 929, 931, 935, 936, 937, 939, 940, 941, 947, 949, 951, 952, 954, 956, 957, 959, 970, 971, 972, 973, 975, 976, 978, 979, 980, 984, 985, 989];

	var area = value.substring(0, 3);
	if (list.indexOf(parseInt(area)) == -1) {
		return false;
	}
	return value.length == 10
}, "Invalid Phone");
$.validator.addMethod("check_date_of_birth", function (value, element) {
    
	var dateOfBirth = value;
	var arr_dateText = dateOfBirth.split("/");
	day = arr_dateText[0];
	month = arr_dateText[1];
	year = arr_dateText[2];
	
	var mydate = new Date();
	mydate.setFullYear(year, month - 1, day);
	
	var maxDate = new Date();
	maxDate.setFullYear(maxDate.getFullYear() - 18);
	
	var minDate = new Date();
	minDate.setYear(minDate.getYear() - 100);
	
	if (maxDate < mydate) {
			$.validator.messages.check_date_of_birth = "You must be at least 18 years old";
			return false;
	}
	if (minDate > mydate) {
			$.validator.messages.check_date_of_birth = "Please enter a valid birthdate";
			return false;
	}
	return true;
});
$.validator.setDefaults({
	highlight: function(element) {
		$(element).closest('.form-group').addClass('has-error');
	},
	unhighlight: function(element) {
		$(element).closest('.form-group').removeClass('has-error');
	},
	showErrors: function(errorMap, errorList) {
		$('.alert').attr('data-errors', this.numberOfInvalids()).show().html("Your form contains " + this.numberOfInvalids() + " errors, see details below.");
		this.defaultShowErrors();
	},
	errorElement: 'span',
	errorClass: 'help-block',
	errorPlacement: function(error, element) {
		if (element.parent('.input-group').length) {
			error.insertAfter(element.parent());
		} else {
			error.insertAfter(element);
		}
	}
});
String.prototype.capitalize = function() {
	return this.toLowerCase().replace(/\b\w/g, function(m) {
		return m.toUpperCase();
	});
};

function setLocationFromZip(zip) {
	$.get('https://ziptasticapi.com/' + zip, function(data) {
		var parsed = $.parseJSON(data);
		if (typeof parsed.error === 'undefined') {
			$('[name=Zip]').val(zip);
		} else {
			if (!fromZipInUrl) {
				alert('Invalid Zip')
				$('[name=Zip]').val('');
			}
		}
	})
}
$(document).ready(function() {
	$('[data-toggle="tooltip"]').tooltip();
	$('#form form').validate({
		rules: {
			Email: {
				required: true,
				email: true
			},
			First_Name: {
				required: true,
				alpha: true
			},
			Last_Name: {
				required: true,
				alpha: true
			},
			Primary_Phone: {
				phone: true
			},
			City: {
				alpha: true
			},
			DOB:{
				check_date_of_birth: true
			}
		},
		messages: {
			required: "Required"
		}
	});
});
var formSubmitting = false;
$('form').submit(function(e) {
	formSubmitting = true;
});
$(window).on('beforeunload', function() {
	if (formSubmitting === false) {
		return "You have unsaved changes, do you want to leave without saving?";
	}
});
$('.toggle-button').click(function() {
	var data = $(this).data('toggle-button').split(':');
	$('input[name="' + data[0] + '"]').val(data[1]);
});
$('.condition-toggle .toggle-button').click(function() {
	$('.conditions.dropdown').toggle();
});
$('.age').change(function() {
	var url = $('input[name="Redirect_URL"]').val()
	newUrl = url.replace(/&Age=\d+/, '&Age=' + this.value);
	$('input[name="Redirect_URL"]').val(newUrl);
});
$('.household').change(function() {
	var url = $('input[name="Redirect_URL"]').val()
	newUrl = url.replace(/&Household=\w+/, '&Household=' + this.value);
	$('input[name="Redirect_URL"]').val(newUrl);
});
$('.conditions input[type="checkbox"]').change(function() {
	var value = $('input[name="Preexisting_List"]').val();
	var condition = $(this).data('condition');
	var spacer = value == '' ? '' : ', ';
	if ($(this).is(':checked')) {
		$('input[name="Preexisting_List"]').val(value + spacer + condition);
	} else {
		$('input[name="Preexisting_List"]').val(value.replace(spacer + condition, ''));
	}
});
$('.done').on('click', function() {
	$('.conditions.open').removeClass('open');
	var checked = $('.conditions').find(':checked').length;
	$('.selectDropdown').html(checked + ' Selected <span class="caret"></span>');
});
$('.conditions.dropdown .dropdown-menu').on('click', function(e) {
	e.stopPropagation();
});
$('[name="First_Name"]').focus();