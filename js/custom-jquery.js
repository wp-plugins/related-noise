jQuery(document).ready(function($){
	$('#access_feed').on('click',function(){
		var feed_id = $('#feed_id').val();
		var access_token = $('#access_token').val();
		if(feed_id==''){
			$('#access_token').css('border','none');
			$('#feed_id').css('border','1px solid red');
				return false;
		}else if(access_token==''){
			$('#feed_id').css('border','none');
			$('#access_token').css('border','1px solid red');
		return false;
		}
	});
});