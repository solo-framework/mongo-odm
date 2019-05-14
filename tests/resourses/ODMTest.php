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

class ODMTest extends \RuntimeLLC\Mongo\Entity
{

	public $name = null;

	public $age = 0;

	/**
	 * @var ODMAddress
	 */
	public $address = null;

	/**
	 * @var ODMUser[]
	 */
	public $users = null;

	/**
	 * Возвращает имя коллекции, где хранятся сущности этого типа
	 *
	 * @return string
	 */
	public function getCollectionName()
	{
		return "odm_test";
	}


	public function getEntityRelations()
	{
		return [
			"address" => ["type" => self::REF_TYPE_ENTITY, "class" => __NAMESPACE__ . "\\ODMAddress"],
			"users"   => ["type" => self::REF_TYPE_ARRAY_ENTITIES, "class" => __NAMESPACE__ . "\\ODMUser"],
		];
	}

}

