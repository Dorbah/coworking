<?php

include_once 'procs.php';

$domain='VLADIMIR\\';


	function mssql_base( $mssqlbase = 'coworking' ) {
		return $mssqlbase;
	}

	#Функция тупо подключается к MSSQL базе и возвращает дескриптор подключения
	function mssql_conn() {
		# Переменные для подключения к БД
		$mssqlhost = 'sqlcl';			# Хост
		$mssqlbase = mssql_base();	# БД
		$mssqllogn = 'sqlguest'; 	# Логин 
		$mssqlpass = 'sqlguest'; 	# Пароль
		
		try { # Пытаемся подключиться
			$DBH = new PDO("dblib:host=$mssqlhost;dbname=$mssqlbase", "$mssqllogn", "$mssqlpass");
			return $DBH;# Возвращаем дескриптор
		} catch(PDOException $e) {  
			die('<font color=red><b>MSSQLERR:'.$e->getMessage().'</b></font><br>');# Упадаем в ужасе
		}
	}#end func mssql_conn


	#Подключение к LDAP
	function ldap_conn() {
	
		$connect = ldap_connect("10.33.1.8", "389") or die("ldap_conn: Could not connect to LDAP server.");
		ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);  
		ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);  

		return $connect;
	}#end func ldap_conn



	# binding to ldap server
	function bind_ldap($ldapconn, $ldap_username="VLADIMIR\\LAMP", $ldap_password="09udfg;l45") {
		
		if (empty($ldapconn)) {$ldapconn=ldap_conn();}
		$res = ldap_bind($ldapconn, $ldap_username, $ldap_password); # or die ("Error trying to bind: ".ldap_error($ldapconn));
		
		return $res;
	}#end func bind_ldap


	function ldap_base() {
		$ldap_base="dc=vld,dc=msk-center,dc=companyname,dc=local";
		
		return $ldap_base;
	}#end func ldap_base



	#Определяем атрибуты юзера из LDAP по логину
	function ldap_user($login) {
		$login=cut_domain($login);
		# Переменные для подключения к LDAP
		$connect = ldap_conn();#ОТКРЫЛИ
		$bind = bind_ldap($connect);
		#$read = ldap_search($connect, $config['ldap_base'], "(&(sAMAccountName=$login))", array("sAMAccountName","cn","title","department","company","mail","physicaldeliveryofficename","telephonenumber","mobile"));
		$ldap_base=ldap_base();
		$read = ldap_search($connect, $ldap_base, "(&(sAMAccountName=$login))", array("sAMAccountName","cn","title","department","company","mail","physicaldeliveryofficename","telephonenumber","mobile"));
		
		#if ($read==FALSE) { message('ldap_user: Неверно указаны логин или пароль !!!'); exit; }
		$result = ldap_get_entries($connect, $read);
		ldap_close($connect);#ЗАКРЫЛИ
		
		return $result;
	}#end func ldap_user








?>
