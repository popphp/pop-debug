<?php

namespace Pop\Debug\Test\Storage\Fixtures;

/**
 * Class name deliberately avoids any known driver keyword so pop-db's Sql::init() falls through
 * to the '?' placeholder style (the same style mysql/sqlsrv adapters resolve to).
 */
class GenericTestAdapter extends AbstractTestAdapter
{
}
