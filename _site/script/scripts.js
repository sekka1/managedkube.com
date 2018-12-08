$(document).ready(function(){
	console.log('hello');
	$(".send-email").on("click", function() {
	    $.ajax({
	        url: "//formspree.io/amyhwang1218@gmail.com", 
	        method: "POST",
	        data: {message: "hello!"},
	        dataType: "json"
	    });
	});

	// var contactform =  document.getElementById('contactform');
	// contactform.setAttribute('action', '//formspree.io/' + 'amyhwang1218@gmail.com');
});