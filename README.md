pop-debug
=========

[![Build Status](https://github.com/popphp/pop-debug/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-debug/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-debug)](http://cc.popphp.org/pop-debug/)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Handlers](#handlers)
  - [Exception](#exception)
  - [Memory](#memory)
  - [Message](#message)
  - [PHP](#php)
  - [Query](#query)
  - [Request](#request)
  - [Time](#time)
* [Storage](#storage)
  - [File](#file)
  - [Database](#database)
* [Logging](#logging)

Overview
--------
`pop-debug` is a debugging component that can be used to hook into an application to track
certain aspects of the application's lifecycle. It can help provide insight to an application's
performance or any issues that may arise within an application.

`pop-debug` is a component of the [Pop PHP Framework](https://www.popphp.org/).

Install
-------

Install `pop-debug` using Composer.

    composer require popphp/pop-debug

Or, require it in your composer.json file

    "require": {
        "popphp/pop-debug" : "^3.0.0"
    }

[Top](#pop-debug)

Quickstart
----------

The basic concept of the debugger is that it works with a handler object or multiple handler objects and
one storage object. The handlers are wired to listen to and track various aspects of the application and
push their results to the storage object to be retrieved at a later time.

In this simple example, we can set up a generic message handler to store its triggered messages in a file.

```php
use Pop\Debug\Debugger;
use Pop\Debug\Handler\MessageHandler;
use Pop\Debug\Storage\File;

$debugger = new Debugger();
$debugger->addHandler(new MessageHandler());
$debugger->setStorage(new File(__DIR__ . '/log'));

$debugger['message']->addMessage('Hey! Something happened!');

$debugger->save();
```

The above code will save the following output to the `log` folder in a CSV file:

```text
key,handler,start,end,elapsed,type,message,context
b8c00658be2aee93703deea23e58b99f,message,1762216971.7394,,,message,Hey! Something happened!,
```

[Top](#pop-debug)

Handlers
----------

There are a total of 6 available handlers. More handlers can be added, provided they implement the
`Pop\Debug\Handler\HandlerInterface` interface.

### Exception

The exception handler captures and tracks any exceptions thrown by an application.

```php
use Pop\Debug\Debugger;
use Pop\Debug\Handler\ExceptionHandler;
use Pop\Debug\Storage\File;

$debugger = new Debugger();
$debugger->addHandler(new ExceptionHandler());
$debugger->setStorage(new File(__DIR__ . '/log'));

try {
    throw new \Exception('Error: This is a test exception');
} catch (\Exception $e) {
    $debugger['exception']->addException($e);
    $debugger->save();
}
```

[Top](#pop-debug)

### Memory

The memory handler captures memory usages and peak memory usage. At any point in the application,
you can call the `updateMemoryUsage()` and `updatePeakMemoryUsage()` methods to take a snapshot
of memory usage in the app at that time.

```php
use Pop\Debug\Debugger;
use Pop\Debug\Handler\MemoryHandler;
use Pop\Debug\Storage\File;

$debugger = new Debugger();
$debugger->addHandler(new MemoryHandler());
$debugger->setStorage(new File(__DIR__ . '/log'));


$debugger['memory']->updateMemoryUsage();
$debugger['memory']->updatePeakMemoryUsage();
sleep(2);
$debugger['memory']->updateMemoryUsage();
$debugger['memory']->updatePeakMemoryUsage();

$debugger->save();
```

[Top](#pop-debug)

Message
-------

The message handler provides simple and generic messaging to record debug events from
within the application:

```php
use Pop\Debug\Debugger;
use Pop\Debug\Handler\MessageHandler;
use Pop\Debug\Storage\File;

$debugger = new Debugger();
$debugger->addHandler(new MessageHandler());
$debugger->setStorage(new File(__DIR__ . '/log'));

$debugger['message']->addMessage('Hey! Something happened!');

$debugger->save();
```

[Top](#pop-debug)

PHP
---

The PHP handler provides a way to take a snapshot of common PHP info and INI values:

```php
use Pop\Debug\Debugger;
use Pop\Debug\Handler\PhpHandler;
use Pop\Debug\Storage\File;

$debugger = new Debugger();
$debugger->addHandler(new PhpHandler());
$debugger->setStorage(new File(__DIR__ . '/log'));

$debugger->save();
```

[Top](#pop-debug)

### Query

The query handler is a special handler that ties into the `pop-db` component and the profiler
available with that component. It allows you to capture any database queries and any information
associated with them.

You can set up the query handler like this:

```php
use Pop\Debug\Debugger;
use Pop\Debug\Storage\File;
use Pop\Db\Db;
use Pop\Db\Record;

$db = Db::mysqlConnect([
    'database' => 'DATABASE',
    'username' => 'DB_USER',
    'password' => 'DB_PASS'
]);

class Users extends Record {}

Record::setDb($db);

// Register the query handler with the DB adapter 
$queryHandler = $db->listen('Pop\Debug\Handler\QueryHandler');

$debugger = new Debugger();
$debugger->addHandler($queryHandler);
$debugger->setStorage(new File('log'));

// Interact with the database
$user = new Users([
    'username' => 'admin',
    'password' => 'password'
]);

$user->save();

$debugger->save();
```

[Top](#pop-debug)

### Request

The request handler works with a `Pop\Http\Server\Request` object from the `pop-http` component and tracks
all of the inbound request data. The following example would be a block of code that would run in a script
that receives an inbound HTTP request:

```php
use Pop\Debug\Debugger;
use Pop\Debug\Handler\RequestHandler;
use Pop\Debug\Storage\File;

$debugger = new Debugger();
$debugger->addHandler(new RequestHandler());
$debugger->setStorage(new File(__DIR__ . '/log'));
$debugger->save();
```

[Top](#pop-debug)

### Time

The time handler provides a simple way to track how long a application request takes, which is useful
for performance metrics.

```php
use Pop\Debug\Debugger;
use Pop\Debug\Handler\TimeHandler;
use Pop\Debug\Storage\File;

$debugger = new Debugger();
$debugger->addHandler(new TimeHandler());
$debugger->setStorage(new File(__DIR__ . '/log'));

sleep(2);

$debugger->save();
```

[Top](#pop-debug)

Storage
-------

There are two different storage options are available to store the output of the debugger:

- CSV (or TSV) File
- Database Table

### File

Store the debugger output into a file in a folder location:

```php
use Pop\Debug\Debugger;
use Pop\Debug\Handler\TimeHandler;
use Pop\Debug\Storage\File;

$debugger = new Debugger();
$debugger->addHandler(new TimeHandler());
$debugger->setStorage(new File(__DIR__ . '/log'));
```

[Top](#pop-debug)

### Database

Store the debugger output into a table in a database. The default table name is `pop_debug` but that
can be changed with the database storage object.

```php
use Pop\Debug\Debugger;
use Pop\Debug\Handler\TimeHandler;
use Pop\Debug\Storage\Database;
use Pop\Db\Db;

$db = Db::mysqlConnect([
    'database' => 'DATABASE',
    'username' => 'DB_USER',
    'password' => 'DB_PASS'
]);

$debugger = new Debugger();
$debugger->addHandler(new TimeHandler());
$debugger->setStorage(new Database($db, 'text', 'my_debug_table'));
```

[Top](#pop-debug)

Logging
-------

The debug component can also work with the `pop-log` component to deliver syslog-compatible logging messages
to a logging resource using the standard BSD syslog protocol [RFC-3164](http://tools.ietf.org/html/rfc3164).
Logging can be used in additional to the storage adapters, or by itself, sending the debug data and information
to the logging resource only and without storing anything to a storage adapter.

To work with a logger, a logger object must be passed to the debugger, along with logging parameters, which is an array
of options. The minimum parameter required is a `level` value. The `context` option can also be used to log the body
of the debug messaging results:

```php
use Pop\Debug\Debugger;
use Pop\Debug\Handler\ExceptionHandler;
use Pop\Debug\Storage\File;
use Pop\Log;

$debugger = new Debugger();
$debugger->addHandler(new ExceptionHandler(true));
$debugger->addLogger(
    new Log\Logger(new Log\Writer\File(__DIR__ . '/log/debug.log')),
    [
        'level'   => Log\Logger::ERROR,
        'context' => 'json'
    ]
);

try {
    throw new Pop\Debug\Exception('This is a test debug exception');
} catch (\Exception $e) {
    $debugger['exception']->addException($e);
    $debugger->save();
}
```

Other logging parameters options include:

***Memory***

The `usage_limit` and `peak_limit` are memory-specific limits to monitor is an operation goes above the specified limits.

```php
$loggingParams = [
    'level'       => Log\Logger::WARNING,
    'usage_limit' => '500000',  // Limit in bytes.
                                // If the usage goes above the limit,
                                // the log message is sent
    'peak_limit'  => '1000000', // Limit in bytes.
                                // If the peak usage goes above the limit,
                                // the log message is sent
];
```

***Query, Request & Time***

The `limit` parameter is supported for the query, request and time handlers. It is a time limit. If any of those
operations take longer than the time limit, a log message is sent.

```php
$loggingParams = [
    'level' => Log\Logger::WARNING,
    'limit' => 1, // Time limit in seconds.
                  // If the operation takes longer than the time limit,
                  // the log message is sent
];
```

##### Query Example:

```php
use Pop\Debug\Debugger;
use Pop\Db\Db;
use Pop\Db\Record;
use Pop\Db\Adapter\Profiler\Profiler;
use Pop\Log;

$db = Db::mysqlConnect([
    'database' => 'DATABASE',
    'username' => 'DB_USER',
    'password' => 'DB_PASS'
]);

class Users extends Record {}

Record::setDb($db);

// Register the debugger and query handler with the DB adapter
$debugger = new Debugger();
$db->listen('Pop\Debug\Handler\QueryHandler', null, new Profiler($debugger));

// Add logger to the debugger
$debugger->addLogger(
    new Log\Logger(new Log\Writer\File(__DIR__ . '/log/debug.log')),
    [
        'level' => Log\Logger::INFO,
        'limit' => 1
    ]
);

// Save a user to the database - debugging and logging will automatically happen
$user = new Users([
    'username' => 'testuser',
    'password' => 'password',
    'email'    => 'testuser@test.com'
]);

$user->save();
```

[Top](#pop-debug)
