<?php
/**
 * @author selcukmart
 * 8.02.2022
 * 22:31
 */

namespace Examples\DBExamples\Libraries\Database;

class DBConnect
{

    private static $instance;

    public function __construct()
    {
    }

    public static function connect()
    {
        if (is_null(self::$instance)) {
            if (defined('SQLDRIVER') && defined('SQLHOST') && defined('DBNAME') && defined('DBUSER') && defined('DBPASS')) {
                /**
                 * SQL CONNECTION OPERATIONS
                 */
                $dsn = SQLDRIVER . ':host=' . SQLHOST . ';dbname=' . DBNAME;
                $dbOptions = [
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ];

                try {
                    $dbh = new \PDO($dsn, DBUSER, DBPASS, $dbOptions);
                } catch (\PDOException $e) {
                    throw new \PDOException($e->getMessage(), (int)$e->getCode());
                }
                $dbh->query("SET NAMES 'utf8'");
                $dbh->query("SET CHARACTER SET utf8");
                $dbh->query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
                self::$instance = $dbh;
                /**
                 * SQL BAĞLANTI İŞLEMLERİ SONU
                 */
            } else {
                throw new \RuntimeException("No connection information");
            }
        }
        return self::$instance;
    }

    public function __destruct()
    {

    }
}