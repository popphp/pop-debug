<?php

namespace Pop\Debug\Test\Storage;

use Pop\Db\Db;
use Pop\Debug;
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

}
