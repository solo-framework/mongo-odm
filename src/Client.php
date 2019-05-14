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

use MongoDB\Collection;

class Client extends \MongoDB\Client
{
	protected $db;
	protected $dbName;

	/**
	 * @var Collection
	 */
	protected $collection = null;

	public function __construct($dbName, $uri = 'mongodb://127.0.0.1/', array $uriOptions = [], array $driverOptions = [])
	{
		parent::__construct($uri, $uriOptions, $driverOptions);

		$this->dbName = $dbName;
		$this->db = $this->selectDatabase($dbName);
	}

	/**
	 * @return \MongoDB\Database
	 */
	public function getDatabase()
	{
		return $this->db;
	}
}

