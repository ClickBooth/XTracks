<?php

class Config
{
    static public $instance = null;

    private $_config = array();
    private $_uid = 0;

    public function __construct($user_id)
    {
        $s_uid = db::escape($user_id);
        $configs = db::getRows("select * from 202_config WHERE user_id IN ('$s_uid', 0)");
        
        foreach($configs as $config)
        {
            $this->_config[$config['var_name']] = $config['var_value'];
        }
    }

    public function __get($name)
    {
        return $this->_config[$name];
    }

    public function __set($name, $value)
    {
        $s_name = db::escape($name);
        $s_val = db::escape($value);
        $s_uid = db::escape($this->_uid);

        return db::execute("REPLACE INTO 202_config (var_name, var_value, user_id)
                            VALUES ('$s_name', '$s_val', '$s_uid')");
    }

    static public function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Config($_SESSION['user_id']);
        }
        return self::$instance;
    }
}