<?php

namespace Pop\Debug\Test\Storage;

use Pop\Db\Db;
use Pop\Debug;
use Pop\Debug\Test\Storage\Fixtures;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{

    public function testConstructor()
    {
        chmod(__DIR__ . '/../tmp', 0777);
        touch(__DIR__ . '/../tmp/debug.sqlite');
        chmod(__DIR__ . '/../tmp/debug.sqlite', 0777);
        $db = new Debug\Storage\Database(Db::sqliteConnect(['database' => __DIR__ . '/../tmp/debug.sqlite']));
        $this->assertInstanceOf('Pop\Debug\Storage\Database', $db);
        $this->assertInstanceOf('Pop\Db\Adapter\Sqlite', $db->getDb());
        $this->assertEquals('pop_debug', $db->getTable());
    }

    public function testSave()
    {
        $db = Db::sqliteConnect(['database' => __DIR__ . '/../tmp/debug.sqlite']);

        $debugger = new Debug\Debugger();
        $debugger->addHandler(new Debug\Handler\MessageHandler());
        $debugger->setStorage(new Debug\Storage\Database($db));
        $debugger['message']->addMessage('Hey! Something happened!');
        $debugger->save();

        $db->query('SELECT * FROM pop_debug');
        $rows = $db->fetchAll();

        $this->assertGreaterThan(0, count($rows));
    }

    public function testClear()
    {
        $db = Db::sqliteConnect(['database' => __DIR__ . '/../tmp/debug.sqlite']);

        $debugger = new Debug\Debugger();
        $debugger->addHandler(new Debug\Handler\MessageHandler());
        $debugger->setStorage(new Debug\Storage\Database($db));
        $debugger->clear();

        $db->query('SELECT * FROM pop_debug');
        $rows = $db->fetchAll();

        $this->assertCount(0, $rows);

        unlink(__DIR__ . '/../tmp/debug.sqlite');
    }

    /**
     * Database::save() branches its SQL placeholder style on the adapter's class name (via
     * pop-db's Sql::init(), which stripos()'s it for 'mysql'/'pgsql'/'sqlite'/'sqlsrv'/'pdo').
     * There's no live MySQL/Postgres server in this test environment, so no-op adapter fixtures
     * named after those drivers (see tests/Storage/Fixtures) stand in to exercise the '$' and
     * '?' placeholder branches that the SQLite-backed tests above can't reach (SQLite always
     * resolves to ':').
     */
    public function testSaveWithPgsqlPlaceholder()
    {
        $adapter = new Fixtures\PgsqlTestAdapter();
        $storage = new Debug\Storage\Database($adapter);

        $handler = new Debug\Handler\MessageHandler();
        $handler->addMessage('Hey! Something happened!');

        $storage->save('request-id', 'message', $handler);
        $this->assertStringContainsString('$1', (string)$adapter->lastPreparedSql);
    }

    public function testSaveWithQuestionMarkPlaceholder()
    {
        $adapter = new Fixtures\GenericTestAdapter();
        $storage = new Debug\Storage\Database($adapter);

        $handler = new Debug\Handler\MessageHandler();
        $handler->addMessage('Hey! Something happened!');

        $storage->save('request-id', 'message', $handler);
        $this->assertStringContainsString('?', (string)$adapter->lastPreparedSql);
    }

}
