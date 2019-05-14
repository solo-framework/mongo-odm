<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace ODMTests\Entity;

use RuntimeLLC\Mongo\Entity;

class ODMUser extends Entity
{
	public $name = null;

	public $password = null;

	/**
	 * Возвращает имя коллекции, где хранятся сущности этого типа
	 *
	 * @return string
	 */
	public function getCollectionName()
	{
		return "odmuser";
	}
}

