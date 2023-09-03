<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2023 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2023 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.3.2
 */
class Db extends AbstractStorage
{

    /**
     * DB adapter
     * @var AbstractAdapter
     */
    protected $db = null;

    /**
     * Table
     * @var string
     */
    protected $table = 'pop_debug';

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
    public function __construct(AbstractAdapter $db, $format = 'text', $table = 'pop_debug')
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
     * @param  string $db
     * @return Db
     */
    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Get the current debug db adapter.
     *
     * @return AbstractAdapter
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Get the current debug db table.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set the debug db table
     *
     * @param  string $table
     * @return Db
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Save debug data
     *
     * @param  string $id
     * @param  mixed  $value
     * @return Db
     */
    public function save($id, $value)
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

        return $this;
    }

    /**
     * Get debug data
     *
     * @param  string $id
     * @return mixed
     */
    public function get($id)
    {
        $sql         = $this->db->createSql();
        $placeholder = $sql->getPlaceholder();
        $value       = false;

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

        // If the value is found, return.
        if (isset($rows[0]) && isset($rows[0]['value'])) {
            $value = $this->decodeValue($rows[0]['value']);
        }

        return $value;
    }

    /**
     * Determine if debug data exists
     *
     * @param  string $id
     * @return mixed
     */
    public function has($id)
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
     * Delete debug data
     *
     * @param  string $id
     * @return void
     */
    public function delete($id)
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
    public function clear()
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
    public function encodeValue($value)
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
    public function decodeValue($value)
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
    protected function createTable()
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
