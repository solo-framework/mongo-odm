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
use MongoDB\Collection;

abstract class EntityManager
{
	protected $collectionName = null;

	/**
	 * @var Collection
	 */
	protected $collection = null;

	/**
	 * Name of PHP class that describes an entity for this manager
	 *
	 * @var null|string
	 */
	protected $entityClassName = null;

	protected $typeMap = [
		'array' => 'array', //'MongoDB\Model\BSONArray',
		'document' => 'array',//'MongoDB\Model\BSONArray',//'MongoDB\Model\BSONDocument',
		'root' => null//'MongoDB\Model\BSONDocument',
	];


	/**
	 * Options for creating collection
	 *
	 * @return array
	 */
	public function getCollectionOptions()
	{
		$opts["typeMap"] = $this->typeMap;
		return $opts;
	}


	public function __construct()
	{
		$this->entityClassName = $this->getEntityClassName();
		if (!$this->entityClassName)
			throw new \RuntimeException("You should define entity class name in getEntityClassName() for " . get_called_class());

		/** @var $inst Entity */
		$inst = new $this->entityClassName;
		$this->typeMap["root"] = $this->entityClassName;
		$this->collectionName = $inst->getCollectionName();

		if ($this->collectionName)
		{
			$this->collection = $this->getClient()->getDatabase()->selectCollection($this->collectionName, $this->getCollectionOptions());
		}
	}

	/**
	 * Returns collection object
	 *
	 * @return Collection
	 */
	public function getCollection()
	{
		return $this->collection;
	}

	/**
	 * Returns client
	 *
	 * @return Client
	 */
	abstract public function getClient();

	/**
	 * Gets full name of entity class name
	 *
	 * @return null | string
	 */
	public function getEntityClassName()
	{
		$parts = explode("\\", get_called_class());
		$len = 7; // lenth of "Manager" word
		$entityName = substr(array_pop($parts), 0, -$len);
		$ns = implode("\\", $parts);

		return "{$ns}\\Entity\\{$entityName}";
	}

	/**
	 * Gets entity by Id
	 * @see FindOne::__construct() for supported options
	 *
	 * @param $entityId
	 *
	 * @return array|null|object
	 */
	public function findById($entityId)
	{
		$entityId = new ObjectId($entityId);

		$opts["typeMap"] = $this->typeMap;
		$res = $this->collection->findOne(["_id" => $entityId], $opts);
		return $res;
	}

	/**
	 * To save an entity
	 *
	 * @see UpdateOne::__construct() for supported options
	 *
	 * @param Entity $entity
	 * @param array $options
	 *
	 * @return Entity
	 */
	public function save(Entity $entity, $options = [])
	{
		$doc = (array)$entity;

		if (!isset($doc["id"]))
			$id = new ObjectId();
		else
			$id = new ObjectId($doc["id"]);

		unset($doc["id"]);

		$options["upsert"] = true;
		$res = $this->collection->updateOne(["_id" => $id], ['$set' => $doc], $options);

		$entity->id = $res->getUpsertedId();
		return $entity;
	}

	/**
	 * Removes an entity by id
	 *
	 * @see DeleteOne::__construct() for supported options
	 *
	 * @param $entityId
	 * @param array $options
	 *
	 * @return \MongoDB\DeleteResult
	 */
	public function removeById($entityId, $options = [])
	{
		return $this->collection->deleteOne(["_id" => new ObjectId($entityId)], $options);
	}

	/**
	 * Removes an entity by condition
	 *
	 * @param $condition
	 * @param array $options
	 *
	 * @return \MongoDB\DeleteResult
	 */
	public function remove($condition, $options = [])
	{
		return $this->collection->deleteMany($condition, $options);
	}

	/**
	 * Updates an entity by id
	 *
	 * @see UpdateOne::__construct() for supported options
	 *
	 * @param $entityId
	 * @param $update
	 * @param array $options
	 *
	 * @return \MongoDB\UpdateResult
	 */
	public function updateById($entityId, $update, array $options = [])
	{
		return $this->collection->updateOne(
			["_id" => new ObjectId($entityId)],
			$update,
			$options
		);
	}

	/**
	 * Updates an entity by condition
	 *
	 * @see UpdateMany::__construct() for supported options
	 *
	 * @param $condition
	 * @param $update
	 * @param array $options
	 *
	 * @return \MongoDB\UpdateResult
	 */
	public function update($condition, $update, array $options = [])
	{
		return $this->collection->updateMany($condition, $update, $options);
	}

	/**
	 * To find entities by condition
	 *
	 * @see Find::__construct() for supported options
	 *
	 * @param $filter
	 * @param array $options
	 *
	 * @return \MongoDB\Driver\Cursor
	 */
	public function find($filter, array $options = [])
	{
		return $this->collection->find($filter, $options);
	}

	/**
	 * Counts entities by condition
	 *
	 * @see CountDocuments::__construct() for supported options
	 *
	 * @param array $filter
	 * @param array $options
	 *
	 * @return int
	 */
	public function count($filter = [], array $options = [])
	{
		return $this->collection->countDocuments($filter, $options);
	}

	/**
	 * Создает по списку строковых идентификаторов список ObjectId
	 *
	 * @param array $ids Список идентификатров
	 *
	 * @return array
	 */
	public static function buildObjectIdList(array $ids)
	{
		$func = function($val) {
			return new ObjectId($val);
		};

		return array_map($func, $ids);
	}

	/**
	 * Returns only one field value
	 *
	 * @see FindOne::__construct() for supported options
	 *
	 * @param $condition
	 * @param $name
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function fetchField($condition, $name, $options = [])
	{
		$options["projection"][$name] = true;
		$options["projection"]["_id"] = false;
		$options["typeMap"] = [
			'array'    => 'array',
			'document' => 'array',
			'root'     => 'array'
		];

		$res = $this->collection->findOne($condition, $options);

		if (!$res || count($res) == 0)
			return null;

		return $res[$name];
	}

	/**
	 * Returns values of documetns field (like a column in RDBS)
	 *
	 * @see Find::__construct() for supported options
	 *
	 * @param $condition
	 * @param $name
	 * @param array $options
	 *
	 *
	 * @return \Generator|null
	 */
	public function fetchColumn($condition, $name, $options = [])
	{
		$options["projection"][$name] = true;
		$options["projection"]["_id"] = false;
		$options["typeMap"] = [
			'array'    => 'array',
			'document' => 'array',
			'root'     => 'array'
		];


		$res = $this->collection->find($condition, $options);
		if (!$res)
			return null;

		foreach ($res as $item)
		{
			yield $item[$name];
		}
	}
}

