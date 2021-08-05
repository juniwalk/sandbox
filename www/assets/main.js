
/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

function client_init()
{
    'use strict'
 
	$('input.datetime').flatpickr({
		dateFormat: "Y-m-d H:i",
		enableTime: true,
		locale: "cs"
	});

	$('input.date').flatpickr({
		dateFormat: "Y-m-d",
		enableTime: false,
		locale: "cs"
	});

	$('select').each(function() {
		var $dropdownParent = $(document.body);
		var $minimumResultsForSearch = 20;

		if ($(this).hasClass('custom-select') || $(this).hasClass('flatpickr-monthDropdown-months')) {
			return;
		}

		if ($(this).hasClass('ajax')) {
			var $ajax = { delay: 250, cache: true };
			var $minimumResultsForSearch = 0;
		}

		if ($(this).closest('.modal').length) {
			var $dropdownParent = $(this).parent().parent()
		}

		$(this).select2({
			ajax: $ajax === undefined ? null : $ajax,
			minimumResultsForSearch: $minimumResultsForSearch,
			templateSelection: select2Format,
			templateResult: select2Format,
			dropdownParent: $dropdownParent
		});
	});

	$("a[data-pwd-toggle]").click(function() {
		$('i.fas', this).toggleClass("fa-eye fa-eye-slash");
		$($(this).data("pwd-toggle")).attr("type", function(k, v) {
			return v == "text" ? "password" : "text";
		});
	});

	$("a[data-clear-input]").click(function() {
		$($(this).data("clear-input")).val('');
	});

	$('[data-toggle="tooltip"]').tooltip();
	$('[data-toggle="popover"]').popover({
		content: function() {
            return $(this).siblings('.popover-content').html();
        },
		trigger: 'focus',
		html: true
	});

	$(document).on('click', '[data-confirm]', function(e) {
		if (!confirm($(e.target).closest('a').attr('data-confirm'))) {
			e.stopPropagation();
			return e.preventDefault();
		}
	});
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


$(function () {
    'use strict'

	client_init();

    $.nette.ext('snippets').after(function () {
		client_init();
    });

    $.nette.init();

});
