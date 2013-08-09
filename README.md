# Getting Started

## Basic Usage

```php
#!/usr/bin/env php
<?php

require_once('<path_to_migreat>/schema_statements.php')));

connect('pgsql', 'localhost', '<database>');
migrate(array(
    array(
        '1',
        'Create first table',
        function () {
            execute('create table t1(c int)');
        },
        function () {
            execute('drop table t1');
        }),
    array(
        '2',
        'Create second table',
        function () {
            execute('create table t2(c int)');
        },
        function () {
            execute('drop table t2');
        })
));
```