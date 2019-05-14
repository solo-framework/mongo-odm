<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace RuntimeLLC\Mongo;

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Unserializable;

abstract class Entity implements Unserializable
{
	/**
	 * Идентификатор сущности
	 *
	 * @var ObjectId
	 */
	public $id = null;

	/**
	 * Тип массив сущностей
	 */
	const REF_TYPE_ARRAY_ENTITIES = "REF_TYPE_ARRAY_ENTITIES";

	/**
	 * Тип сущность
	 */
	const REF_TYPE_ENTITY = "REF_TYPE_ENTITY";

	/**
	 * Возвращает метаинформацию по связям полей с другими сущностями
	 *
	 * @return array
	 */
	public function getEntityRelations()
	{
//		for example:
//		return array[
//			"author" => array("type" => self::REF_TYPE_ENTITY, "class" => __NAMESPACE__ . "\\Author"),
//			"comments" => array("type" => self::REF_TYPE_ARRAY_ENTITIES, "class" => __NAMESPACE__ . "\\Comment")
//		];

		return [];
	}

	/**
	 * Возвращает имя коллекции, где хранятся сущности этого типа
	 *
	 * @return string
	 */
	public abstract function getCollectionName();

	/**
	 * Constructs the object from a BSON array or document
	 * Called during unserialization of the object from BSON.
	 * The properties of the BSON array or document will be passed to the method as an array.
	 * @link http://php.net/manual/en/mongodb-bson-unserializable.bsonunserialize.php
	 *
	 * @param array $data Properties within the BSON array or document.
	 */
	public function bsonUnserialize(array $data)
	{
		$this->fillEntityWithData($data);
	}

	protected function fillEntityWithData($data)
	{
		$id = $data["_id"];
		unset($data["_id"]);
		$this->id = $id;

		$rels = $this->getEntityRelations();

		foreach ($data as $name => $value)
		{
			if (property_exists($this, $name))
			{
				if (is_array($value) && isset($rels[$name]))
				{
					$this->$name = $this->arrayToObjectRecurively($value, $rels[$name]);
				}
				else
				{
					$this->$name = $value;
				}
			}
		}
	}

	/**
	 * Рекурсивно преобразовывает массив в сущность
	 *
	 * @param mixed $data Данные
	 * @param array $options Опции
	 *
	 * @return Entity|array|null
	 */
	private function arrayToObjectRecurively($data, $options)
	{
		if ($options["type"] == self::REF_TYPE_ENTITY)
		{
			$object = new $options["class"];

			$rels = [];
			if ($object instanceof Entity)
				$rels = $object->getEntityRelations();

			foreach ($data as $name => $value)
			{
				if (property_exists($object, $name))
				{
					if (is_array($value) && isset($rels[$name]))
					{
						$object->$name = $this->arrayToObjectRecurively($value, $rels[$name]);
					}
					else
					{
						$object->$name = $value;
					}
				}
			}
			return $object;
		}
		else if ($options["type"] == self::REF_TYPE_ARRAY_ENTITIES)
		{
			$list = array();
			foreach ($data as $key => $value)
			{
				$list[$key] = $this->arrayToObjectRecurively(
					$value,
					["type" => self::REF_TYPE_ENTITY, "class" => $options["class"]]
				);
			}
			return $list;
		}

		return null;
	}
}