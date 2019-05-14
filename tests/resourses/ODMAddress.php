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

class ODMAddress extends Entity
{
	public $street = null;

	public $building = null;

	/**
	 * @var ODMOrg[]
	 */
	public $organizations = null;

	/**
	 * Возвращает имя коллекции, где хранятся сущности этого типа
	 *
	 * @return string
	 */
	public function getCollectionName()
	{
		return "odmaddress";
	}

	public function getEntityRelations()
	{
		return [
			"organizations" => ["type" => self::REF_TYPE_ARRAY_ENTITIES, "class" => __NAMESPACE__ . "\\ODMOrg"],
		];
	}
}

