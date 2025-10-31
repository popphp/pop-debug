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
use Pop\Db\Adapter\Pdo;
use Pop\Db\Adapter\Sqlite;
use Pop\Debug\Handler\AbstractHandler;
use SQLite3;

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
     * @param  string          $format
     * @param  string          $table
     */
    public function __construct(AbstractAdapter $db, string $format = 'TEXT', string $table = 'pop_debug')
    {
        parent::__construct($format);

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
        $content = ($this->getFormat() == 'TEXT') ? $handler->prepareAsString() : $handler->prepare();
        $sql     = $this->db->createSql();
        $sql->reset();
        $placeholder = $sql->getPlaceholder();

        if ($placeholder == ':') {
            $placeholders = [
                'parent_id' => ':parent_id',
                'key'       => ':key',
                'handler'   => ':handler',
                'start'     => ':start',
                'end'       => ':end',
                'elapsed'   => ':elapsed',
                'type'      => ':type',
                'value'     => ':value',
                'content'   => ':content',
            ];
        } else if ($placeholder == '$') {
            $placeholders = [
                'parent_id' => '$1',
                'key'       => '$2',
                'handler'   => '$3',
                'start'     => '$4',
                'end'       => '$5',
                'elapsed'   => '$6',
                'type'      => '$7',
                'value'     => '$8',
                'content'   => '$9',
            ];
        } else {
            $placeholders = [
                'parent_id' => '?',
                'key'       => '?',
                'handler'   => '?',
                'start'     => '?',
                'end'       => '?',
                'elapsed'   => '?',
                'type'      => '?',
                'value'     => '?',
                'content'   => '?',
            ];
        }

        [$requestId, $handlerName] = explode('-', $id, 2);

        $parentId = null;
        $start    = $handler->getStart();
        $end      = $handler->getEnd();
        $elapsed  = $handler->getElapsed();
        $type     = null;
        $value    = null;

        $sql->insert($this->table)->values($placeholders);
        $params = [
            'parent_id' => $parentId,
            'key'       => $requestId,
            'handler'   => $handlerName,
            'start'     => $start,
            'end'       => $end,
            'elapsed'   => $elapsed,
            'type'      => $type,
            'value'     => $value,
            'content'   => $this->encodeValue($content),
        ];

        // Save value
        $this->db->prepare($sql)
            ->bindParams($params)
            ->execute();
    }

    /**
     * Get debug data by ID
     *
     * @param  string $id
     * @return mixed
     */
    public function getById(string $id): mixed
    {
        $sql         = $this->db->createSql();
        $placeholder = $sql->getPlaceholder();
        $value       = false;

        [$requestId, $handlerName] = explode('-', $id, 2);

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
            ->bindParams(['key' => $requestId, 'handler' => $handlerName])
            ->execute();

        $rows = $this->db->fetchAll();

        // If the value is found, return.
        if (isset($rows[0]) && isset($rows[0]['content'])) {
            $value = $this->decodeValue($rows[0]['content']);
        }

        return $value;
    }

    /**
     * Get debug data by type
     *
     * @param  string $type
     * @return mixed
     */
    public function getByType(string $type): mixed
    {
        $sql         = $this->db->createSql();
        $placeholder = $sql->getPlaceholder();
        $value       = false;

        if ($placeholder == ':') {
            $placeholder .= 'handler';
        } else if ($placeholder == '$') {
            $placeholder .= '1';
        } else {
            $placeholder = '?';
        }

        $sql->select()->from($this->table)->where('handler = ' . $placeholder);
        $this->db->prepare($sql)
            ->bindParams(['handler' => $type])
            ->execute();

        $rows = $this->db->fetchAll();

        // If the value is found, return.
        if (isset($rows[0])) {
            $value = $rows;
        }

        return $value;
    }

    /**
     * Determine if debug data exists by ID
     *
     * @param  string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        $sql         = $this->db->createSql();
        $placeholder = $sql->getPlaceholder();

        [$requestId, $handlerName] = explode('-', $id, 2);

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
            ->bindParams(['key' => $requestId, 'handler' => $handlerName])
            ->execute();

        $rows = $this->db->fetchAll();

        return (isset($rows[0]) && isset($rows[0]['content']));
    }

    /**
     * Delete debug data by ID
     *
     * @param  string $id
     * @return void
     */
    public function delete(string $id): void
    {
        $sql         = $this->db->createSql();
        $placeholder = $sql->getPlaceholder();

        [$requestId, $handlerName] = explode('-', $id, 2);

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

        $sql->delete($this->table)->where('key = ' . $placeholder1)->andWhere('handler = ' . $placeholder2);

        $this->db->prepare($sql)
            ->bindParams(['key' => $requestId, 'handler' => $handlerName])
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
     * Encode the value based on the format
     *
     * @param  mixed  $value
     * @throws Exception
     * @return string
     */
    public function encodeValue(mixed $value): string
    {
        if ($this->format == self::JSON) {
            $value = json_encode($value, JSON_PRETTY_PRINT);
        } else if ($this->format == self::PHP) {
            $value = serialize($value);
        } else if (!is_string($value)) {
            throw new Exception('Error: The value must be a string if storing in text format.');
        }

        return $value;
    }

    /**
     * Decode the value based on the format
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function decodeValue(mixed $value): mixed
    {
        if ($this->format == self::JSON) {
            $value = json_decode($value, true);
        } else if ($this->format == self::PHP) {
            $value = unserialize($value);
        }

        return $value;
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
            ->int('parent_id')->nullable()
            ->varchar('key', 255)
            ->varchar('handler', 255)
            ->float('start')->nullable()
            ->float('end')->nullable()
            ->float('elapsed')->nullable()
            ->varchar('type', 255)->nullable()
            ->text('value')->nullable()
            ->text('content')->nullable()
            ->primary('id');

        $schema->execute();

        if ((!$this->db instanceof Sqlite) || (($this->db instanceof Pdo) && $this->db->getType() != 'sqlite')) {
            $schema->alter($this->table)->foreignKey('parent_id')->references($this->table)->on('id')->onDelete('CASCADE');
            $schema->execute();
        }

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
