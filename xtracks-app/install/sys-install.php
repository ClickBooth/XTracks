<?php

class SystemInstaller
{
	static public function _doInstall($install_db)
	{
	    $install_dir = dirname(__FILE__);

        $sql_setup = explode("\n\n", file_get_contents("{$install_dir}/install.sql"));

        foreach($sql_setup as $sql)
        {
            $sql = trim($sql);
            if (empty($sql)) {
                continue;
            }

            $sql = str_replace('%install_db%', $install_db, $sql);
            if (db::execute($sql) < 1) {
                if ($error = mysql_error()) {
                    printf("Error: %s\n", $error);
                    printf("SQL: %s\n\n", $sql);
                }
            }
        }
	}
    static public function makeInstall()
    {
    	global $dbname;
    	
    	self::_doInstall($dbname);
    	
    	db::execute("USE $dbname");
    	db::execute("INSERT IGNORE INTO 202_config (user_id, var_name, var_value) VALUES
    	(0, 'mode', 'single')");
    	
    }
    static public function makeInstallation($subdomain, $affiliate_id, $prefix='prosper_')
    {
        $install_db = sprintf("`$prefix%s`", $subdomain);
        
        self::_doInstall($install_db);
        
        
        
        self::importUser($affiliate_id, $install_db);

        return true;
    }

    static public function markDone($install_id, $affiliate_id)
    {
        $s_id = db::escape($install_id);
        $res = db::execute("UPDATE prosper_master.install_jobs
                            SET status='done'
                            WHERE id='{$s_id}'");
                  
        return $res && db::execute("
            INSERT INTO prosper_master.installs
            (subdomain, affiliate_id, status)
            SELECT subdomain, $affiliate_id, 'active'
            FROM prosper_master.install_jobs ij
            WHERE ij.id = '$s_id'
        ");
    }

    static public function checkStatus($install_id)
    {
        $s_id = db::escape($install_id);
        return db::getRow("select status from prosper_master.install_jobs WHERE install_id='{$s_id}'");
    }

    static public function importUser($affiliate_id, $install_db)
    {
        // Grab user from directtrack db.
        //$s_addcode = db::escape($pub);
        $user = db::getRow("select * from prosper_master.affiliates WHERE affiliate_id='{$affiliate_id}'");

		//md5 the user pass with salt
	 	$user_pass = salt_user_pass($_SESSION['login_pass']);
		$mysql['user_pass'] = db::escape($user_pass);

		//insert this user
		$user_sql = "  	INSERT INTO {$install_db}.`202_users`
					    	SET	user_email='".$user['email']."',
					    		user_name='".$user['addCode']."',
					    		user_pass='".$mysql['user_pass']."',
					    		addCode='".$user['addCode']."',
					    		user_timezone='-5',
					    		user_time_register=NOW()";//die($user_sql);
		$user_result = db::execute($user_sql);

		$user_id = mysql_insert_id(db::$db_write);
		$mysql['user_id'] = db::escape($user_id);
		$mysql['affiliate_id'] = $user['affiliate_id'];

        $md5token = md5(serialize($user).uniqid());
        $_SESSION['authtoken'] = $md5token;
		db::execute("insert into prosper_master.login_tokens(affiliate_id, user_id, user_name, token)
		             values ('".$mysql['affiliate_id']."', '".$mysql['user_id']."', '".$user['addCode']."', '".$md5token."');");

		//update user preference table
		$user_sql = "INSERT INTO {$install_db}.`202_users_pref` SET user_id='".$mysql['user_id']."'";
		$user_result = db::execute($user_sql);
	}
}
