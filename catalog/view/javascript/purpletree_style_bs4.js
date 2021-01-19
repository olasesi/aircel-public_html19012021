// Image Manager
	$(document).on('click', 'a[data-toggle=\'image\']', function(e) {
		var $element = $(this);
		var $popover = $element.data('bs.popover'); // element has bs popover?
		
		e.preventDefault();

		// destroy all image popovers
		$('a[data-toggle="image"]').popover('dispose');

		// remove flickering (do not re-add popover when clicking for removal)
		if ($popover) {
			return;
		}

		$element.popover({
			html: true,
			placement: 'right',
			trigger: 'manual',
			content: function() {
				return '<button type="button" id="pts-button-image" class="pts-btn pts-btn-primary"><i class="fa fa-pencil fas fa-edit"></i></button> <button type="button" id="pts-button-clear" class="pts-btn pts-btn-danger"><i class="fa fa-trash-o fas fa-trash-alt"></i></button>';
			}
		});

		$element.popover('show');

		$('#pts-button-image').on('click', function() {
			var $button = $(this);
			var $icon   = $button.find('> i');
			
			$('#modal-image').remove();
			var seller_id = ($('input[name=\'seller_id\']').val());
			var seller_name = ($('input[name=\'seller_name\']').val());
			$.ajax({
				url: 'index.php?route=extension/common/filemanager&target=' + $element.parent().find('input').attr('id') + '&thumb=' + $element.attr('id')+'&seller_id=' + seller_id +'&seller_name=' + seller_name,
				dataType: 'html',
				beforeSend: function() {
					$button.prop('disabled', true);
					if ($icon.length) {
						$icon.attr('class', 'fa fa-circle-o-notch fa-spin');
					}
				},
				complete: function() {
					$button.prop('disabled', false);
					if ($icon.length) {
						$icon.attr('class', 'fa fa-pencil');
					}
				},
				success: function(html) {
					$('body').append('<div id="modal-image" class="modal">' + html + '</div>');

					$('#modal-image').modal('show');
				}
			});

			$element.popover('dispose');
		});

		$('#pts-button-clear').on('click', function() {
			$element.find('img').attr('src', $element.find('img').attr('data-placeholder'));

			$element.parent().find('input').val('');

			$element.popover('dispose');
		});
	});