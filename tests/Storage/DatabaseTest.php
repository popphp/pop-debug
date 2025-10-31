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

        unlink(__DIR__ . '/../tmp/debug.sqlite');
    }

}
