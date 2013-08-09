<?php

function database_exists($database_name) {
    return select_value('SELECT 1 FROM pg_database WHERE datname=?', array($database_name)) === 1;
}

function create_database($database_name) {
    return execute("CREATE DATABASE $database_name");
}

function table_exists($table_name) {
    return select_value('SELECT 1 FROM pg_tables WHERE schemaname=CURRENT_SCHEMA() AND tablename=?', array($table_name)) === 1;
}

function create_schema_migrations_table() {
    return execute('CREATE TABLE schema_migrations(version VARCHAR(255) UNIQUE)');
}
