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
        $sql         = $this->db->createSql();
        $placeholder = $sql->getPlaceholder();

        if ($placeholder == ':') {
            $placeholders = [
                'key'     => ':key',
                'handler' => ':handler',
                'start'   => ':start',
                'end'     => ':end',
                'elapsed' => ':elapsed',
                'type'    => ':type',
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
                'type'    => '$6',
                'message' => '$7',
                'context' => '$8',
            ];
        } else {
            $placeholders = [
                'key'     => '?',
                'handler' => '?',
                'start'   => '?',
                'end'     => '?',
                'elapsed' => '?',
                'type'    => '?',
                'message' => '?',
                'context' => '?',
            ];
        }

        $events = $this->prepareEvents($id, $name, $handler);

        foreach ($events as $event) {
            $sql->reset()
                ->insert($this->table)
                ->values($placeholders);

            // Save value
            $this->db->prepare($sql)
                ->bindParams($event)
                ->execute();
        }
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
            ->decimal('start', 16, 6)->nullable()
            ->decimal('end', 16, 6)->nullable()
            ->decimal('elapsed', 16, 6)->nullable()
            ->varchar('type', 255)
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
