jQuery(document).ready(function($){
	$('form#codeform').submit(function(e){
		e.preventDefault();
		e.stopPropagation();
		var t = $(this);
		var btn = t.find('input#updatebtn');
		btn.attr('data-original', btn.val());
		btn.val(btn.attr('data-updating'));
		$.ajax({
			method: 'post',
			url: ajaxurl + '?action=phppc_code',
			data: t.serialize()
		}).done(function(r){
			if (r == 1) {
				btn.val(btn.attr('data-updated'));
				t.prepend('<div class="updated" id="phppc_code_msg">Code Snippet Updated</div>');
			} else {
				btn.val(btn.attr('data-failed'));
				t.prepend('<div class="error" id="phppc_code_msg">Could not update snippet, did anything actually change?</div>');
			}
			window.setTimeout(function(){
				btn.val(btn.attr('data-original'));
				$('div#phppc_code_msg').remove();
			},5000);
		});
	});
	$('a#closebtn').click(function(e){
		return confirm('Are you sure you want to go back? unsaved changes will be lost!');
	});
});