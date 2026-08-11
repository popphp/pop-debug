<?php

namespace Pop\Debug\Test\Storage\Fixtures;

/**
 * Class name deliberately contains 'pgsql' so pop-db's Sql::init() resolves the '$' placeholder style.
 */
class PgsqlTestAdapter extends AbstractTestAdapter
{
}
