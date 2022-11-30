<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\LabeledEnum;
use JuniWalk\Utils\Enums\Traits\Labeled;

enum Status: string implements LabeledEnum
{
	use Labeled;

	const Created = 'created';
	const Active = 'active';
    const Finished = 'finished';
    const Invoice = 'invoice';
    const Delete = 'deleted';


	public function label(): string
	{
		return match($this) {
			self::Created => 'web.enum.status.created',
			self::Active => 'web.enum.status.active',
			self::Finished => 'web.enum.status.finished',
			self::Invoice => 'web.enum.status.invoice',
			self::Delete => 'web.enum.status.delete',
		};
	}


	public function color(): Color
	{
		return match($this) {
			self::Created => Color::Warning,
			self::Active => Color::Info,
			self::Finished => Color::Success,
			self::Invoice => Color::Primary,
			self::Delete => Color::Secondary,
		};
	}
}
