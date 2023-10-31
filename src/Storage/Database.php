<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Debug\Storage;

use Pop\Db\Adapter\AbstractAdapter;

/**
 * Debug database storage class
 *
 * @category   Pop
 * @package    Pop\Debug
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
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
    public function __construct(AbstractAdapter $db, string $format = 'text', string $table = 'pop_debug')
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
     * @param  string $id
     * @param  mixed  $value
     * @return void
     */
    public function save(string $id, mixed $value): void
    {
        // Determine if the value already exists.
        $sql         = $this->db->createSql();
        $placeholder = $sql->getPlaceholder();

        if ($placeholder == ':') {
            $placeholder .= 'key';
        } else if ($placeholder == '$') {
            $placeholder .= '1';
        }

        $sql->select()->from($this->table)->where('key = ' . $placeholder);

        $this->db->prepare($sql)
            ->bindParams(['key' => $id])
            ->execute();

        $rows = $this->db->fetchAll();

        $sql->reset();
        $placeholder = $sql->getPlaceholder();

        // Insert new value
        if (count($rows) == 0) {
            if ($placeholder == ':') {
                $placeholder1 = ':key';
                $placeholder2 = ':value';
            } else if ($placeholder == '$') {
                $placeholder1 = '$1';
                $placeholder2 = '$2';
            } else {
                $placeholder1 = $placeholder;
                $placeholder2 = $placeholder;
            }
            $sql->insert($this->table)->values(['key' => $placeholder1, 'value' => $placeholder2]);
            $params = [
                'key'   => $id,
                'value' => $this->encodeValue($value)
            ];
        // Else, update it.
        } else {
            if ($placeholder == ':') {
                $placeholder1 = ':value';
                $placeholder2 = ':key';
            } else if ($placeholder == '$') {
                $placeholder1 = '$1';
                $placeholder2 = '$2';
            } else {
                $placeholder1 = $placeholder;
                $placeholder2 = $placeholder;
            }
            $sql->update($this->table)->values(['value' => $placeholder1])->where('key = ' . $placeholder2);
            $params = [
                'value' => $this->encodeValue($value),
                'key'   => $id
            ];
        }

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
        $isWildcard  = false;

        if ($placeholder == ':') {
            $placeholder .= 'key';
        } else if ($placeholder == '$') {
            $placeholder .= '1';
        }

        if (str_ends_with($id, '*') || str_ends_with($id, '%')) {
            $sql->select()->from($this->table)->where('key LIKE ' . $placeholder);
            $id = substr($id, 0, -1) . '%';
            $isWildcard = true;
        } else {
            $sql->select()->from($this->table)->where('key = ' . $placeholder);
        }

        $this->db->prepare($sql)
            ->bindParams(['key' => $id])
            ->execute();

        $rows = $this->db->fetchAll();

        // If the value is found, return.
        if (($isWildcard) && isset($rows[0])) {
            $value = $rows;
        } else if (isset($rows[0]) && isset($rows[0]['value'])) {
            $value = $this->decodeValue($rows[0]['value']);
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
            $placeholder .= 'key';
        } else if ($placeholder == '$') {
            $placeholder .= '1';
        }

        $sql->select()->from($this->table)->where('key LIKE ' . $placeholder);
        $this->db->prepare($sql)
            ->bindParams(['key' => '%' . $type])
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

        if ($placeholder == ':') {
            $placeholder .= 'key';
        } else if ($placeholder == '$') {
            $placeholder .= '1';
        }

        $sql->select()->from($this->table)->where('key = ' . $placeholder);

        $this->db->prepare($sql)
            ->bindParams(['key' => $id])
            ->execute();

        $rows = $this->db->fetchAll();

        return (isset($rows[0]) && isset($rows[0]['value']));
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

        if ($placeholder == ':') {
            $placeholder .= 'key';
        } else if ($placeholder == '$') {
            $placeholder .= '1';
        }

        $sql->delete($this->table)->where('key = ' . $placeholder);

        $this->db->prepare($sql)
            ->bindParams(['key' => $id])
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
            ->varchar('key', 255)
            ->text('value')
            ->primary('id');

        $this->db->query($schema);
    }

}
