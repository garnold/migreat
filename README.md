# Getting Started

## Basic Usage

Given a PHP script `migrate.php` containing the following 3 method calls:

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

* `migrate.php [UP] [STEP=<N>]` - Applies the next `N` pending migrations on `<database>` (creating it if necessary), defaulting to all.
* `migrate.php REDO [STEP=<N>]` - Reverts and reapplies the last `N` previously applied migrations, defaulting to 1.
* `migrate.php UNDO [STEP=<N>]` - Reverts the last `N` previously applied migrations, defaulting to 1.
* `migrate.php DOWN [STEP=<N>]` - Reverts the last `N` previously applied migrations, defaulting to all.
