pop-debug
=========

[![Build Status](https://github.com/popphp/pop-debug/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-debug/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-debug)](http://cc.popphp.org/pop-debug/)

[![Join the chat at https://popphp.slack.com](https://media.popphp.org/img/slack.svg)](https://popphp.slack.com)
[![Join the chat at https://discord.gg/D9JBxPa5](https://media.popphp.org/img/discord.svg)](https://discord.gg/D9JBxPa5)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Handlers](#handlers)
  - [Exception](#exception)
  - [Memory](#memory)
  - [Message](#message)
  - [Query](#query)
  - [Request](#request)
  - [Time](#time)
* [Storage](#storage)
  - [File](#file)
  - [Database](#database)
  - [Redis](#redis)
* [Formats](#formats)

Overview
--------
`pop-debug` is a debugging component that can be used to hooked into an application to track
certain aspects of the application's lifecycle. It can help provide insight to an application's
performance or any issues that may arise within an application.

`pop-debug` is a component of the [Pop PHP Framework](http://www.popphp.org/).

Install
-------

Install `pop-debug` using Composer.

    composer require popphp/pop-debug

Or, require it in your composer.json file

    "require": {
        "popphp/pop-debug" : "^2.0.0"
    }

[Top](#pop-debug)

Quickstart
----------

The basic concept of the debugger is that it works with a handler object or multiple handler objects and
one storage object. The handlers are wired to listen to and track various aspects of the application and
push their results to the storage object to be retrieved at a later time.

In this simple example, we can set up a generic message handler to storage its triggered messages in a file.

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

The above code will save the following output to the `log` folder in a plain text file:

```text
1504213206.00000    Hey! Something happened!
```

[Top](#pop-debug)

Handlers
----------

There are a total of 6 available handlers. More handlers can be added, provided they implement the
handler interface.

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

The above code will save the following output to the `log` folder in a plain text file:

```text
1698699170.22920	Error: This is a test exception
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

The above code will save the following output to the `log` folder in a plain text file:

```text
Limit:			128MB

Usages:
-------
1698699589.59750	1.19MB
1698699591.59760	1.19MB

Peaks:
------
1698699589.59750	1.5MB
1698699591.59770	1.5MB
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

The above code will save the following output to the `log` folder in a plain text file:

```text
1504213206.00000    Hey! Something happened!
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

$debugger = new Debugger();
$debugger->addHandler($db->listen('Pop\Debug\Handler\QueryHandler'));
$debugger->setStorage(new File('log'));

// Interact with the database
$user = new Users([
    'username' => 'admin',
    'password' => 'password'
]);

$user->save();

$debugger->save();
```

The above code will save the following output to the `log` folder in a plain text file:

```text
Start:			1698703083.95424
Finish:			0.00000
Elapsed:		0.01048 seconds

Queries:
--------
INSERT INTO `users` (`username`, `password`) VALUES (?, ?) [0.00697]
Start:			1698703083.95769
Finish:			1698703083.96466
Params:
	username => admin
	password => password
```

[Top](#pop-debug)

### Request

The request handler works with a `Pop\Http\Server\Request` object from the `pop-http` component and tracks
all of the inbound request data.

```php
use Pop\Debug\Debugger;
use Pop\Debug\Handler\RequestHandler;
use Pop\Debug\Storage\File;

$debugger = new Debugger();
$debugger->addHandler(new RequestHandler());
$debugger->setStorage(new File(__DIR__ . '/log'));
$debugger->save();
```

The above code will save the following output to the `log` folder in a plain text file:

```text
GET /http.php?foo=bar [1698703989.32316]

HEADERS:
--------
Host: Host: localhost:8000
User-Agent: User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/119.0
Accept: Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8
Accept-Language: Accept-Language: en-US,en;q=0.5
Accept-Encoding: Accept-Encoding: gzip, deflate, br
Connection: Connection: keep-alive
Cookie: Cookie: PHPSESSID=gm6schd82drhemu71isp26355b

SERVER:
-------
DOCUMENT_ROOT: /path/to/repo/public
REMOTE_ADDR: 127.0.0.1
REMOTE_PORT: 43394
SERVER_SOFTWARE: PHP 8.2.11 Development Server
SERVER_PROTOCOL: HTTP/1.1
SERVER_NAME: localhost
SERVER_PORT: 8000
REQUEST_URI: /http.php?foo=bar
REQUEST_METHOD: GET
SCRIPT_NAME: /http.php
SCRIPT_FILENAME: /path/to/repo/public/http.php
PHP_SELF: /http.php
QUERY_STRING: foo=bar
HTTP_HOST: localhost:8000
HTTP_USER_AGENT: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/119.0
HTTP_ACCEPT: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8
HTTP_ACCEPT_LANGUAGE: en-US,en;q=0.5
HTTP_ACCEPT_ENCODING: gzip, deflate, br
HTTP_CONNECTION: keep-alive
HTTP_COOKIE: PHPSESSID=gm6schd82drhemu71isp26355b
REQUEST_TIME_FLOAT: 1698703590.0842
REQUEST_TIME: 1698703590

GET:
----
foo: bar

COOKIE:
-------
PHPSESSID: gm6schd82drhemu71isp26355b

SESSION:
--------
_POP_SESSION_: 

PARSED:
-------
foo: bar

RAW:
----
foo=bar
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

The above code will save the following output to the `log` folder in a plain text file:

```text
Start:			1698704121.29484
Stop:			1698704123.29532
Elapsed:		2.00048 seconds
```

[Top](#pop-debug)

Storage
-------

There are a few different storage options are available to store the output of the debugger.

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

### Redis

Store the debugger output into the Redis server cache.

```php
use Pop\Debug\Debugger;
use Pop\Debug\Handler\TimeHandler;
use Pop\Debug\Storage\Redis;

$debugger = new Debugger();
$debugger->addHandler(new TimeHandler());
$debugger->setStorage(new Redis());
```

[Top](#pop-debug)

Formats
-------

Three different formats are available for the storing of the debugger output:

- Text (Default)
- JSON
- Serialized PHP

You can set it via the constructor:

```php
use Pop\Debug\Storage\File;

$fileStorage = new File(__DIR__ . '/log', 'TEXT');
// OR
$fileStorage = new File(__DIR__ . '/log', 'JSON');
// OR
$fileStorage = new File(__DIR__ . '/log', 'PHP');
```

Also, the format can be set via the `setFormat()` method:

```php
use Pop\Debug\Storage\File;

$fileStorage = new File(__DIR__ . '/log');

$fileStorage->setFormat('TEXT');
// OR
$fileStorage->setFormat('JSON');
// OR
$fileStorage->setFormat('PHP');
```


[Top](#pop-debug)