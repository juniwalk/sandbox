<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Tools;

use Nette\Localization\ITranslator;
use Nette\Utils\Html;
use Nette\Utils\Strings;

final class StringHelper
{
	/**
	 * @param  string  $content
	 * @param  string|null  $lang
	 * @return string
	 */
	public static function slugify(string $content, string $lang = null): string
	{
		$content = self::transliterate($content, $lang);
		$content = Strings::webalize($content, "'");
		$content = str_replace("'", '', $content);

		return $content;
	}


	/**
	 * @param  string  $string
	 * @param  string|null  $lang
	 * @return string
	 */
	private static function transliterate(string $string, string $lang = null): string
	{
		switch($lang) {
			case 'ru':
				return transliterator_transliterate('Russian-Latin/BGN', $string);
			break;
		}

		return $string;
	}
}
