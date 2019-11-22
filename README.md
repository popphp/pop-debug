pop-debug
=========

[![Build Status](https://travis-ci.org/popphp/pop-debug.svg?branch=master)](https://travis-ci.org/popphp/pop-debug)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-debug)](http://cc.popphp.org/pop-debug/)

OVERVIEW
--------
`pop-debug` is a simple debugging component that can be used to hooked into an application to track
certain aspects of the application's lifecycle.

`pop-debug` is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `pop-debug` using Composer.

    composer require popphp/pop-debug

BASIC USAGE
-----------

The debugger supports a number of handlers that can record various events during an application's lifecycle.
The provided handlers are:

- **ExceptionHandler**
    + Capture exceptions thrown by the application
- **MemoryHandler**
    + Capture memory usage and peak memory usage
- **MessageHandler**
    + Capture generic messages at various points in the application's lifecycle
- **LogHandler**
    + Capture standard log messages at various points in the application's lifecycle
- **QueryHandler**
    + Capture database queries and their parameters and information
- **RequestHandler**
    + Capture information about the current request
- **TimeHandler**
    + Trigger a timer to time the current request or a part of the request.

Also, the debugger supports a few storage methods to storage the debug data after the request is complete:

- File
- Database
- Redis

### Setting up the debugger

```php
use Pop\Debug;

$debugger = new Debug\Debugger();
$debugger->addHandler(new Debug\Handler\MessageHandler());
$debugger->setStorage(new Debug\Storage\File('log'));

$debugger['message']->addMessage('Hey! Something happened!');

$debugger->save();
```

The above code will save the following output to the `log` folder in a plain text file:

```text
1504213206.00000	Hey! Something happened!
```

### Setting up multiple handlers

You can configure multiple handlers to capture different points of data within the application:

```php
use Pop\Debug;

$debugger = new Debug\Debugger();
$debugger->addHandler(new Debug\Handler\MessageHandler())
    ->addHandler(new Debug\Handler\ExceptionHandler())
    ->addHandler(new Debug\Handler\RequestHandler())
    ->addHandler(new Debug\Handler\MemoryHandler())
    ->addHandler(new Debug\Handler\TimeHandler());
$debugger->setStorage(new Debug\Storage\File('log'));

$debugger['message']->addMessage('Hey! Something happened!');
$debugger['exception']->addException(new \Exception('Whoops!'));
$debugger['memory']->updateMemoryUsage();
$debugger['memory']->updatePeakMemoryUsage();

$debugger->save();
```

In the above example, if the debugger is exposed as a service throughout the application,
then you can access it and call those methods above for the individual handlers to capture
the things you need to examine.

### Storage formats

The storage object allows you to store the debug data in the following formats:

- Plain text
- JSON
- Serialized PHP

```php
use Pop\Debug;

$debugger = new Debug\Debugger();
$debugger->addHandler(new Debug\Handler\MessageHandler());
$debugger->setStorage(new Debug\Storage\File('log', 'json'));
```

### Log handler

The log handler is a special handler that ties into the `pop-log` component. It allows you
to capture standard log messages. You can set up the log handler like this:

```php
use Pop\Debug;
use Pop\Log;

$logger = new Log\Logger(new Log\Writer\File('log/system.log'));
$debugger = new Debug\Debugger();
$debugger->addHandler(new Debug\Handler\LogHandler($logger));
$debugger->setStorage(new Debug\Storage\File('log'));

$debugger['log']->info("Here's some info about what just happened!");
sleep(1);
$debugger['log']->alert("Hey you! I must alert you to something!");

$debugger->save();
```

So with the logger object attached to the debugger, the debugger can record the log entries
that are sent to the logger as well.

### Query handler

The query handler is a special handler that ties into the `pop-db` component and the
profiler available with that component. It allows you to capture any database queries
and any information associated with them.

You can set up the query handler like this:

```php
use Pop\Debug;
use Pop\Db;

$db = Db\Db::mysqlConnect([
    'database' => 'popdb',
    'username' => 'popuser',
    'password' => '12pop34'
]);

$queryHandler = $db->listen('Pop\Debug\Handler\QueryHandler');

$debugger = new Debug\Debugger();
$debugger->addHandler($queryHandler);
$debugger->setStorage(new Debug\Storage\File('log'));

// Run DB queries...

$debugger->save();
```

So with the query handler attached to the database adapter object, any and all queries
that are executed will be recorded by the debugger's query handler.
