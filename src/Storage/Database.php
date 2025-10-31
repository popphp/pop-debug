<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Debug\Storage;

use Pop\Db\Adapter\AbstractAdapter;
use Pop\Debug\Handler\AbstractHandler;

/**
 * Debug database storage class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Database extends AbstractStorage
{

    /**
     * DB adapter
     * @var ?AbstractAdapter
     */
    protected ?AbstractAdapter $db = null;

    /**
     * Table
     * @var string
     */
    protected string $table = 'pop_debug';

    /**
     * Constructor
     *
     * Instantiate the DB writer object
     *
     * The DB table requires the following fields at a minimum:

     *     id    INT
     *     value TEXT, VARCHAR, etc.
     *
     * @param  AbstractAdapter $db
     * @param  string          $table
     */
    public function __construct(AbstractAdapter $db, string $table = 'pop_debug')
    {
        $this->setDb($db);
        $this->setTable($table);

        if (!$db->hasTable($this->table)) {
            $this->createTable();
        }
    }

    /**
     * Set the current debug db adapter.
     *
     * @param  AbstractAdapter $db
     * @return Database
     */
    public function setDb(AbstractAdapter $db): Database
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Get the current debug db adapter.
     *
     * @return ?AbstractAdapter
     */
    public function getDb(): ?AbstractAdapter
    {
        return $this->db;
    }

    /**
     * Get the current debug db table.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Set the debug db table
     *
     * @param  string $table
     * @return Database
     */
    public function setTable(string $table): Database
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Save debug data
     *
     * @param  string          $id
     * @param  string          $name
     * @param  AbstractHandler $handler
     * @return void
     */
    public function save(string $id, string $name, AbstractHandler $handler): void
    {
        $sql = $this->db->createSql();
        $sql->reset();
        $placeholder = $sql->getPlaceholder();

        if ($placeholder == ':') {
            $placeholders = [
                'key'     => ':key',
                'handler' => ':handler',
                'start'   => ':start',
                'end'     => ':end',
                'elapsed' => ':elapsed',
                'message' => ':message',
                'context' => ':context',
            ];
        } else if ($placeholder == '$') {
            $placeholders = [
                'key'     => '$1',
                'handler' => '$2',
                'start'   => '$3',
                'end'     => '$4',
                'elapsed' => '$5',
                'message' => '$6',
                'context' => '$7',
            ];
        } else {
            $placeholders = [
                'key'     => '?',
                'handler' => '?',
                'start'   => '?',
                'end'     => '?',
                'elapsed' => '?',
                'message' => '?',
                'context' => '?',
            ];
        }

        $sql->insert($this->table)->values($placeholders);
        $params = [
            'key'       => $id,
            'handler'   => $name,
            'start'     => $handler->getStart(),
            'end'       => $handler->getEnd(),
            'elapsed'   => $handler->getElapsed(),
            'message'   => $handler->prepareMessage(),
            'context'   => json_encode($handler->prepare()),
        ];

        // Save value
        $this->db->prepare($sql)
            ->bindParams($params)
            ->execute();
    }

    /**
     * Get debug data by ID
     *
     * @param  string  $id
     * @param  ?string $name
     * @return mixed
     */
    public function getById(string $id, ?string $name = null): mixed
    {
        $sql         = $this->db->createSql();
        $placeholder = $sql->getPlaceholder();
        $value       = false;
        $params      = ['key' => $id];

        if ($placeholder == ':') {
            $placeholder1 = 'key';
            $placeholder2 = 'handler';
        } else if ($placeholder == '$') {
            $placeholder1 = '1';
            $placeholder2 = '2';
        } else {
            $placeholder1 = '?';
            $placeholder2 = '?';
        }

        if ($name !== null) {
            $sql->select()->from($this->table)->where('key = ' . $placeholder1)->andWhere('handler = ' . $placeholder2);
            $params['handler'] = $name;
        } else {
            $sql->select()->from($this->table)->where('key = ' . $placeholder1);
        }

        $this->db->prepare($sql)
            ->bindParams($params)
            ->execute();

        $rows = $this->db->fetchAll();

        // If the value is found, return.
        if (isset($rows[0])) {
            foreach ($rows as $i => $row) {
                if (!empty($row['context'])) {
                    $rows[$i]['context'] = json_decode($row['context'], true);
                }
            }
        }

        return $rows;
    }

    /**
     * Determine if debug data exists by ID
     *
     * @param  string  $id
     * @param  ?string $name
     * @return bool
     */
    public function has(string $id, ?string $name = null): bool
    {
        $sql         = $this->db->createSql();
        $placeholder = $sql->getPlaceholder();

        if ($placeholder == ':') {
            $placeholder1 = 'key';
            $placeholder2 = 'handler';
        } else if ($placeholder == '$') {
            $placeholder1 = '1';
            $placeholder2 = '2';
        } else {
            $placeholder1 = '?';
            $placeholder2 = '?';
        }

        $sql->select()->from($this->table)->where('key = ' . $placeholder1)->andWhere('handler = ' . $placeholder2);

        $this->db->prepare($sql)
            ->bindParams(['key' => $id, 'handler' => $name])
            ->execute();

        $rows = $this->db->fetchAll();

        return !empty($rows);
    }

    /**
     * Delete debug data by ID
     *
     * @param  string  $id
     * @param  ?string $name
     * @return void
     */
    public function delete(string $id, ?string $name = null): void
    {
        $sql         = $this->db->createSql();
        $placeholder = $sql->getPlaceholder();
        $params      = ['key' => $id];

        if ($placeholder == ':') {
            $placeholder1 = 'key';
            $placeholder2 = 'handler';
        } else if ($placeholder == '$') {
            $placeholder1 = '1';
            $placeholder2 = '2';
        } else {
            $placeholder1 = '?';
            $placeholder2 = '?';
        }

        if ($name !== null) {
            $sql->delete($this->table)->where('key = ' . $placeholder1)->andWhere('handler = ' . $placeholder2);
            $params['handler'] = $name;
        } else {
            $sql->delete($this->table)->where('key = ' . $placeholder1);
        }

        $this->db->prepare($sql)
            ->bindParams($params)
            ->execute();
    }

    /**
     * Clear all debug data
     *
     * @return void
     */
    public function clear(): void
    {
        $sql = $this->db->createSql();
        $sql->delete($this->table);
        $this->db->query($sql);
    }

    /**
     * Create table in database
     *
     * @return void
     */
    protected function createTable(): void
    {
        $schema = $this->db->createSchema();
        $schema->create($this->table)
            ->int('id')->increment()
            ->varchar('key', 255)
            ->varchar('handler', 255)
            ->float('start')->nullable()
            ->float('end')->nullable()
            ->float('elapsed')->nullable()
            ->text('message')->nullable()
            ->text('context')->nullable()
            ->primary('id');

        $schema->execute();

        $schema->alter($this->table)->index('key');
        $schema->execute();

        $schema->alter($this->table)->index('handler');
        $schema->execute();

        $schema->alter($this->table)->index('start');
        $schema->execute();

        $schema->alter($this->table)->index('end');
        $schema->execute();

        $schema->alter($this->table)->index('elapsed');
        $schema->execute();

        $schema->alter($this->table)->index('type');
        $schema->execute();
    }

}
