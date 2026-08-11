<?php

namespace Pop\Debug\Test\Storage\Fixtures;

use Pop\Db\Adapter\AbstractAdapter;

/**
 * Minimal no-op AbstractAdapter implementation used to exercise Storage\Database::save()'s
 * placeholder-style branching (':' / '$' / '?'), which pop-db's Sql class picks based on the
 * adapter's class name. There's no live MySQL/Postgres server in this test environment, so
 * concrete subclasses named after those drivers (PgsqlTestAdapter, GenericTestAdapter) stand in
 * for them - the SQLite-backed DatabaseTest already covers the ':' branch against a real driver.
 */
abstract class AbstractTestAdapter extends AbstractAdapter
{

    /**
     * The SQL passed to the most recent prepare() call, for tests to inspect the rendered
     * placeholder style actually used.
     * @var mixed
     */
    public mixed $lastPreparedSql = null;

    public function __construct(array $options = [])
    {
    }

    public function connect(array $options = []): AbstractAdapter
    {
        return $this;
    }

    public function setOptions(array $options): AbstractAdapter
    {
        return $this;
    }

    public function hasOptions(): bool
    {
        return false;
    }

    public function beginTransaction(): AbstractAdapter
    {
        return $this;
    }

    public function commit(): AbstractAdapter
    {
        return $this;
    }

    public function rollback(): AbstractAdapter
    {
        return $this;
    }

    public function isSuccess(): bool
    {
        return true;
    }

    public function query(mixed $sql): AbstractAdapter
    {
        return $this;
    }

    public function prepare(mixed $sql): AbstractAdapter
    {
        $this->lastPreparedSql = $sql;
        return $this;
    }

    public function bindParams(array $params): AbstractAdapter
    {
        return $this;
    }

    public function execute(): AbstractAdapter
    {
        return $this;
    }

    public function fetch(): mixed
    {
        return null;
    }

    public function fetchAll(): array
    {
        return [];
    }

    public function escape(?string $value = null): string
    {
        return (string)$value;
    }

    public function getLastId(): int
    {
        return 0;
    }

    public function getNumberOfRows(): int
    {
        return 0;
    }

    public function getNumberOfAffectedRows(): int
    {
        return 0;
    }

    public function getVersion(): string
    {
        return '';
    }

    public function getTables(): array
    {
        return ['pop_debug'];
    }

}
