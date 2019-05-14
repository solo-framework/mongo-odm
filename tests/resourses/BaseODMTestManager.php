<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace ODMTests;



use RuntimeLLC\Mongo\Client;

class BaseODMTestManager extends \RuntimeLLC\Mongo\EntityManager
{

	/**
	 *
	 * @param mixed $options Параметры подключения к БД
	 *
	 * @return Client
	 */
	public function getClient()
	{
		return new Client($GLOBALS["mongo.dbname"], $GLOBALS["mongo.server"]);
	}
}

