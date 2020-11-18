
/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

function client_init()
{
    'use strict'

	$('select:not(.ajax):not(.custom-select)').select2({
		minimumResultsForSearch: 20,
		templateSelection: select2Format,
		templateResult: select2Format,
		tokenSeparators: [],
		tags: false
	});

	$('select.select2.ajax').select2({
		ajax: {delay: 250, cache: true},
		templateSelection: select2Format,
		templateResult: select2Format,
		tokenSeparators: []
	});

	$("a[data-pwd-toggle]").click(function() {
		$('i.fas', this).toggleClass("fa-eye fa-eye-slash");
		$($(this).data("pwd-toggle")).attr("type", function(k, v) {
			return v == "text" ? "password" : "text";
		});
	});

	$(document).on('click', '[data-confirm]', function(e) {
		if (!confirm($(e.target).closest('a').attr('data-confirm'))) {
			e.stopPropagation();
			return e.preventDefault();
		}
	});

	$('[data-toggle="popover"]').popover();
	$('[data-toggle="tooltip"]').tooltip();
}


/**
 * @param  object  state
 * @return string
 */
function select2Format(state)
{
	var $option = $(state.element);
	var $value = null;

	if ($value = $option.data('content')) {
		return $('<span>').html($value);
	}

	if ($value = $option.data('icon')) {
		return $('<span><i class="fa '+ $value +'"></i> '+ state.text +'</span>');
	}

	return state.text;
}


(function ($) {
    'use strict'

	client_init();

    $.nette.ext('snippets').after(function () {
        client_init();
    });

    $.nette.init();

})(jQuery)
