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
use ODMTests\Entity\ODMTest;
use RuntimeLLC\Mongo\Entity;

/**
 * Class ODMTestManager
 * @package ODMTests
 *
 * @method ODMTest findById($entityId)
 * @method ODMTest save(Entity $entity, $options = [])
 */
class ODMTestManager extends BaseODMTestManager
{

	/**
	 * Gets full name of entity class name
	 *
	 * @return null | string
	 */
//	public function getEntityClassName()
//	{
//		print_r($this->mana);
//	}

}

