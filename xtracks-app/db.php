<?php

class db
{
    static public $db_read;
    static public $db_write;

    static public function init($host, $user, $pass, $db)
    {
        $dbh = self::connect($host, $user, $pass, $db);

        self::$db_read = $dbh;
        self::$db_write = $dbh;
    }

    static public function connect($host, $user, $pass, $db)
    {
        if ($conn = mysql_connect($host, $user, $pass)) {
        	if (empty($db)) return $conn;
            if (mysql_select_db($db, $conn)) {
                return $conn;
            } else {
                throw new Exception("Failed to select db '{$db}'");
            }
        }

        return false;
    }

    static public function execute($sql)
    {
        if ($res = mysql_query($sql, self::$db_write)) {
            return mysql_affected_rows(self::$db_write);
        } else {
            if ($error = mysql_error(self::$db_write)) {
                throw new Exception("Write error: $error");
            }
        }
        return false;
    }

    static public function getRow($sql)
    {
        $res = @mysql_query($sql, self::$db_read);

        if ($res) {
            return mysql_fetch_assoc($res);
        } else {
            if ($error = mysql_error(self::$db_read)) {
                throw new Exception("Read error: $error");
            }
        }
        return false;
    }

    static public function getRows($sql)
    {
        $rows = array();
        $res  = mysql_query($sql, self::$db_read);

        if ($res) {
            while($row = mysql_fetch_assoc($res)) {
                $rows[] = $row;
            }
            return $rows;
        }
        return false;
    }

    static public function escape($var)
    {
        if (is_array($var)) {
            foreach($var as $k=>$v) {
                $var[$k] = mysql_real_escape_string($v, self::$db_read);
            }
        } else {
            $var = mysql_real_escape_string($var, self::$db_read);
        }
        return $var;
    }
}