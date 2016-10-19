<?php
	# TEST
	include_once 'procs.php';
	
	$module='coworking';
	$dbg=true;# Отладку включить/выключить
	$PHP_AUTH_USER=strtoupper($_SERVER['PHP_AUTH_USER']);
	if ($dbg) {
		ini_set('display_errors','On'); #включаем отображение ошибок если выключено
		error_reporting(E_ALL | E_STRICT); #устанавливаем режим отображения - все ошибки и советы
	}#end if
	
	
	

	
	
	
	
	
	
	
	$login=IOFamilia($PHP_AUTH_USER);
	$user_id=add_user_from_ldap($login); #Регаем/обновляем юзера из LDAPа, если его ещё нету в базе
	if ( empty($user_id) ) { message("ID пользователя не найден"); exit; }
	
	$user_info=get_user_info($login);# Узнаем из базы ФИО юзера чтобы не дёргать LDAP
	$FIO=$user_info['last_name'].' '.$user_info['first_name'].' '.$user_info['second_name'];
	
	
	
	
	htmlhead("$FIO [$login]");
	$tab="\t\t\t\t\t\t\t";
	echo "$tab<!-- START index.php -->\r\n";
	
	$ramka=''; if ($dbg) {$ramka='border=3 bordercolor=lime';}
	echo "$tab<TABLE $ramka width='100%' style='height:100%;' cellspacing=0 cellpadding=0>\r\n";
	echo "$tab	<TBODY>\r\n";
	echo "$tab		<TR valign='top'>\r\n";
	echo "$tab			<TD width='1%'>\r\n";
	echo "$tab				<div class='area' id='menu'>\r\n";
	show_menu($login); #Показаем меню
	echo "$tab				</div>\r\n";
	echo "$tab			</TD>\r\n";
	
	echo "$tab			<TD>\r\n";
	echo "$tab				<DIV id='main' class='area' style='height:99%;'>";
	echo "$tab					<noscript>Включите JavaScript в браузере</noscript>";
	
	# Пытаемся показать таблицу с этими правами доступа
	#echo "$tab				<script>pg('edit_table.php','main','tbl=zaya');</script>";
	
	
	echo "$tab				</DIV>\r\n"; #Тут выведем DIVчик main
	echo "$tab			</TD>\r\n";
	echo "$tab		</TR>\r\n";
	echo "$tab	</TBODY>\r\n";
	echo "$tab</TABLE>\r\n";
	htmlfoot();
	
?>