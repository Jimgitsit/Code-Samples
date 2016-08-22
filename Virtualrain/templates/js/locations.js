$(document).ready(function(){	
	
	//******************************************//
	//		 Locations Page Animations   		//
	//******************************************//
	
	// New Preferred Location
	$('.newRadio').change(function(){
		console.log($(this).val());
		var data = {action: 'save', preferred: $(this).val()};
		$.post("/locations", data, function(ret) {
			if( ret == 'success' ) {
			}
			else{
			}
		})
		.fail(function(ret){
		})
	})
	
})