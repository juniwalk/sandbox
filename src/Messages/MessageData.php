<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Messages;

use Ublaboo\Mailing\IMessageData;
use Nette\Utils\ArrayHash;

final class MessageData extends ArrayHash implements IMessageData
{
}
