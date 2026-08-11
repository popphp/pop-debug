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
        "popphp/pop-debug" : "^4.0.0"
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

The `key` column is the debugger's **request ID** - a unique ID auto-generated the first time it's needed
(`Debugger::getRequestId()`) that's shared by every handler's output for that `Debugger` instance, so events from
the same request can be correlated later. It can also be set explicitly with `setRequestId()` if you want it to
match an ID from elsewhere in the app (e.g. a request/trace ID from upstream middleware).

Handlers and storage can also be passed directly into the constructor instead of calling `addHandler()`/
`setStorage()` afterward - as individual arguments or as a single array:

```php
$debugger = new Debugger(new MessageHandler(), new File(__DIR__ . '/log'));
// or
$debugger = new Debugger([new MessageHandler(), new File(__DIR__ . '/log')]);
```

[Top](#pop-debug)

Handlers
----------

There are a total of 7 available handlers. More handlers can be added by extending
`Pop\Debug\Handler\AbstractHandler` (which provides the shared timing, name and logger plumbing that
`HandlerInterface` requires) rather than implementing the interface from scratch.

Each handler is keyed on the debugger (e.g. `$debugger['message']`) by its class name, lowercased, with `Handler`
stripped off. To register more than one instance of the same handler type, pass a `$name` to the constructor - it's
prefixed onto the key, e.g. `new MessageHandler('custom')` is registered as `custom-message`:

```php
$debugger->addHandler(new MessageHandler('requests'));
$debugger->addHandler(new MessageHandler('background-jobs'));

$debugger['requests-message']->addMessage('Handled an inbound request');
$debugger['background-jobs-message']->addMessage('Ran a queued job');
```

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

### Message

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

### PHP

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

Unlike the request handler (below), the query handler has no redaction - bound query parameters are captured
as-is, including anything sensitive passed into them (the `password` value bound above, for example). Keep that
in mind before wiring a logger or persistent storage to it in an environment where that matters.

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

By default, the request handler redacts values for keys that look sensitive (`password`, `token`, `secret`,
`authorization`, `cookie`, etc., matched case- and separator-insensitively) out of the headers, `$_SERVER`,
`$_ENV`, and post/put/patch/parsed data it captures, and redacts the entirety of `$_COOKIE` and `$_SESSION`.
This keeps secrets out of whatever storage or logger the debugger is wired to. Redaction can be turned off, or
its key list customized, per handler instance:

```php
use Pop\Debug\Handler\RequestHandler;

$requestHandler = new RequestHandler();
$requestHandler->setRedactSensitiveData(false);       // capture raw values, unredacted
$requestHandler->setRedactedKeys(['password', 'pin']); // replace the default key list entirely
$requestHandler->addRedactedKey('x-internal-id');      // add one more key to the current list
```

[Top](#pop-debug)

### Time

The time handler provides a simple way to track how long an application request takes, which is useful
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

There are two different storage options available to store the output of the debugger:

- CSV (or TSV, or NDJSON) File
- Database Table

Whichever is set, calling `$debugger->clear()` proxies to that storage's own `clear()` method to wipe out
whatever it's accumulated - see the caveats about what exactly gets cleared in each section below.

### File

Store the debugger output into a file in a folder location. The folder must already exist and be writable -
it's not created automatically, and the constructor throws `Pop\Debug\Storage\Exception` if it's missing or
not writable. The default format is CSV, but TSV and NDJSON (newline-delimited JSON, one self-contained JSON
object per line) are also supported via the second constructor argument:

```php
use Pop\Debug\Debugger;
use Pop\Debug\Handler\TimeHandler;
use Pop\Debug\Storage\File;

$debugger = new Debugger();
$debugger->addHandler(new TimeHandler());
$debugger->setStorage(new File(__DIR__ . '/log'));       // CSV (default)
$debugger->setStorage(new File(__DIR__ . '/log', 'tsv'));
$debugger->setStorage(new File(__DIR__ . '/log', 'ndjson'));
```

NDJSON is well-suited to log-aggregator/`jq`-style tooling: unlike the CSV/TSV formats, where the `context`
column is a `json_encode()`d string in a flat cell, NDJSON output keeps `context` as real nested JSON.

`clear()` deletes **every file** directly inside the storage directory, not just ones the debugger wrote - so
give it a dedicated folder rather than pointing it at a directory anything else writes to.

[Top](#pop-debug)

### Database

Store the debugger output into a table in a database. The default table name is `pop_debug` but that
can be changed via the second constructor argument. If the table doesn't already exist, it's created
automatically (along with several indexes) the first time the storage object is constructed.

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
$debugger->setStorage(new Database($db, 'my_debug_table'));
```

`clear()` runs an unconditional `DELETE` against the whole table - it's not scoped to a single request or run.

[Top](#pop-debug)

Logging
-------

The debug component can also work with the `pop-log` component (or any other [PSR-3](https://www.php-fig.org/psr/psr-3/)
logger) to deliver log messages as debug events happen. `pop-log`'s `Logger` supports several interchangeable
writers - `File`, `Database`, `Mail`, `Http`, `Stream`, and `Syslog` (for standard BSD syslog / [RFC-3164](http://tools.ietf.org/html/rfc3164)
delivery) - so where the messages actually go depends entirely on which writer you configure it with; the examples
below use `Log\Writer\File`. Logging can be used in addition to the storage adapters, or by itself, sending the
debug data and information to the logging resource only and without storing anything to a storage adapter.

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

The `usage_limit` and `peak_limit` are memory-specific limits to monitor if an operation goes above the specified limits.

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

Note this is set up a little differently than the [Query handler example](#query) above: passing a `Profiler`
that's already wired to the `$debugger` into `listen()` makes it register the resulting `QueryHandler` on that
debugger automatically, so there's no separate `$debugger->addHandler(...)` call needed here.

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
