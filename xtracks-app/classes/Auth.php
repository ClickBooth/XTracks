<?php

class Auth
{
    static public function getClientSite()
    {
        list($subdomain, $domain) = explode('.', $_SERVER['HTTP_HOST'], 2);
        return strtolower($subdomain);
    }

    static public function getDomain()
    {
        list($subdomain, $domain) = explode('.', $_SERVER['HTTP_HOST'], 2);
        return strtolower($domain);
    }

    static public function isValidDTLogin($user, $pass, $encrypted=false)
    {
        // TODO: Make call with file()
        return false;
    }

    static public function login($user, $pass)
    {
        $subdomain = self::getClientSite();
        global $mode;
        if ($mode != 'single' && (!client_installed($subdomain) || $subdomain == 'auth')) {
            // Do a quick lookup to see if this login was correct.
            if (self::isValidDTLogin($user, $pass, true)) {
                // Pass some kind of authentication details. Also verify that
                // they haven't already claimed a subdomain.
                $_SESSION['login_user'] = $user;
                $_SESSION['login_pass'] = $pass;

                forward("/new-subdomain.php");
                exit;

            } else {
                die("No such luck.");
            }
        }

        $mysql = array();
        $mysql['user_name'] = db::escape($user);
        $mysql['user_pass'] = db::escape(salt_user_pass($pass));
        $mysql['subdomain'] = db::escape($subdomain);

        //check to see if this user exists
        $user_sql = "SELECT *
                      FROM 	202_users
                      WHERE user_name='".$mysql['user_name']."'
                      AND   user_pass='".$mysql['user_pass']."'";

        $user_row = db::getRow($user_sql);

        if (!$user_row) {
            throw new Exception('Your username or password is incorrect.');
        }

        //set session variables
        $_SESSION['session_fingerprint'] = md5('session_fingerprint' . $_SERVER['HTTP_USER_AGENT'] . session_id());
        $_SESSION['session_time'] = time();
        $_SESSION['user_name'] = $user_row['user_name'];
        $_SESSION['user_id'] = $user_row['user_id'];
        $_SESSION['addCode'] = $user_row['addCode'];
        $_SESSION['user_api_key'] = $user_row['user_api_key'];
        $_SESSION['user_stats202_app_key'] = $user_row['user_stats202_app_key'];
        $_SESSION['user_timezone'] = $user_row['user_timezone'];
        return true;
    }

    static public function recordLogin()
    {
        //RECORD THIS USER LOGIN, into user_logs
		$mysql['login_server'] = db::escape( serialize($_SERVER) );
		$mysql['login_session'] = db::escape( serialize($_SESSION) );
		$mysql['login_error'] = db::escape( serialize($error) );
		$mysql['ip_address'] = db::escape( $_SERVER['REMOTE_ADDR'] );

		$mysql['login_time'] = time();

		if ($error) {
			$mysql['login_success'] = 0;
		} else {
			$mysql['login_success'] = 1;
		}

	    //record everything that happend during this crime scene.
		$user_log_sql = "INSERT INTO   202_users_log
								   SET user_name='".$mysql['user_name']."',
										user_pass='".$mysql['user_pass']."',
										ip_address='".$mysql['ip_address']."',
										login_time='".$mysql['login_time']."',
										login_success = '".$mysql['login_success']."',
										login_error='".$mysql['login_error']."',
										login_server='".$mysql['login_server']."',
										login_session='".$mysql['login_session']."'";
		$user_log_result = mysql_query($user_log_sql) or record_mysql_error($user_log_sql);

        if (!$error) {

            $ip_id = INDEXES::get_ip_id($_SERVER['HTTP_X_FORWARDED_FOR']);
            $mysql['ip_id'] = mysql_real_escape_string($ip_id);

            //update this users last login_ip_address
            $user_sql = "	UPDATE 	202_users
                            SET			user_last_login_ip_id='".$mysql['ip_id']."'
                            WHERE 	user_name='".$mysql['user_name']."'
                            AND     		user_pass='".$mysql['user_pass']."'";
            $user_result = _mysql_query($user_sql);
        }
    }

	static public function logged_in()
	{
		$session_time_passed = time() - $_SESSION['session_time'];

        // Tricky logic for handing off authentication across subdomains.
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['login_user'])
            && isset($_GET['auth'])) {

            $s_token = db::escape($_GET['auth']);
            $user_row = db::getRow("SELECT lt.*, a.addCode from prosper_master.login_tokens lt
                               INNER JOIN prosper_master.affiliates a ON lt.affiliate_id=a.affiliate_id
                               WHERE token='{$s_token}'");

            $_SESSION['session_fingerprint'] = md5('session_fingerprint' . $_SERVER['HTTP_USER_AGENT'] . session_id());
            $_SESSION['session_time'] = time();
            $_SESSION['user_name'] = $user_row['user_name'];
            $_SESSION['user_id'] = $user_row['user_id'];
            $_SESSION['addCode'] = $user_row['addCode'];
            $_SESSION['user_api_key'] = @$user_row['user_api_key'];
            $_SESSION['user_stats202_app_key'] = @$user_row['user_stats202_app_key'];
            $_SESSION['user_timezone'] = @$user_row['user_timezone'];

            @db::execute("delete from prosper_master.login_tokens WHERE token='{$s_token}' LIMIT 1");

            $uri = preg_replace('/auth=[a-zA-Z0-9]+/', '', $_SERVER['REQUEST_URI']);
            forward($uri);
            exit;
        }

        if (!isset($_SESSION['user_id']) && isset($_SESSION['login_user'])) {
            if (self::login($_SESSION['login_user'], $_SESSION['login_pass']))
                return true;
        }

		if  ($_SESSION['user_name']
		AND $_SESSION['user_id']
		AND ($_SESSION['session_fingerprint'] == md5('session_fingerprint' . $_SERVER['HTTP_USER_AGENT'] . session_id()))
		AND ($session_time_passed < 50000)) {
			$_SESSION['session_time'] = time();
			return true;
		} else {
			return false;
		}
	}

	static public function require_user()
	{
		if (Auth::logged_in() == false) {
			 die(include_once($_SERVER['DOCUMENT_ROOT']. '/xtracks-access-denied.php'));
		}
	}

    /*
	function require_valid_api_key()
	{
		$user_api_key = $_SESSION['user_api_key'];
		if (AUTH::is_valid_api_key($user_api_key) == false) {
			header('location: /xtracks-account/api-key-required.php'); die();
		}
	}

	function require_valid_app_key($appName, $user_api_key, $user_app_key)
	{
		if (AUTH::is_valid_app_key($appName, $user_api_key, $user_app_key) == false) {
			header('location: /xtracks-account/app-key-required.php'); die();
		}
	}


	//this checks if this api key is valid
	function is_valid_api_key($user_api_key)
	{
		$url = TRACKING202_API_URL . "/auth/isValidApiKey?apiKey=$user_api_key";

		//check the XTracks api authentication server
		$xml = getUrl($url);
		$isValidApiKey = convertXmlIntoArray($xml);
		$isValidApiKey = $isValidApiKey['isValidApiKey'];

		//returns true or false if it is a valid key
		if ($isValidApiKey['isValid'] == 'true') 	return true;
		else 									return false;
	}

	//this checks if the application key is valid
	function is_valid_app_key($appName, $user_api_key, $user_app_key)
	{
		switch ($appName) {
			case "stats202": // check to make sure this is a valid stats202 app key
				$url = TRACKING202_API_URL . "/auth/isValidStats202AppKey?apiKey=$user_api_key&stats202AppKey=$user_app_key";
				$xml = getUrl($url);
				$isValidStats202AppKey = convertXmlIntoArray($xml);
				$isValidStats202AppKey = $isValidStats202AppKey['isValidStats202AppKey'];

				if ($isValidStats202AppKey['isValid'] == 'true') 	return true;
				else 											return false;

				break;
		}
	}
	*/

	function set_timezone($user_timezone)
	{
		if (isset($_SESSION['user_timezone'])) {
			$user_timezone = $_SESSION['user_timezone'];
		}

		if ($user_timezone == '-12') { @putenv('TZ=NZS-12NZD'); }
		if ($user_timezone == '-11') { @putenv('TZ=SST11'); }
		if ($user_timezone == '-10') { @putenv('TZ=HST10HDT'); }
		if ($user_timezone == '-9') { @putenv('TZ=AKS9AKD'); }
		if ($user_timezone == '-8') { @putenv('TZ=PST8PDT'); }
		if ($user_timezone == '-7') { @putenv('TZ=MST7MDT'); }
		if ($user_timezone == '-6') { @putenv('TZ=CST6CDT'); }
		if ($user_timezone == '-5') { @putenv('TZ=EST5EDT'); }
		if ($user_timezone == '-4') { @putenv('TZ=AST4ADT'); }
		if ($user_timezone == '-3.5') { @putenv('TZ=NST3:30NDT'); }
		if ($user_timezone == '-3') { @putenv('TZ=BST3'); }
		if ($user_timezone == '-2') { @putenv('TZ=FST2FDT'); }
		if ($user_timezone == '0') { @putenv('TZ=Europe/London'); }
		if ($user_timezone == '1') { @putenv('TZ=Europe/Paris'); }
		if ($user_timezone == '2') { @putenv('TZ=Asia/Istanbul'); }
		if ($user_timezone == '3') { @putenv('TZ=Asia/Kuwait'); }
		if ($user_timezone == '3.5') { @putenv('TZ=Asia/Tehran'); }
		if ($user_timezone == '5.5') { @putenv('TZ=IST-5:30'); }
		if ($user_timezone == '7') { @putenv('TZ=Asia/Bangkok'); }
		if ($user_timezone == '8') { @putenv('TZ=Asia/Hong_Kong'); }
		if ($user_timezone == '9') { @putenv('TZ=Asia/Tokyo'); }
		if ($user_timezone == '9.5') { @putenv('TZ=Australia/Darwin'); }
		if ($user_timezone == '10') { @putenv('TZ=Australia/Sydney'); }
		if ($user_timezone == '12') {  @putenv('TZ=Pacific/Auckland'); }

	}
}
