<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2018
 * @license   MIT License
 */

namespace App\Tools;

use Nette\Localization\ITranslator;
use Nette\Utils\Html;
use Nette\Utils\Strings;

final class HtmlHelper
{
	/**
	 * @var ITranslator|NULL
	 */
	private static $translator;


	/**
	 * @param  ITranslator|NULL  $translator
	 * @return void
	 */
	public static function setTranslator(ITranslator $translator = NULL): void
	{
		static::$translator = $translator;
	}


	/**
	 * @param  float  $value
	 * @param  string  $unit
	 * @return Html
	 */
	public static function createPrice(float $value, string $unit = '%s Kč'): Html
	{
		$value = number_format($value, 2, ',', ' ');
		$unit = static::translate($unit);
		$value = sprintf($unit, $value);

		return Html::el('span class="badge"', $value);
	}


	/**
	 * @param  mixed  $status
	 * @param  bool  $hasIcons
	 * @param  bool  $hasContent
	 * @return Html
	 */
	public static function createStatus($status, bool $hasIcons = TRUE, bool $hasContent = TRUE): Html
	{
		$content = $status ? 'nette.general.yes' : 'nette.general.no';
		$type = $status ? 'success' : 'danger';
		$icon = NULL;

		if ($hasIcons == TRUE) {
			$icon = $status ? 'fa-check' : 'fa-times';
		}

		if ($hasContent == FALSE) {
			$content = '';
		}

		return static::createLabel($content, $type, 'fa-fw '.$icon);
	}


	/**
	 * @param  string  $content
	 * @param  string  $type
	 * @param  string  $icon
	 * @param  nool  $translate
	 * @return Html
	 */
	public static function createLabel(string $content, string $type = 'default', string $icon = NULL, bool $translate = TRUE): Html
	{
		$label = Html::el('span class="label"')->addClass('label-'.$type);
		$content = $translate == TRUE
			? static::$translator->translate($content)
			: $content;

		if (!empty($icon)) {
			$icon = Html::el('i class="fas"')->addClass($icon);
			$label->addHtml($icon)->addText(' ');
		}

		return $label->addHtml(Strings::lower($content));
	}


	/**
	 * @param  float  $size
	 * @param  string|NULL  $icon
	 * @param  string[g  $tresholds
	 * @return Html
	 */
	public static function createSize(float $size, string $icon = NULL, iterable $tresholds = []): Html
	{
		$content = static::formatSize($size);
		$tresholds += [0 => 'default'];

		foreach ($tresholds as $treshold => $type) {
			if ($treshold > $size) {
				continue;
			}

			break;
		}

		return static::createLabel($content, $type, $icon, FALSE);
	}


	/**
	 * @param  string  $url
	 * @param  bool  $isSecured
	 * @return Html
	 */
	public static function createUrl(string $url, bool $isSecured = FALSE): Html
	{
		$icon = Html::el('i class="fa fa-fw"');
		$icon->addClass($isSecured ? 'fa-lock' : 'fa-unlock-alt');

		return Html::el('a target="_blank"')->setHref($url)
			->addClass($isSecured ? 'text-success' : 'text-danger')
			->addHtml($icon)->addText($url);
	}


	/**
	 * @param  string  $content
	 * @return Html
	 */
	public static function createOption(string $content): Html
	{
		$content = static::translate($content);
		$option = Html::el('option', strip_tags($content));

		if ($option->getText() !== $content) {
			$option->data('content', $content);
		}

		return $option;
	}


	/**
	 * @param  string  $value
	 * @param  string  $content
	 * @param  string  $type
	 * @return Html
	 */
	public static function createPopover(string $value, string $content, string $type = 'default'): Html
	{
		return Html::el('button type="button" class="btn btn-'.$type.'"')
			->setHtml(static::translate($value))
			->data('content', static::translate($content))
			->data('container', 'body')
			->data('placement', 'auto')
			->data('toggle', 'popover');
	}


	/**
	 * @param  int  $bytes
	 * @param  int  $decimals
	 * @return string
	 */
	public static function formatSize(int $bytes, int $decimals = 2): string
	{
	    $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
	    $factor = floor((strlen($bytes) - 1) / 3);

		if ($factor <= 0) {
			$decimals = 0;
		}

	    return sprintf(
			'%.'.$decimals.'f '.$size[$factor],
			$bytes / pow(1024, $factor)
		);
	}


	/**
	 * @param  string  $content
	 * @return string
	 */
	public static function translate(string $content): string
	{
		return static::$translator->translate($content);
	}
}
