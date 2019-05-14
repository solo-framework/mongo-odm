<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace RuntimeLLC\Tests;

use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use MongoDB\Exception\InvalidArgumentException;
use ODMTests\Entity\ODMAddress;
use ODMTests\Entity\ODMOrg;
use ODMTests\Entity\ODMTest;
use ODMTests\Entity\ODMUser;
use ODMTests\ODMTestManager;
use PHPUnit\Framework\TestCase;
use RuntimeLLC\Mongo\EntityManager;

class EntityManagerTest extends TestCase
{
	/**
	 * @var ODMTestManager
	 */
	public $manager = null;

	protected function setUp()
	{
		$this->manager = new ODMTestManager();
	}

	protected function tearDown()
	{
		$this->manager->getClient()->selectCollection("odmtest", "odm_test")->drop();
	}

	public function testGetConnection()
	{
		$client = $this->manager->getClient();
		$this->assertTrue($client instanceof Client);
	}

	public function testGetEntityClassName()
	{
		$this->assertEquals('ODMTests\Entity\ODMTest', $this->manager->getEntityClassName());
	}


	public function testSave()
	{
		$ent = new ODMTest();
		$ent->name = "some name";
		$ent->age = 100;
		$saved = $this->manager->save($ent);

		$this->assertNotNull($saved);
		$this->assertInstanceOf('ODMTests\Entity\ODMTest', $saved);
	}

	public function testObjectId()
	{
		$strId = "5cb6cf1440f72c0001746242";
		$id = new ObjectId($strId);

		$this->assertEquals($strId, $id->__toString());
		$this->assertInstanceOf('MongoDB\BSON\ObjectId', $id);
	}


	public function testUpdateViaSave()
	{
		$ent = new ODMTest();
		$ent->name = "some name";
		$ent->age = 300;
		$saved = $this->manager->save($ent);

		$saved->age = 500;
		$savedAgain = $this->manager->save($ent);

		$this->assertEquals(500, $savedAgain->age);
		$this->assertEquals($saved->id, $savedAgain->id);
	}


	public function testFindById()
	{
		$ent = new ODMTest();
		$ent->name = "test name";
		$ent->age = 1000;

		$address = new ODMAddress();
		$address->building = 45;
		$address->street = "Lenina street";

		$ent->address = $address;

		for ($i = 1; $i <= 2; $i++)
		{
			$user = new ODMUser();
			$user->name = "name{$i}";
			$user->password = "password";
			$ent->users[] = $user;
		}

		$org = new ODMOrg();
		$org->inn = "12151540043";
		$org->ogrn = "123435451423654634";
		$ent->address->organizations[] = $org;

		$saved = $this->manager->save($ent);

		$res = $this->manager->findById($saved->id);

		$this->assertNotNull($res);
		$this->assertEquals(1000, $res->age);

		$this->assertCount(2, $res->users);
		$this->assertEquals(45, $res->address->building);

		$this->assertCount(1, $res->address->organizations);
		$this->assertEquals("12151540043", $res->address->organizations[0]->inn);
	}

	public function testFindByIdNotFound()
	{
		$strId = "5cb6cf1440f72c0001746242";
		$saved = $this->manager->findById($strId);
		$this->assertNull($saved);
	}


	public function testFindByIdWithNull()
	{
		$strId = null;
		$saved = $this->manager->findById($strId);
		$this->assertNull($saved);
	}

	public function testRemoveById()
	{
		$ent = new ODMTest();
		$ent->name = "test name";
		$ent->age = 1000;
		$saved = $this->manager->save($ent);

		$res = $this->manager->removeById($saved->id);

		$find = $this->manager->findById($saved->id);
		$this->assertNull($find);
	}

	public function testRemoveByCondition()
	{
		$ent = new ODMTest();
		$ent->name = "test name";
		$ent->age = 1000;
		$saved = $this->manager->save($ent);

		$ent = new ODMTest();
		$ent->name = "test name 2";
		$ent->age = 1000;
		$saved = $this->manager->save($ent);

		$res = $this->manager->remove(["age" => 1000]);

		$this->assertEquals(2, $res->getDeletedCount());
		$this->assertEquals(0, count($this->manager->getCollection()->find()->toArray()));
	}


	public function testUpdateById()
	{
		$ent = new ODMTest();
		$ent->name = "test name";
		$ent->age = 1000;
		$saved = $this->manager->save($ent);

		$res = $this->manager->updateById($saved->id, ['$set' => ["age" => 10000]]);

		$this->assertEquals(1, $res->getModifiedCount());

		$stored = $this->manager->findById($saved->id);
		$this->assertEquals(10000, $stored->age);
	}

	public function testUpdate()
	{
		$ent = new ODMTest();
		$ent->name = "test name";
		$ent->age = 1000;
		$saved = $this->manager->save($ent);

		$condition = ["age" => 1000];
		$update = [
			'$inc' => ["age" => 10],
			'$set' => ["name" => "another name"]
		];
		$this->manager->update($condition, $update);

		$upd = $this->manager->findById($saved->id);

		$this->assertEquals(1010, $upd->age);
		$this->assertEquals("another name", $upd->name);
	}

	public function testFind()
	{
		for ($i = 0; $i < 5; $i++)
		{
			$ent = new ODMTest();
			$ent->name = "test name {$i}";
			$ent->age = 1000 + $i;
			$saved = $this->manager->save($ent);
		}

		$res = $this->manager->find(["age" => ['$gte' => 1001]]);

		$c = 0;
		foreach ($res as $item)
		{
			$c++;
			$this->assertEquals(1000 + $c, $item->age);
		}
	}

	public function testCount()
	{
		for ($i = 0; $i < 5; $i++)
		{
			$ent = new ODMTest();
			$ent->name = "test name {$i}";
			$ent->age = 1000 + $i;
			$this->manager->save($ent);
		}

		$res = $this->manager->count(["age" => ['$gte' => 1001]]);

		$this->assertEquals(4, $res);

	}

	public function testBuildObjectIdList()
	{
		$ids = [
			"5cc2dc2c7258ab0001585b02",
			"5cc2dc2c7258ab0001585b03",
			"5cc2dc2c7258ab0001585b04",
			"5cc2dc2c7258ab0001585b05",
		];

		$res = EntityManager::buildObjectIdList($ids);

		$this->assertIsArray($res);

		foreach ($res as $id)
		{
			$this->assertInstanceOf('MongoDB\BSON\ObjectId', $id);
		}
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testBuildObjectIdList_fail()
	{
		$ids = [
			"wrong5cc2dc2c7258ab0001585b",
			"wrong5cc2dc2c7258ab000158503",
			"wrong5cc2dc2c7258ab1585b04",
			"wrong5cc2dc2c7250001585b05",
		];

		$res = EntityManager::buildObjectIdList($ids);

		$this->assertIsArray($res);

		foreach ($res as $id)
		{
			$this->assertInstanceOf('MongoDB\BSON\ObjectId', $id);
		}
	}

	public function testFetchField()
	{
		$ent = new ODMTest();
		$ent->name = "test name";
		$ent->age = 1000;
		$this->manager->save($ent);

		$res = $this->manager->fetchField(["age" => 1000], "name");

		$this->assertEquals("test name", $res);

		$res = $this->manager->fetchField(["age" => 1000], "non_exists_field");

		$this->assertNull($res);
	}

	public function testFetchColumn()
	{
		for ($i = 0; $i < 5; $i++)
		{
			$ent = new ODMTest();
			$ent->name = "test name {$i}";
			$ent->age = 1000 + $i;
			$this->manager->save($ent);
		}

		$res = $this->manager->fetchColumn([], "age");

		$c = 0;
		foreach ($res as $item)
		{
			$this->assertEquals(1000 + $c, $item);
			$c++;
		}
	}
}
