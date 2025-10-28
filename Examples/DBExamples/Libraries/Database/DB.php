<?php
/**
 * @author selcukmart
 * 3.02.2022
 * 14:17
 */

namespace Examples\DBExamples\Libraries\Database;

use FormGenerator\Tools\DB\DBInterface;
use PDO;

/**
 * Class DBExample
 * @package FormGenerator\Tools\DB
 * @author selcukmart
 * @desc it is only example, it is not a professional solution for DB operations.
 * 3.02.2022
 * 14:53
 */
class DB implements DBInterface
{
    private static $instance;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = DBConnect::connect();
        }
        return self::$instance;
    }

    public static function getRow($column, $id, $table): array
    {
        $dbh = self::getInstance();
        $sql = "SELECT * FROM $table WHERE $column='$id' LIMIT 1";
        return $dbh->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    public static function fetch($query): array
    {
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public static function rowCount($query): int
    {
        $dbh = self::getInstance();
        $dbh->query($query->queryString);
        return $dbh->query("SELECT FOUND_ROWS()")->fetchColumn();
    }

    public static function query($sql)
    {
        $dbh = self::getInstance();
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $sth = $dbh->prepare($sql);
        $sth->execute();
        return $sth;
    }

    public static function select($where_arr, $table)
    {
        $and_sql = self::whereSQLGenerate($where_arr);
        $sql = "SELECT * FROM $table $and_sql";
        return self::query($sql);
    }

    public static function getAllFieldOptions($table): array
    {
        $sql = "SHOW COLUMNS FROM $table";
        $query = self::query($sql);
        $fields = [];
        foreach ($query as $row) {
            $fields[$row['Field']] = $row;
        }
        return $fields;
    }

    private static function whereSQLGenerate(array $arr)
    {
        $sql = "";
        $size = count($arr);
        if ($size > 0) {
            $end = "#";
            $operator = '=';
            $s = 0;
            foreach ($arr as $column => $value) {
                $s++;
                if ($s === $size) {
                    $end = "";
                }
                if (!is_array($value)) {
                    $value = addslashes($value);
                    $sql .= $column . $operator . "'" . $value . "' " . $end;
                } else {
                    $sql .= $column . " IN (" . self::implodeSQL($value) . ") " . $end;
                }
            }
            $sql = str_replace("#", " AND ", trim($sql, "#"));
        }
        if (!empty($sql)) {
            $sql = " WHERE " . $sql;
        }
        return $sql;
    }

    private static function implodeSQL(array $arr): string
    {
        $imp = '';
        foreach ($arr as $value) {
            $imp .= "'" . $value . "',";
        }
        return rtrim($imp, ',');
    }
}