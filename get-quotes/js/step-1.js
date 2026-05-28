$(document).ready(function(){

	var formSubmitting = false;

	$(window).on('beforeunload', function() {

		alert(window.location.origin != window.location.href)
	    if(formSubmitting === false || window.location.origin != window.location.href){
	        return "You have unsaved changes, do you want to leave without saving?";       
	    }
	});

	$('form').submit(function(){
		formSubmitting = true;
	});

	new Vue({
		el: '#zip',
		data: {
			zip: ""
		},
		methods: {
			processZip: function(e)
			{
				this.resetFormStyles()			

				console.log('keydown')

				if(this.validates())
				{
					console.log('valid')

					$.get('http://ziptasticapi.com/' + this.zip, function(data)
					{			
						var parsed = $.parseJSON(data);

						console.log(data);

						if(typeof parsed.error === "undefined")
						{
							console.log('set cookie');

							$.cookie('location', data, { expires: 7, path: '/' });
												
							$('#city').val(parsed.city);

							$('#state').val(parsed.state);

							$('[name=country]').val(parsed.country);

							$('#zip .form-group')
								.addClass('has-success')
								.removeClass('has-error');

							$('#zip button')
								.removeAttr('disabled')
								.addClass('btn-success active')
								.focus();


						} else 
						{
							$('#zip .form-group')
								.addClass('has-error')
								.removeClass('has-success ');

							$('#zip button')
								.attr('disabled', true)
								.removeClass('btn-success active');

							window.setTimeout(function(){
								$('#zip input').val('')
									.css('color', '#333')
									.removeAttr('style');
								$('#zip .form-group')
									.removeClass('has-error')
							}, 950);
						}
					})
				}						
			},

			validates: function()
			{
				if(this.isNumeric(this.zip) == false)
				{
					this.removeLastCharacter(1)	

					return false;			
				};

				if(this.zip.length < 5) 
				{
					this.removeLastCharacter(this.zip.length - 5);

					return false;
				}

				if(this.zip.length > 5)
				{
					this.removeLastCharacter(1)

					return false;
				}

				return true;
			},

			isNumeric: function(value)
			{
	  			return /^\d+$/.test(value);
			},

			removeLastCharacter: function(amount)
			{
				this.zip = this.zip.substring(0, this.zip.length - amount);			
			},

			resetFormStyles: function()
			{
				$('#zip .form-group')
					.removeClass('has-error has-success');

				$('#zip button')
					.attr('disabled', true)
					.removeClass('btn-success');	
			},

			redirect: function(page)
			{			
				setTimeout(function(){
					window.location.href = page
				}, 1000)					
			},
		}
	});


	$('.paragraph:not(.show)')
			.waypoint(function(direction) {
				$(this.element).addClass('show');
			}, { offset: '80%'});

	var fixedZip = $('.fixed-zip-wrapper .fixed-zip');
	
	var navbarBtm = $('.navbar-fixed-bottom');	

	var lightGrey = $('.light-grey').waypoint(function(direction) {		

		$(fixedZip).toggleClass('show');

		if($(navbarBtm).is(':visible'))
		{
			$(navbarBtm).toggleClass('show');			
		}			
		
	})

	

});

