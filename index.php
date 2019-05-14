<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

require_once "./vendor/autoload.php";

//"mongodb://192.168.11.50:27017/citycard"
$opts = [
//	"ssl" => false,
	"authSource" => "citycard",
	"authMechanism" => "SCRAM-SHA-1",
	"username" => "user",
	"password" => "password",
];

$connectionString = "mongodb://odmtest:odmtest@192.168.11.50:27017/odmtest?authSource=odmtest&quot;authMechanism=SCRAM-SHA-1";
$m = new \MongoDB\Client($connectionString);


//
//foreach ($idx as $i)
//{
//	print_r($i->getName());
//}

exit();


//$m->watch()


class Opts// extends ArrayIterator
{
	public $projection = [];

	public $skip = 0;

	public $limit = 0;
}


class User implements MongoDB\BSON\Unserializable
{
	public $email = null;

	public $bankCards = [];



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
		// TODO: Implement bsonUnserialize() method.
//		print_r($data);
//		print_r("\n-----\n");
//		$this->email = $data['email'];
//		$this->bankCards = $data['bankcards'];

//		$this = (array)$data;

		foreach ($data as $index => $datum)
		{
			$this->{$index} = $datum;
		}

	}
}
//
//$opts = new Opts();
//$opts->limit = 5;

//$res = $m->selectCollection("citycard", "user")->findOne(["email" => "pup-hidden@mailmail.ru"]);


//$r = (array)$opts;
//print_r($r);
//exit();

//$res = $m->getManager()->getReadConcern()->getLevel();

$typeMap = [
	'array' => 'array', //'MongoDB\Model\BSONArray',
	'document' => 'array',//'MongoDB\Model\BSONArray',//'MongoDB\Model\BSONDocument',
	'root' => 'User'//'MongoDB\Model\BSONDocument',
];

$opts["typeMap"] = $typeMap;

/** @var $res User[] */
$res = $m->selectCollection("citycard", "user")->findOne(["email" => "ARo-hidden@mail.ru"], $opts);//->toArray();

//$m->selectDatabase()->selectCollection()->

print_r($res);// ->bankCards[1]

exit();


$manager = new MongoDB\Driver\Manager("mongodb://192.168.11.50:27017/citycard", [
//	"ssl" => false,
	"authSource" => "citycard",
	"authMechanism" => "SCRAM-SHA-1",
	"username" => "user",
	"password" => "password",
]);

$query = new \MongoDB\Driver\Query([]);


$res = $manager->executeQuery("citycard.user", $query);
print_r($res->setTypeMap());

//$manager->executeCommand("citycard", new \MongoDB\Driver\Command());

//print_r($manager->getServers());

//
//$cmd = new \MongoDB\Driver\Command([]);
//
//print_r($client->executeCommand("", $cmd));

//print_r(get_loaded_extensions());


//print_r(date("dmY-H:i:s"));


//$dt = new DateTime("now", new DateTimeZone("Europe/Moscow"));
$dt = new DateTime("now");
print_r($dt);

print_r(time());

//print_r(DateTimeZone::);
//$oid = new \MongoDB\BSON\ObjectId();

