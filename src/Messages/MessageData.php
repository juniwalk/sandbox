<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\Messages;

use Ublaboo\Mailing\IMessageData;
use Nette\Utils\ArrayHash;

final class MessageData extends ArrayHash implements IMessageData
{
}
