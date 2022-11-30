<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace App\Enums;

use JuniWalk\Utils\Enums\LabeledEnum;
use JuniWalk\Utils\Enums\Traits\Labeled;

enum Active: int implements LabeledEnum
{
	use Labeled;

	case Yes = 1;
	case No = 0;


	public static function getLabels(): iterable
	{
		$labels = [];
		$labels[null] = 'web.general.all';

		foreach (self::cases() as $case) {
			$labels[$case->value] = $case->label();
		}

		return $labels;
	}


	public function label(): string
	{
		return match($this) {
			self::Yes => 'web.general.yes',
			self::No => 'web.general.no',
		};
	}
}
