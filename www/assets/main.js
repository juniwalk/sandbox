
/**
 * @author    Design Point, s.r.o. <info@dpoint.cz>
 * @copyright Design Point, s.r.o. (c) 2018
 * @license   MIT License
 */

function client_init()
{
    'use strict'
}

(function ($) {
    'use strict'

	client_init();

    $.nette.ext('snippets').after(function () {
        client_init();
    });

    $.nette.init();

})(jQuery)
