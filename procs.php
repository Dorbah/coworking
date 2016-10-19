<?php
	include_once 'config.php';
	#«Пишите код так, как будто сопровождать его будет склонный к насилию психопат, который знает, где вы живёте» (C) Стив Макконнел
	#>Злые юзеры, так и норовят что нить сломать :-D
	#Программист должен учитывать все возможные, ряд невозможных и пару невероятных ситуаций.
	
	# В начало константу ставить хочешь ты :-D (C) 'Мастер'===$Йода
	
# Буратино дали три яблока. Два он съел.
# Сколько яблок осталось у Буратино?
# Думаете одно? Ничего подобного.
# Никто же не знает сколько у него уже было яблок до этого.
# Мораль - обнуляйте переменные !!!
	
	
	
	
	#Функция запрета кеширования скриптов и стилей
	# На входе список подключаемых файлов с относительными путями вида:
	# $arr_files=array('js/ajax.js', 'styles.css');
	# Использование: echo deny_cache($arr_files);
	function deny_cache($arr_files){
		$module='deny_cache';
		
		if (empty($arr_files) ) { echo "$module: Список файлов пустой"; return false;}
		
		$tab="\t\t";
		$str='';#Вернём HTML в строке
		foreach($arr_files as $filename) {
			clearstatcache(TRUE, $filename);
			if (file_exists($filename)) {
				$mod_time=filemtime($filename);
				
				$ext = substr($filename, strrpos($filename, '.')+1);
				switch ($ext) {
					case 'js':
						$str.="$tab<script type='text/javascript' src='$filename?$mod_time'></script>";
						break;
						
					case 'css':
						$str.="$tab<link rel='stylesheet' type='text/css' href='$filename?$mod_time' media='all'/>";
						break;
				}#end switch
				
				$str.="\r\n";
			} else { $str.="$tab<!-- $module: File '$filename' not found -->";	}
			#end if
		}#end foreach
		
		return $str;
	}#end func deny_cache
	
	
	
	function htmlhead($FIO='') {
		$dbg=FALSE; $title='Система совместного редактирования';
		$ramka=''; if ($dbg) {$ramka='border=1 bordercolor=red';}
		
		echo "<!-- START htmlhead -->
<HTML>
	<HEAD>
		<Title>$title</Title>
		<meta http-equiv='content-type' content='text/html; charset=UTF8'>\r\n";
		
		echo deny_cache(array('js/ajax.js', 'styles.css')); #массив подключаемых файлов с относительными путями
		
		$curr_dir=getcwd();
		if ($r_pos=strrpos($curr_dir, '/')) { $curr_dir=substr($curr_dir, $r_pos+1);}	# отрезаем всё до слэша
		
		$color='white';
		if ('test'==$curr_dir) {$color='#6A5ACD';}
		
echo "	</HEAD>
	<BODY leftmargin='0' topmargin='0' rightmargin='0' bottommargin='0' bgcolor='$color'>
		<!-- START DIV body -->
		<DIV id='body'>
			<TABLE $ramka width='100%' style='height:99%;' cellspacing='0' cellpadding='0'>
				<TBODY>
					<TR style='height: 1%;'>
						<TD colspan=2>
							<div style='width: 100%; height: 75px; background-color: #003577; background-image: url(img/bg-top.jpg); background-repeat: repeat-y;'>
								<table width='100%' cellpadding='0' cellspacing='0'>
									<tr style='height: 100%;' align='center' valign='top'>
										<td style='width: 5%;'>
											<img src='img/logo.png' style='height: 75px; width: 100px;'>
										</td>
										<td style='width: 95%;'>
											<div style='width: 100%; height: 100%;'>
												<div style='color: white;  width: 100%; z-index:0; position:relative;' align='right'>$FIO</div>
												<div style='color: $color; width: 100%; z-index:1; position:relative;'><H2>$title</H2></div>
											</div>
										</td>
									</tr>
								</table>
							</div>
						</TD>
					</TR>
					<TR style='height:98%;' align='center' valign='middle'>
						<TD width='98%' align='center'>
							<!-- END htmlhead -->\r\n";
	}#end func htmlhead
	
	
	
	
	#Функция выводит меню
	# Можно пунктами меню выводить таблицы в зависимости от доступа пользователя
	# Делаем запрос - выбираем все таблицы, куда у юзера есть доступ
	function show_menu($login=''){
		$dbg=FALSE; $module='Show_menu'; $tab="\t\t\t\t\t\t\t\t\t\t\t\t";
		echo "$tab<!-- START $module -->\r\n";
		
		#msg("$module: var=$var", $dbg); ###
		
		msg("$module: login=$login", $dbg); ###
		
		$userID=get_userID_by($login);
		msg("$module: userID=$userID", $dbg); ###
		
		$qry ="SELECT DISTINCT description, tiny_name";
	  $qry.=" FROM [coworking].[dbo].[user_access] ua";
	  $qry.=" LEFT JOIN [coworking].[dbo].[tables] t ON ua.table_id=t.id";
	  $qry.=" WHERE user_id=$userID";
		
		msg("$module: qry=$qry", $dbg); ###

		$STH=query($qry);
		
		while ($row=$STH->fetch(PDO::FETCH_ASSOC) ) {
			$description=$row['description'];
			msg("$module: description=$description", $dbg); ###
			
			$description=str_replace(' ', '&nbsp;', $description);
			
			$tiny_name=substr($row['tiny_name'], 0, 4);
			msg("$module: tiny_name=$tiny_name", $dbg); ###
			
			#echo "<a href='#' onclick=\"pg('edit_table.php','main','tbl=zaya');\">Заявления</a><br>\r\n";
			echo "<a href='#' onclick=\"pg('edit_table.php','main','tbl=$tiny_name');\">$description</a><br>\r\n";
		}#end while
		
		echo "$tab<!-- END $module -->\r\n";
	}#end func show_menu
	
	
	
	
	
	function htmlfoot() {
		echo "							<!-- START htmlfoot -->
						</TD>
					</TR>
				<TBODY>
			</TABLE>
		</DIV><!-- END DIV body -->
	</BODY>
</HTML>";
	}#end func htmlfoot
	
	
	
	
	
	
	
	
	#Отладочная функция для вывода значения переменной
	function show_var($var, $var_name='var') {
		echo "<pre>$var_name=";
		var_dump($var);
		echo '</pre>';
	}#end func show_var
	
	
	function bold($mess) {
		return "<b>$mess</b>";
	}#end func bold
	
	
	function color($mess, $color) {
		return "<font color=$color>$mess</font>";
	}#end func color
	
	
	function script($scrpt) {
		echo "<script>$scrpt</script>\r\n";
	}#end func script
	
	
	function alert($mess) {
		script("alert(\"$mess\");");
	}#end func alert
	
	
	#Выводим сообщение в текст страницы
	#$mess - текст сообщения
	#$color - цвет текста
	#$bold - жирным шрифтом (true) или нет (false)
	function message($mess, $color='red', $bold=true) {
		$msg=$mess;
		if ($bold) {$msg=bold($mess);}
		echo color($msg, $color)."<br>\r\n";
	}#end func message
	
	
	#Функция выводит сообщение только если $debug=TRUE
	function msg($msg, $debug, $color='red') {
		if ($debug) {message($msg, $color); }
	}#end func msg
	
	
	
	
	
	
	
	
	
	#Функция делает все буквы до первой точки
	# и первую букву после первой точки заглавными
	# Логин в виде "IO.Familia" или "I.Familia"
	function IOFamilia($login) {
		if (strpos($login, '.')===FALSE) {
			$FIO=strtoupper($login[0]).substr($login, 1);# Familia
		} else {#Если есть точка
			$FIO=explode('.', $login);#Раскукоживаем
			$FIO[0]=strtoupper($FIO[0]);# IO
			$FIO[1]=strtoupper($FIO[1][0]).strtolower(substr($FIO[1], 1));# Familia
			$FIO=implode('.', $FIO); #Скукоживаем
		}#end if
		
		return $FIO;
	}#end func 
	
	
	# Функция вырезает всё, кроме латинских букв a-z, A-Z, цифр 0-9 и символов _ . - \
	function filter_aZ09($val) {
		return preg_replace('/[^a-z0-9_\\.\\-\\\\]+/i', '', $val);
	}#end func filter_aZ09
	
	
	# Функция вырезает всё, кроме латинских букв a-f, A-F и цифр 0-9
	function filter_hex($val) {
		return preg_replace('/[^a-f0-9]+/i', '', $val);
	}#end func filter_hex
	
	
	
	#Функция вырезает из строки всё кроме цифр и разделителей . / -
	# Нужна для фильтрации принимаемых от юзера данных
	function filter_date($val) {
		return preg_replace('#[^0-9./\\-]+#', '', $val);
	}#end func filter_date
	
	
	#Функция вырезает из строки всё кроме цифр и разделителей . : -
	# Нужна для фильтрации принимаемых от юзера данных
	function filter_time($val) {
		return preg_replace('#[^0-9:]+#', '', $val);
	}#end func filter_time

	
	#Функция вырезает из строки всё кроме цифр и разделителей . / : -
	# Нужна для фильтрации принимаемых от юзера данных
	function filter_datetime($val) {
		return preg_replace('#[^0-9./:-]+ #', '', $val);
	}#end func filter_time

	
	# Функция вырезает из строки всё кроме больших и маленьких букв
	function get_letters($val) {
		return preg_replace('/[^a-z]+/i', '', $val);
	}#end func get_letters
	
	
	# Функция добавляет слеши, заменяет HTML теги кодами и переводит в UTF-8
	# Это нужно для безопасной записи строки в таблицу и вывода потом в браузер
	function make_harmless($val) {
		return htmlentities(addslashes($val), ENT_QUOTES, "UTF-8");
	}#end func make_harmless
	
	# Функция убирает все бэкслеши
	function remove_slashes($string){
		$string=implode("",explode("\\",$string));
		return html_entity_decode(stripslashes(trim($string)), ENT_QUOTES,  "UTF-8");
	}#end func remove_slashes
	
	
	#Функция вырезает из строки всё кроме цифр и возвращает целое значение
	# Нужна для фильтрации принимаемых от юзера данных
	# Максимальное значение 2147483647=2^31-1
	function get_int_val($val) {
		return intval(preg_replace('/[^0-9]+/', '', $val));
	}#end func get_int_val
	
	
	
	
	
	#Функция возвращает логин без домена
	function cut_domain($login){
		$dbg=FALSE; $module='cut_domain';
		
		#Обрезание доменного имени из учетной записи
		#$last_name = preg_replace('/.*?\\\/', '', $login);

		
		$user_name=$login; # Берём логин
		if ($r_pos=strrpos($login, '\\')) { $user_name=substr($login, $r_pos+1);}	# отрезаем всё до бэкслэша (домен)
		msg("$module: login='$login' user_name='$user_name'", $dbg);
		
		return $user_name;
	}#end func cut_domain
	
	
	
	
	
	#Функция возвращает ФИО пользователя из LDAP
	# или пустую строку если не находит
	function get_user_fio($login) {
		$FIO='';
		$login=cut_domain($login);
		
		#Запрашиваем данные из LDAP
		$ldap_info = ldap_user($login);		#Получение данных по пользователю из AD
		$FIO=$ldap_info[0]['cn'][0];
		
		return $FIO;
	}#end func get_user_fio
	
	
	
	
	#Функция ищет юзера по логину в таблице users
	# и возвращает строку инфы в массиве
	# или пустой массив если не находит
	function get_user_info($login) {
		$arr_info=array();
		$login=strtoupper(cut_domain($login));
		
		$STH=search('users', 'UPPER([login])=:login', '', array(':login', $login));	#Ищем юзера по логину
		$arr_info=$STH->fetch();
		
		return $arr_info;
	}#end func get_user_info
	
	
	
	
	# Функция возвращает IDшник юзера из таблицы users
	# по его логину или 0 если не найден
	function get_userID_by($login) {
		$user_id=0; $login=cut_domain($login);
		
		$bind=array(':login', strtoupper($login));
		$STH=search('users', 'UPPER([login])=:login', '', $bind);	#Ищем юзера по логину
		if ($row=$STH->fetch()) {										# Если нашли
			$user_id=$row['id'];										# Получаем IDшник
		}# end if
		
		#Можно сделать лучше так:
		#$arr_info=get_user_info($login);
		#$user_id=$arr_info['id'];
		
		return $user_id;
	}#end func get_userID_by
	
	
	
	
	
	
	# Функция возвращает логин юзера из таблицы users
	# по его IDшнику или '' если не найден
	function get_login_by_ID($user_id) {
		$login='';
		$STH=search('users', 'id=:id', '', array(':id', $user_id));	#Ищем юзера по IDшнику
		if ($row=$STH->fetch()) {						# Если нашли
			$login=$row['login'];						# Получаем логин
		}# end if
		
		return $login;
	}#end func get_login_by_ID
	
	
	
	
	
	#Функция возвращает уровень доступа из базы по логину
	function access_level($login) {
		$arr_info=get_user_info($login);
		return $arr_info['supervisior'].$arr_info['admin'];
	}#end func access_level
	
	
	#Функция проверяет признак supervisior в users по логину
	#Возвращает TRUE если supervisior=1
	# и FALSE в остальных случаях
	function is_supervisior($login) {
		$arr_info=get_user_info($login);
		return (1==$arr_info['supervisior']);
	}#end func is_supervisior
	
	
	#Функция проверяет признак admin в users по логину
	#Возвращает TRUE если admin=1
	# и FALSE в остальных случаях
	function is_admin($login) {
		$arr_info=get_user_info($login);
		return (1==$arr_info['admin']);
	}#end func is_admin
	
	
	
	
	
	#Функция заменяет один разделитель на другой
	# И добавляет по бокам каждому элементу дополнительные символы
	# На входе 'elem1,elem2,...'
	# На выходе например '[elem1],[elem2],...'
	# Нужна для insert и send_mail
	function change_delim($left='', $text, $delim_old, $delim_new, $right='') {
		return $left.implode(explode($delim_old, $text), $delim_new).$right; #Раскукоживаем и закукоживаем
	}#end func change_delim
	
	
	
	
	function get_field_list($table) {
		$dbg=FALSE; $module='get_field_list';
		msg("$module: START", $dbg); ###
		
		$mssqlbase = mssql_base();
		$qry="SELECT sc.name
					FROM syscolumns sc, sysobjects so
					WHERE so.Name = '$table'
					AND sc.id = so.id";
		$STH_ch=query($qry);
		$all_rows = $STH_ch->fetchAll(PDO::FETCH_COLUMN, 0);
		if ($dbg) { show_var($all_rows, "all_rows"); }###
		
		msg("$module: END", $dbg, 'green'); ###
		return $all_rows;
	}
	
	
	#Функция быстрого вывода массива в заголовок таблицы
	function show_HEAD($row) {
		echo '<TR><TH>'.implode($row,'</TH><TH>').'</TH></TR>';
	}#end func show_HEAD
	
	#Функция быстрого вывода массива в строку таблицы
	function show_ROW($row) {
		echo '<TR><TD>'.implode($row,'</TD><TD>').'</TD></TR>';
	}#end func show_ROW
	
	# Функция пробегает по STH уже выполненного запроса и выводит в заголовок
	#  названия столбцов таблицы и все строки таблицы
	function show_rows($STH) {
		$row_ch = $STH->fetch(PDO::FETCH_ASSOC);
		if ( empty($row_ch) ) {
			echo "<TR><TD>Ничего не найдено</TD></TR>"; return;
		}
		$arr_keys=array_keys($row_ch);
		show_HEAD($arr_keys);
		do {
			show_ROW($row_ch);
		} while ($row_ch = $STH->fetch(PDO::FETCH_ASSOC));
	}#end func show_rows
	
	function show_STH($table_name, $STH) {
		echo "<TABLE border=1>";
		echo "<caption>".bold($table_name)."</caption>";
		show_rows($STH);
		echo "</TABLE>";
	}#end func show_STH
	
	function show_table($table_name) {
		$STH_ch=search($table_name);
		show_STH($table_name, $STH_ch);
	}#end func show_table
	
	
	
	
	
	
	
	
	
	
	
	#Выполнить запрос к базе
	#$select - строка SQL запроса
	#$arr - параметры запроса в (одно)/(дву)мерном массиве [("ключ1","значение1")[,("ключ2","значение2")[,()...]]]
	# если $arr не задано, то выполняется запрос без параметров
	#$query_attr - аттрибуты запроса в массиве вида, например
	#  array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY) - делать PDO::Fetch() только вперёд или
	#  array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL)  - чтобы получить прокручиваемый курсор PDO::fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_*) и
	#  задавать направление выборки через PDO::FETCH_ORI_* :
	#   PDO::FETCH_ORI_NEXT, PDO::FETCH_ORI_PRIOR, PDO::FETCH_ORI_FIRST, PDO::FETCH_ORI_LAST, PDO::FETCH_ORI_ABS, PDO::FETCH_ORI_REL
	
	##################################################################################################
	#  Интересные варианты прибиндить параметры к запросу без $STH->bindValue($key, $val);
	#	$data = array('Cathy', '9 Dark and Twisty Road', 'Cardiff');  
	#	$STH = $DBH->prepare("INSERT INTO folks (name, addr, city) values (?, ?, ?)");  
	#	$STH->execute($data); 
	#
	#	$data = array( 'name' => 'Cathy', 'addr' => '9 Dark and Twisty', 'city' => 'Cardiff' );  
	#	$STH = $DBH->prepare("INSERT INTO folks (name, addr, city) values (:name, :addr, :city)");  
	#	$STH->execute($data);
	##################################################################################################
	function query($select, $arr=array(), $query_attr=array() ) {
		$dbg=FALSE;
		
		if (!empty($select)) { #Если select задан
			$DBH=mssql_conn();#Цепляемся к базе
			$STH = $DBH->prepare($select, $query_attr);#Готовим запрос
			
			if (!empty($arr)) {#Если есть параметры запроса, то привязываем
				$ext_key=''; $ext_val=''; $ext_flag=true;#Флаг внешнего цикла
				foreach($arr as $line) {
					$key=''; $val=''; $flag=true;#Флаг внутреннего цикла
					if (is_array($line)) {#Если $line вложенный массив, тогда
						foreach($line as $info) {#Выковыриваем переменные по одной
							if ($flag) {$key=$info;} else {$val=$info;}
							$flag=! $flag;#Инвертируем флаг для записи во вторую переменную
						}#end foreach $line
					}#end if is_array
					else {#Попытаемся достать данные из первого массива
						if ($ext_flag) {$ext_key=$line;} else {$ext_val=$line;}
						$ext_flag=! $ext_flag;#Инвертируем флаг для записи во вторую переменную
					}#end else
					
					#Теперь на выходе получаем пару key:value
					$key=(!empty($key)) ? $key: $ext_key;
					if ($key[0]!=':') {$key=':'.$key;} #Если вдруг забыл : перед именем параметра
					$val=(!empty($val)) ? $val: $ext_val;
					$STH->bindValue($key, $val);#Добавим параметры в запрос
				}#end foreach $arr
			}#end if !empty $arr
		
			$res=$STH->execute();#Выполняем запрос
			
			if (empty($res)) {
				msg("Query: Error in sql=$select", $dbg);
				if ($dbg) { show_var($arr, 'arr');}
				$STH=FALSE;
			}#end if
			
			unset($DBH); # $DBH = NULL; #Освобождаем дескриптор подключения к базе
			
			return $STH; #Возвращаем ссылку на результат запроса
		}#end if !empty $select
		else {message('query:EMPTY select !!!'); die();} #Матюгаемся на пустой запрос и упадаем
		
	}#end func query



	#Функция выполняет запрос к таблице $table с условием $where
	# упорядоченные по $order с заданными параметрами $bind
	#$query_attr - аттрибуты запроса в массиве вида, например
	#  array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY) - делать PDO::Fetch() только вперёд или
	#  array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL)  - чтобы получить прокручиваемый курсор
	#  PDO::fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_*)
	function search($table, $where='', $order='', $bind=array(), $query_attr=array() ) {
		if (!empty($table)) {
			$mssqlbase = mssql_base();
			$sel="SELECT * FROM [$mssqlbase].[dbo].[$table]";
			if (!empty($where)) {$sel.=' WHERE '.$where;}
			if (!empty($order)) {$sel.=' ORDER BY '.$order;}
			$STH=query($sel, $bind, $query_attr);
		} else {message('Search: Имя таблицы не задано !!!'); exit;}
		
		return $STH;
	}#end func search
	
	
	
	
	
	#Функция вставляет в таблицу $table поля $fields='field1,field2...' значения $bind
	#Пример использования:
	#$STH=insert('reg_access', 'user_id,region_id,result,req_date', $bind);
	function insert($table, $fields, $bind) {
		$dbg=FALSE; $module='Insert';
		if (empty($table)) {message("$module: Имя таблицы не задано !!!"); exit;}
		if (empty($fields)) {message("$module: Поля таблицы '$table' не заданы !!!"); exit;}
				
		$mssqlbase = mssql_base();
		$fields_brackets=change_delim('[', $fields, ',', '],[', ']');
		$fields_values=change_delim(':', $fields, ',', ',:', '');
		$query="INSERT INTO [$mssqlbase].[dbo].[$table] ($fields_brackets) VALUES ($fields_values)";
		msg("$module:qry=$query", $dbg);###
		if ($dbg) { show_var($bind, 'bind'); } ###
		$STH=query($query, $bind); # Делаем INSERT
		
		return $STH;
	}#end func insert
	
	
	
	
	
	#Функция подготавливает поля текста запроса для функции update
	# На входе  'result,date'
	# На выходе [result]=:result, [req_date]=:req_date
	function prep_fields_for_upd($text) {
		$res='';
		$arr=explode(',', $text);
		foreach($arr as $val) { $res.="[$val]=:$val, "; }
		$res=substr($res, 0, -2); #Отрезаем последние лишние ', '
		
		return $res;
	}#end func prep_fields_for_upd
	
	
	#Функция обновляет в таблице $table поля $fields='field1,field2,...' с условием $where значения $bind
	#Пример использования: update('reg_access', 'result,req_date', 'id=:id', $bind);
	#####################################################################
	#       В массиве $bind названия переменных для бинда :var					#
	#     ДОЛЖНЫ ТОЧНО СООТВЕТСТВОВАТЬ НАЗВАНИЯМ ПОЛЕЙ ТАБЛИЦЫ !!!			#
	#####################################################################
	function update($table, $fields, $where, $bind) {
		$dbg=FALSE; $module='Update';
		if (empty($table)) {message("$module: Имя таблицы не задано !!!"); exit;}
		if (empty($fields)) {message("$module: Поля таблицы $table не заданы !!!"); exit;}
		if (empty($where)) {message("$module: Условие WHERE для таблицы '$table' не задано !!!"); exit;}
		
		$mssqlbase = mssql_base();
		$upd_fields=prep_fields_for_upd($fields); #'result,req_date' => '[result]=:result, [req_date]=:req_date'
		$sel="UPDATE [$mssqlbase].[dbo].[$table] SET $upd_fields WHERE $where";
		$STH=query($sel, $bind); # Делаем UPDATE
		msg("$module:qry=$sel", $dbg, 'magenta');
		if ($dbg) {show_var($bind, "$module:bind");}
		if ($dbg) {show_var($STH, "$module:STH");}
		
		return $STH;
	}#end func update
	
	
	
	
	
	#Функция удаляет из таблицы $table строки
	#соответствующие условию $where с параметрами $bind
	function delete($table, $where, $bind) {
		$dbg=FALSE; $module='Delete';
		
		if (empty($table)) {msg("$module: Имя таблицы не задано !!!", $dbg); exit;}
		if (empty($where)) {msg("$module: Условие WHERE для таблицы '$table' не задано !!!", $dbg); exit;}
		$mssqlbase = mssql_base();
		$sel="DELETE FROM [$mssqlbase].[dbo].[$table] WHERE $where;";
		if ($dbg) {show_var($sel, "$module:sel"); show_var($bind, "$module:bind");}###
		$STH=query($sel, $bind);
		
		return $STH;
	}#end func delete
	
	
	
	#Удаляем строку $line_id из таблицы $table
	function DelLine($table, $line_id) {
		$dbg=FALSE; $module='DelLine';
		
		$STH=delete($table, 'id=:id', array(':id', $line_id));		#Удалим строку $line_id из таблицы $table
		if ($STH) { msg("$module: Line '$line_id' deleted from '$table'", $dbg, 'pink'); } else {msg("$module: Line '$line_id' NOT DELETED from '$table'", $dbg);} ###
		
		return $STH;
	}#end func DelLine
	
	
	
	
	
	
	
	
	
	# Функция добавляет юзера в users из LDAP по логину
	#  если он там не существует или обновляет если есть
	#  и возвращает его IDшник
	function add_user_from_ldap($login) {
		$dbg=FALSE; $module='add_user_from_ldap'; $user_id=0; $login=cut_domain($login);

		$ldap_info=ldap_user($login);#Получаем данные юзера из LDAP
		if ($dbg) { show_var($ldap_info, "$module:ldap_info"); } ###
		$arr_FIO=explode(' ', $ldap_info[0]['cn'][0]);
		if ($dbg) { show_var($arr_FIO, "$module:arr_FIO"); } ###
		$fields='login,last_name,first_name,second_name,enabled';
		$bind=array(array(':login', $login),
								array(':last_name', $arr_FIO[0]),
								array(':first_name', $arr_FIO[1]),
								array(':second_name', $arr_FIO[2]),
								array(':enabled', 1)
								);
		if ($dbg) { show_var($bind, "$module:bind"); } ###
		
		$user_id=get_userID_by($login); #Если не найден, то вернёт 0
		if ($dbg) { show_var($user_id, "$module:user_id"); } ###
		if ($user_id!=0) {# Если юзер найден
			$STH=update('users', $fields, "id=$user_id", $bind); # Тогда надо обновить запись по IDшнику
			msg("$module: Updated $login", $dbg);###
		} else { # А если НЕ найден
			$STH=insert('users', $fields, $bind); #Добавляем юзера в таблицу
			$user_id=get_userID_by($login);# Узнаём IDшник только что добавленного юзера
			msg("$module: Inserted $login", $dbg);###
		}#end if
		
		return $user_id;
	}#end func add_user_from_ldap
	
	
	
	
	

?>