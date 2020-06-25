
/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

function client_init()
{
    'use strict'

	$('select:not(.ajax)').select2({
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
