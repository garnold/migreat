<?php

function connect($driver, $database_name, $host = '127.0.0.1', $port = 3306, $username = 'root', $password = '', $options = []) {
    require_once(implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), "schema_statements.$driver.php")));

    __do_connect("$driver:host=$host;port=$port", $username, $password, $options);
    __ob(function () use ($database_name) {
        say('== Create database');
        if (!database_exists($database_name)) {
            create_database($database_name);
            return true;
        }
        else {
            return false;
        }
    });

    __do_connect("$driver:host=$host;port=$port;dbname=$database_name", $username, $password, $options);
    __ob(function () {
        say('== Create schema_migrations table');
        if (!table_exists('schema_migrations')) {
            create_schema_migrations_table();
            return true;
        }
        else {
            return false;
        }
    });

    return true;
}

function __do_connect($dsn, $username, $password, $options) {
    global $connection;

    $connection = new PDO($dsn, $username, $password, $options);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return true;
}

function __ob($callback) {
    ob_start();
    try {
        $return = $callback();
        if ($return) {
            ob_end_flush();
        }
        else {
            ob_end_clean();
        }
        return $return;
    }
    catch (Exception $e) {
        ob_end_flush();
        throw $e;
    }
}

function say($what) {
    printf('%s%s', $what, PHP_EOL);
}

function select_value($sql, $parameters = array()) {
    return execute($sql, $parameters, PDO::FETCH_COLUMN, 0)->fetch();
}

function select_values($sql, $parameters = array()) {
    return execute($sql, $parameters, PDO::FETCH_COLUMN, 0)->fetchAll();
}

function select_row($sql, $parameters = array()) {
    return execute($sql, $parameters, PDO::FETCH_BOTH)->fetch();
}

function select_rows($sql, $parameters = array()) {
    return execute($sql, $parameters, PDO::FETCH_BOTH)->fetchAll();
}

function execute($sql, $parameters = array(), $fetch_mode = null, $column = 0) {
    global $connection;

    say("-- $sql");
    $statement = $connection->prepare($sql);
    if ($fetch_mode == PDO::FETCH_COLUMN) {
        $statement->setFetchMode($fetch_mode, $column);
    }
    else if (!is_null($fetch_mode)) {
        $statement->setFetchMode($fetch_mode);
    }

    $mark = time();
    $statement->execute($parameters);
    $stop = time();
    say(sprintf("   -> %ss", $stop - $mark));

    if (is_null($fetch_mode)) {
        $return = $statement->rowCount();
        say("   -> $return rows affected");
    }
    else {
        $return = $statement;
    }

    return $return;
}

function migrate($migrations) {
    global $argv;

    $is_redo = in_array('REDO', $argv);
    $is_undo = in_array('UNDO', $argv);
    $is_down = $is_undo || in_array('DOWN', $argv);

    $step = array_reduce(
        $argv,
        function ($result, $item) {
            @list($name, $value) = explode('=', $item);
            return $name == 'STEP' ? $value : $result;
        },
        $is_redo || $is_undo ? 1 : -1);

    if ($is_down || $is_redo) {
        do_migrate(array_reverse($migrations), $step, true);
    }

    if (!$is_down || $is_redo) {
        do_migrate($migrations, $step, false);
    }
}

function do_migrate($migrations, $step, $is_down) {
    foreach ($migrations as $migration) {
        $migrated = __ob(function () use ($migration, $is_down) {
            list($version, $comment, $up, $down) = $migration;

            say("== $comment");
            $migrated = migrated($version);
            if ($migrated && $is_down) {
                down($version, $down);
                return true;
            }
            else if (!$migrated && !$is_down) {
                up($version, $up);
                return true;
            }
            else {
                return false;
            }
        });
        if ($migrated && --$step == 0) {
            break;
        }
    }
}

function migrated($version) {
    return select_value('SELECT 1 FROM schema_migrations WHERE version=?', array($version)) == 1;
}

function down($version, $down) {
    return transact(function () use ($version, $down) {
        $down();
        execute('DELETE FROM schema_migrations WHERE version=?', array($version));
    });
}

function up($version, $up) {
    return transact(function () use ($version, $up) {
        $up();
        execute('INSERT INTO schema_migrations VALUES (?)', array($version));
    });
}

function transact($transaction) {
    global $connection;

    try {
        $connection->beginTransaction();
        $return = $transaction();
        $connection->commit();

        return $return;
    }
    catch (Exception $e) {
        $connection->rollBack();
        throw $e;
    }
}
