<?php

namespace Engelsystem\Test\Unit\Http\SessionHandlers;

use Engelsystem\Application;
use Engelsystem\Database\Database;
use Engelsystem\Database\Migration\Migrate;
use Engelsystem\Database\Migration\MigrationServiceProvider;
use Engelsystem\Http\SessionHandlers\DatabaseHandler;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Query\Builder as QueryBuilder;
use PDO;
use PHPUnit\Framework\TestCase;

class DatabaseHandlerTest extends TestCase
{
    /** @var Database */
    protected $database;

    /**
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::__construct
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::read
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::getQuery
     */
    public function testRead()
    {
        $handler = new DatabaseHandler($this->database);
        $this->assertEquals('', $handler->read('foo'));

        $this->database->insert("INSERT INTO sessions VALUES ('foo', 'Lorem Ipsum', CURRENT_TIMESTAMP)");
        $this->assertEquals('Lorem Ipsum', $handler->read('foo'));
    }

    /**
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::write
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::getCurrentTimestamp
     */
    public function testWrite()
    {
        $handler = new DatabaseHandler($this->database);

        foreach (['Lorem Ipsum', 'Dolor Sit!'] as $data) {
            $this->assertTrue($handler->write('foo', $data));

            $return = $this->database->select('SELECT * FROM sessions WHERE id = :id', ['id' => 'foo']);
            $this->assertCount(1, $return);

            $return = array_shift($return);
            $this->assertEquals($data, $return->payload);
        }
    }

    /**
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::destroy
     */
    public function testDestroy()
    {
        $this->database->insert("INSERT INTO sessions VALUES ('foo', 'Lorem Ipsum', CURRENT_TIMESTAMP)");
        $this->database->insert("INSERT INTO sessions VALUES ('bar', 'Dolor Sit', CURRENT_TIMESTAMP)");

        $handler = new DatabaseHandler($this->database);
        $this->assertTrue($handler->destroy('batz'));

        $return = $this->database->select('SELECT * FROM sessions');
        $this->assertCount(2, $return);

        $this->assertTrue($handler->destroy('bar'));

        $return = $this->database->select('SELECT * FROM sessions');
        $this->assertCount(1, $return);

        $return = array_shift($return);
        $this->assertEquals('foo', $return->id);
    }

    /**
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::gc
     */
    public function testGc()
    {
        $this->database->insert("INSERT INTO sessions VALUES ('foo', 'Lorem Ipsum', '2000-01-01 01:00')");
        $this->database->insert("INSERT INTO sessions VALUES ('bar', 'Dolor Sit', '3000-01-01 01:00')");

        $handler = new DatabaseHandler($this->database);

        $this->assertTrue($handler->gc(60 * 60));

        $return = $this->database->select('SELECT * FROM sessions');
        $this->assertCount(1, $return);

        $return = array_shift($return);
        $this->assertEquals('bar', $return->id);
    }

    /**
     * Setup in memory database
     */
    protected function setUp()
    {
        $dbManager = new CapsuleManager();
        $dbManager->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);

        $connection = $dbManager->getConnection();
        $connection->getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->database = new Database($connection);

        $app = new Application();
        $app->instance(Database::class, $this->database);
        $app->register(MigrationServiceProvider::class);

        /** @var Migrate $migration */
        $migration = $app->get('db.migration');

        $migration->initMigration();

        $this->getQuery()
            ->insert([
                ['migration' => '2018_01_01_000001_import_install_sql'],
                ['migration' => '2018_01_01_000002_import_update_sql'],
            ]);

        $migration->run(__DIR__ . '/../../../../db/migrations');
    }

    /**
     * @return QueryBuilder
     */
    protected function getQuery(): QueryBuilder
    {
        return $this->database
            ->getConnection()
            ->table('migrations');
    }
}
