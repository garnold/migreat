<?php

function database_exists($database_name) {
    return select_value('SELECT 1 FROM information_schema.SCHEMATA WHERE SCHEMA_NAME=?', array($database_name)) == 1;
}

function create_database($database_name) {
    return execute("CREATE DATABASE $database_name");
}

function table_exists($table_name) {
    return select_value('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=SCHEMA() AND TABLE_NAME=?', array($table_name)) == 1;
}

function create_schema_migrations_table() {
    return execute('CREATE TABLE schema_migrations(version VARCHAR(255) UNIQUE)');
}
