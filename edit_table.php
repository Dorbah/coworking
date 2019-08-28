<?php
	include_once 'procs.php';
	$module='edit_table'; # Модуль редактирования таблиц
	$script_name='edit_table.php';
	$dbg=FALSE;
	
	$pagenum_param='pagenum'; # Название параметра номера страницы
	$rows_per_page=15;				# Строк на странице
	
	$mssql_base=mssql_base();
	
	$PHP_AUTH_USER=strtoupper($_SERVER['PHP_AUTH_USER']);
	########################################################
	#if ('USERNAME'===$PHP_AUTH_USER) { $dbg=TRUE; }
	########################################################
	
	if ($dbg) {# Если режим отладки
		ini_set('display_errors','On'); #включаем отображение ошибок если выключено
		error_reporting(E_ALL | E_STRICT); #устанавливаем режим отображения - все ошибки и советы
	}#end if
	
	
	$err='';
	################################################################################################################
	#  ГОТОВО - Сделать автоматическое получение типов, длины и названий полей и также Lookup полей
	#           Сделать автоматическое определение ширины столбцов и задание ширины столбцов по умолчанию
	#           Сделать нормальное сохранение текстовых полей с кавычками и апострофами
	#           Можно сделать чтобы при клике по ячейке таблицы появлялось поле для редактирования input type=text
	#             А затем по событию onchange записывалось и опять становилось обычным текстом с событием onclick
	#             Лучше повесить onClick на все ячейки таблицы, в обработчике смотреть куда кликнули
	#							и там выводить поле для редактирования
	################################################################################################################
	
	
	
	
	
	
	# Сюда будет прилетать СОКРАЩЁННОЕ название таблички
	# Будем делать запрос, смотреть
	if ( isset($_REQUEST['tbl']) ) {
		$tbl=substr($_REQUEST['tbl'], 0, 4); # Прилетело название таблицы
		msg("$module: tbl=$tbl", $dbg);###
	} else { message("$module: Название таблицы не задано"); exit; } #Дальше нечего ловить
	
	
	
	#Тут мы в зависимости от сокращённого названия таблички задаём русские названия для столбцов
	# уровень редактирования и тип столбца таблицы для представления данных
	# Уровень редактирования 0-не показывать, 1-просмотр, 2-редактирование
	# Тип поля для редактирования Integer, String, Date, R(DateTime), Time, Binary, LookUp listbox
	# <input type="color">    # Для IE нужен HTML Color Picker
	# <input type="datetime"> # В IE и Chrome не поддерживается - придётся преобразовывать в строку и обратно
	$allow_add_line=FALSE;		# Разрешить добавление новой строки
	$allow_delete_line=FALSE;	# Разрешить УДАЛЕНИЕ строки
	$order='id';							# Сортировка по умолчанию по полю ID
	switch  ($tbl) { 
		case 'zaya':	$table='zayavleniya'; $table_title='Заявления';
									$order='data'; $allow_add_line=TRUE; #$order='data,id';
									$fields=array('id'=>array('ID',									'0','I','id'),
																'dt'=>array('Дата/Время',					'2','R','data'),
																'ac'=>array('Л/С',								'2','S','abon_acc_num'),
																'nu'=>array('Номер(а) абонента',	'2','S','abon_numbers'),
																'nm'=>array('Наименование',				'2','S','abon_name'),
																#'tm'=>array('Тема',								'2','S','theme'),# Позже это поле надо убрать
																'ti'=>array('Тема',								'2','L','theme_id', 'themes', 'theme'),# LookUp field
																#'st'=>array('Статус',							'2','S','status'),
																'si'=>array('Статус',							'2','L','status_id', 'statuses', 'status'),# LookUp field
																'ee'=>array('Сотрудник ЕКЦ',			'2','S','ekc_emp'),
																'ei'=>array('Сотрудник ЕКЦ ID',		'2','L','ekc_emp_id', 'users', 'last_name'),# LookUp field
																#'cs'=>array('Статус контроля',		'2','S','control_status'),
																'ci'=>array('Статус контроля',		'2','L','control_status_id', 'statuses', 'status'),# LookUp field
																#'me'=>array('Сотрудник МСС',			'2','S','mcc_emp'), # , 'users', 'last_name'
																'mi'=>array('Сотрудник МСС',			'2','L','mcc_emp_id', 'users', 'last_name'),
																'cm'=>array('Комментарий',				'2','S','comment'),
																);
																
									break;
		
		case 'thms':	$table='themes'; $table_title='Темы заявлений';
									$allow_add_line=TRUE; $allow_delete_line=TRUE; #$order='data,id';
									$fields=array('id'=>array('ID',									'0','I','id'),
																'tm'=>array('Тема заявления',			'1','S','theme'),
																);
									break;
		
		case 'stat':	$table='statuses'; $table_title='Статусы заявлений';
									$allow_add_line=TRUE; $allow_delete_line=TRUE; #$order='data,id';
									$fields=array('id'=>array('ID',									'0','I','id'),
																'st'=>array('Статус заявления',		'1','S','status'),
																);
									break;
		
		case 'usac':	$table='user_access'; $table_title='Доступы пользователей к таблицам и полям';
									$order='user_id,table_id'; $allow_add_line=TRUE; $allow_delete_line=TRUE;
									$fields=array('id'=>array('ID',							'0','I','id'),
																'ui'=>array('Пользователь',		'1','L','user_id', 'users', 'login'),
																'ti'=>array('Таблица',				'1','L','table_id', 'tables', 'description'), #table_name
																'fi'=>array('Поле',						'1','L','field_id', 'fields', 'field_name'),
																'at'=>array('Доступ',					'1','L','access_type_id', 'access_types', 'description'),
																);
									break;
		
		case 'usrs':	$table='users'; $table_title='Пользователи системы';
									$allow_delete_line=TRUE; #$order='data,id';
									$fields=array('id'=>array('ID',					'1','I','id'),
																'lg'=>array('Логин',			'1','S','login'),
																'ln'=>array('Фамилия',		'1','S','last_name'),
																'fn'=>array('Имя',				'1','S','first_name'),
																'sn'=>array('Отчество',		'1','S','second_name'),
																'en'=>array('Показывать',	'2','B','enabled'),
																);
									break;
		
		case 'tbls':	$table='tables'; $table_title='Таблицы и их описания';
									$order='description'; $allow_add_line=TRUE; $allow_delete_line=TRUE; #$order='data,id';
									$fields=array('id'=>array('ID',								'0','I','id'),
																'ti'=>array('Сокр.имя(4симв)',	'1','S','tiny_name'),
																'tn'=>array('Имя таблицы',			'1','S','table_name'),
																'ds'=>array('Описание',					'1','S','description'),
																);
									break;
		
		case 'flds':	$table='fields'; $table_title='Поля таблиц';
									$allow_add_line=TRUE; $allow_delete_line=TRUE; #$order='data,id';
									$fields=array('id'=>array('ID',								'0','I','id'),
																'fn'=>array('Сокр.имя(4симв)',	'1','S','field_name'),
																'ds'=>array('Описание',					'1','S','description'),
																);
									break;
		
		case 'actp':	$table='access_types'; $table_title='Типы доступа к полям таблиц';
									#$allow_add_line=TRUE; #$allow_delete_line=TRUE; #$order='data,id';
									$fields=array('id'=>array('ID',							'0','I','id'),
																'tn'=>array('Символ',					'1','S','type_name'),
																'ds'=>array('Описание типа',	'1','S','description'),
																);
									break;
		
		case 'trpl':	$table='tar_plans'; $table_title='Тарифные планы';
									$allow_add_line=TRUE; #$allow_delete_line=TRUE; #$order='data,id';
									$fields=array('id'=>array('ID',							'0','I','id'),
																'tp'=>array('Тарифный план',	'1','S','tar_plan'),
																);
									break;
		
		case 'coac':	$table='contract_activation'; $table_title='Активация контрактов';
									$allow_add_line=TRUE; $allow_delete_line=TRUE; #$order='data,id';
									$fields=array('id'=>array('ID',								'0','I','id'),
																'cl'=>array('Цвет',							'2','C','color'),
																'ad'=>array('Дата активации',		'2','R','activation_date'), #DateTime
																'pn'=>array('Номер абонента',		'2','S','phone_num'),
																'rn'=>array('Номер RUIM',				'2','S','ruim_num'),
																'ab'=>array('ФИО абонента',			'2','S','abon_fio'),
																'ti'=>array('Тарифный план',		'2','L','tar_plan_id','tar_plans','tar_plan'), # NOT NULL
																'ei'=>array('Сотрудник',				'2','L','ekc_emp_id',	'users',		'last_name'),
																'cm'=>array('Комментарий',			'2','S','comment'),
																);
									break;
		
		case 'obra':	$table='obraschenia'; $table_title='Обращения по финансам';
									$allow_add_line=TRUE; $allow_delete_line=TRUE; #$order='data,id';
									$fields=array('id'=>array('ID',												'1','I','id'),
																'cl'=>array('Цвет',											'0','C','color'),
																'sd'=>array('Дата обращения',						'2','R','schet_date'),
																'ap'=>array('Номер счёта или телефон',	'2','S','accnum_phonenum'),
																'so'=>array('Суть обращения',						'2','S','sut_obraschenia'),
																'vi'=>array('Внёс',											'2','L','vnes_id',			'users',		'last_name'),
																'oi'=>array('Отработал',								'2','L','otrabotal_id',	'users',		'last_name'),
																'ri'=>array('Результат',								'2','L','result_id',		'statuses',	'status'),
																'cm'=>array('Комментарий',							'2','S','comment'),
																);
									break;
		
		
		
		
		default:			message("$module: Table '$tbl' not found"); exit;
	}#end switch
	
	
	
	

	if ($dbg) {
		show_table('tables'); echo "<br>"; #Выведем таблицу со списком таблиц с названиями столбцов
		show_table('user_access'); echo "<br>"; #Узнаем какие права есть у пользователя
		show_table('users'); echo "<br>";	#Выведем таблицу пользователей с названиями столбцов
		show_table('fields'); echo "<br>"; #Выведем таблицу с названиями столбцов
	}
	
	
	$login=IOFamilia($PHP_AUTH_USER);
	$user_id=get_userID_by($login);
	
	#Строим таблицу доступа к полям таблицы $table
	$mssqlbase = mssql_base();
	$qry ="SELECT ua.id, u.login, f.field_name, ua.access_type_id";
	$qry.=" FROM [$mssqlbase].[dbo].[user_access] ua";
	$qry.=" LEFT JOIN [$mssqlbase].[dbo].[users] u ON ua.user_id=u.id";
	$qry.=" LEFT JOIN [$mssqlbase].[dbo].[fields] f ON ua.field_id=f.id";
	$qry.=" WHERE ua.table_id=(select id from [$mssqlbase].[dbo].[tables] where table_name='$table')";
	$qry.=" AND ua.user_id=$user_id";
	
	msg("qry=$qry", $dbg); ###
	
	$STH=query($qry);
	###########################################################################
	#Как то некрасиво реализован этот кусок - надо перепилить и оптимизировать
	# Тут получаем виды доступа к полям таблицы $table
	$arr_access=array();
	$arr_access['id']='1';# Сразу блочим доступ к полю ID - только просмотр
	
	$row_ch = $STH->fetch(PDO::FETCH_ASSOC);
	$arr_keys=array_keys($row_ch);
	
	if ($dbg) {
		echo "<TABLE border='1'><caption><b>Сводная табличка</b></caption>";
		#show_var($arr_keys, 'arr_keys'); ###
		show_HEAD($arr_keys);
	}
	
	$wild_card=FALSE; $wild_card_access=0;
	do {
		if ($dbg) { show_ROW($row_ch); } ###
		$field_name			=	$row_ch['field_name'];
		$access_type_id	=	$row_ch['access_type_id'];
		if ('*'===$field_name) { # Если wild_card
			$wild_card=TRUE; $wild_card_access=$access_type_id;
		} else {
			$arr_access[$field_name]=$access_type_id;
		}
	} while ($row_ch = $STH->fetch(PDO::FETCH_ASSOC));
	
	if ($dbg) {
		echo "</TABLE>";
		show_var($arr_access, 'arr_access');
	}
	#
	###########################################################################
	
	
	
	
	# Если есть wild_card, то надо получить список всех полей таблицы $table
	$arr_field_access=array();
	if ( $wild_card ) {
		if ($dbg) { echo "<TABLE border=1><caption><b>Expanding wildcards (*)</b></caption>"; } ###
		
		$field_list=get_field_list($table);
		$cou=count($field_list);
		if ($dbg) { show_HEAD($field_list); }###
		$arr_acc=array_fill ( 0, $cou , $wild_card_access ); # Заполняем массив значениями доступа ко всем полям
		if ($dbg) { show_ROW($arr_acc); }###
		$arr_fld_acc=array_combine($field_list, $arr_acc);# Цепляем значения доступа к полям
		if ($dbg) { show_ROW($arr_fld_acc); }###
		# А теперь нужно доступ к остальным полям объединить с доступом wild_card
		$arr_field_access=array_merge($arr_fld_acc, $arr_access);
		if ($dbg) {
			show_ROW($arr_field_access);
			echo "</TABLE>";
		}
		
		#show_var($field_list, "$table:field_list($cou)");###
		#show_var($arr_acc, "$table:arr_acc");###
		#show_var($arr_fld_acc, "$table:arr_fld_acc");###
		#show_var($arr_field_access, "$table:arr_field_access");###	
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	# Удалить строку из таблицы по её IDшнику
	if ( $allow_delete_line ) {
		$del='';
		
		if (isset($_REQUEST['del'])) { # Если есть запрос на удаление
			$del=intval($_REQUEST['del']);# обшкуриваем до целого
			if ($dbg) {show_var($del, 'del');}###
			
			if (!empty($del)) {
				
	#######################################################################
	#
	#       Тут надо бы ещё проверять свою ли строку юзер удаляет
	#       Для этого надо к каждой таблице добавить поле CREATOR
	#
	#######################################################################
				
				$del_res=DelLine($table, $del);# Если не пустое - удаляем
				if (empty($del_res) ) {
					msg("$module: Ошибка удаления строки '$del' !", $dbg);
				} else {
					msg("$module: tbl='$tbl' Строка '$del' удалена", $dbg, 'lime');###
				}
			}
		}
	}#end if allow_delete_line
	
	
	
	
	
	
	
	
	
	#Получим названия полей таблицы и признак autoincrement для редактирования и для записи
	# Хотя названия у нас уже есть в массиве,
	# мы потом будем проверять существование названия этого поля в таблице
	/*
	$STH=query("SELECT sc.name, sc.colstat
							FROM syscolumns sc, sysobjects so
							WHERE so.Name = '$table'
							AND sc.id = so.id");
	*/
	$STH=query("SELECT inf.ORDINAL_POSITION,
											sc.name,
											sc.colstat,
											inf.DATA_TYPE,
											inf.CHARACTER_MAXIMUM_LENGTH,
											inf.NUMERIC_PRECISION
							FROM sysobjects so
							LEFT JOIN syscolumns sc ON sc.id = so.id
							LEFT JOIN INFORMATION_SCHEMA.COLUMNS inf ON inf.TABLE_NAME=so.Name AND inf.COLUMN_NAME=sc.name
							WHERE so.Name = '$table'");
							
	if (empty($STH) ) {
		message("$module: Получение списка полей таблицы '$table_title' не удалось");
		exit; # Ну или можно не выходить, а просто дизаблить поле ID , раз не получилось узнать тип поля
		
	} else {
		if ($row=$STH->fetch(PDO::FETCH_ASSOC)) {
			$arr_fields=array();
			
			do {
				$fld_nam=$row['name'];
				$arr_fields[$fld_nam]=array('colstat'=>$row['colstat'], # Признак автоинкрементного IDшника - записывать в это поле нельзя !!!
																		'ORDINAL_POSITION'=>$row['ORDINAL_POSITION'],
																		'DATA_TYPE'=>$row['DATA_TYPE'],
																		'CHARACTER_MAXIMUM_LENGTH'=>$row['CHARACTER_MAXIMUM_LENGTH'],
																		'NUMERIC_PRECISION'=>$row['NUMERIC_PRECISION'],
																		);
				
			} while ($row=$STH->fetch(PDO::FETCH_ASSOC));
			
		} else { message("$module: В таблице '$table_title' нет полей"); exit;}
	}#end else 
	#if ($dbg) {	show_var($arr_fields, 'arr_fields'); }###
	
	
	
	
	
	
	
	
	
	
	$set='';
	if (isset($_REQUEST['set']) ) {
		$set=substr(get_letters($_REQUEST['set']), 0, 2); # Получим 2 буквы сокр. название поля
		
		if ( isset($_REQUEST['line']) ) { # Если прилетел set, считываем остальные параметры
			$line=get_int_val( substr($_REQUEST['line'], 0, 10) );
			
			if ( isset($_REQUEST['val']) ) {
				$new_val=$_REQUEST['val']; # Получим новое значение поля
			} else { alert("$module: Значение поля не задано"); exit; }
			
		} else { alert("$module: Line_ID не задан"); exit; }
	}#end if $set
	
	
	
	
	
	########################################################################
	#   Готовим для вывода шапку таблицы с русскими заголовками столбцов
	$arr_tpl_lookup_listbox=array(); # Массив шаблонов списков выбора
	$arr_lookup_fields=array(); # Массив полей списков выбора и их значений вида $arr[$field_name]=array('$ID'=>$name, ...)
	$req='req='; # Сюда будем собирать переменные параметров для передачи в запрос
	$edit_line=''; #Собираем пустую строку таблицы для добавления новой строки
	$onClick=''; # Сюда соберём событие нажатия на кнопку Добавить
	$amp_sign=''; # Сюда потом запихнём знак AND
	$plus_sign=''; # Сюда потом запихнём знак +
	$arr_colors=array(0=>'grey', 1=>'red', 2=>'lime' ); # Для раскраски заголовков в зависимости от уровня доступа
	
	#    Выводим шапку таблицы с русскими заголовками столбцов
	# Соберём в переменную код шапки таблицы для вывода на страницу
	$tbl_hdr ="<TABLE border='1' align='center' width='100%'><TBODY>";
	$tbl_hdr.="<caption><b>$table_title</b></caption>\r\n";
	$tbl_hdr.="<TR>\r\n";
	foreach($fields as $key=>$value) {
		$field_caption=$value[0]; $edit_level=$value[1]; $val_type=$value[2]; $field_name=$value[3];
		
		# Проверим признак автоинкрементного поля
		#$autoinc=''; if ( array_key_exists($field_name, $arr_fields) ) { $autoinc=$arr_fields[$field_name]; }
		$autoinc=''; $ORD_POS=''; $DATA_TYPE=''; $CHAR_MAX_LEN=0; $NUM_PREC=0;
		if ( array_key_exists($field_name, $arr_fields) ) {
			$autoinc=$arr_fields[$field_name]['colstat'];
			$ORD_POS=$arr_fields[$field_name]['ORDINAL_POSITION'];
			$DATA_TYPE=$arr_fields[$field_name]['DATA_TYPE'];
			$CHAR_MAX_LEN=$arr_fields[$field_name]['CHARACTER_MAXIMUM_LENGTH'];
			$NUM_PREC=$arr_fields[$field_name]['NUMERIC_PRECISION'];
		}
		
		# В зависимости от уровня доступа к полю раскрасим заголовок
		$color='black'; # На случай если ничего не найдём, закрасим чёрным - сразу будет видно ошибку
		$field_access=$arr_field_access[$field_name];
		if ( array_key_exists($field_access, $arr_colors) ) {
			$color=$arr_colors[$field_access];
			$old_field_access=$fields[$key][1];
			if ( ('2'==$old_field_access) AND ($old_field_access!=$field_access) ) {
				$fields[$key][1]=$field_access; # Модифицируем изначально заданный уровень доступа  Он будет нужен при выводе строк таблицы
			}
		}
		
		if ('0'!==$edit_level ) { #Если показ НЕ запрещён - выводим заголовок столбца
			$fld_cap=$field_caption;
			$width=''; if ('id'===$key) { $width=" width='1%'"; }
			if ($dbg) {	$fld_cap.=":$autoinc:$val_type:$field_access:$ORD_POS:$DATA_TYPE:$CHAR_MAX_LEN:$NUM_PREC"; } ###
			$tbl_hdr.="<TH bgcolor='$color'$width>$fld_cap</TH>"; 
			
			# Готовим пустую строку для добавления
			$val_name='value'; # Тип получаемого значения из инпута - value или checked
			$input=''; $field_tiny_name=$key;
			# Если НЕ автоинкрементное поле, то добавим нормальный инпут
			if ( '0'==$autoinc ) {
				switch ($val_type) {#
					case 'S':	$input="<input type='text' class='inp' id='$key' width='100%'>";																			break;
					case 'I':	$input="<input type='number' class='inp' id='$key' width='100%'>";																		break;
					case 'D':	$input="<input type='date' class='inp' id='$key' width='100%' value='".date('Y-m-d')."'>";						break;
					case 'R':	$input="<input type='datetime' class='inp' id='$key' width='100%' value='".date('Y-m-d H:i:s')."'>";	break;
					case 'T':	$input="<input type='time' class='inp' id='$key' width='100%' value='".date('H:i:s')."'>";						break;
					case 'C':	$input="<input type='color' class='inp' id='$key' width='100%' value='#FFFFFF'>";											break;
					case 'B':	$input="<input type='checkbox' id='$key' width='100%'>";						$val_name='checked';							break;
					case 'L':	# Узнаём название таблицы, lookup поля и сокр.имя поля, для которого делается lookup
										$list_tbl_name=$value[4]; $list_field=$value[5]; $field_tiny_name="lst_".$key;
										
										$arr_list_fields=array(); # Готовим шаблон для поля LookUp listbox
										$STH_ch=search($list_tbl_name, '', $list_field); # Надо ли сортировать список выбора ? И по какому полю ?
										$input="<select id=\"[tiny_name]\" size=1>";
										$input.="<option value=\"0\" selected disabled style='color:red;'>[  Выберите  ]</option>";
										
										$row_ch = $STH_ch->fetch();
										$fld_enbl=''; $field_exists=FALSE;
										if ( isset($row_ch['enable']) ) { $fld_enbl='enable'; $field_exists=TRUE; }   # Если поле существует - возьмём его название
										if ( isset($row_ch['enabled']) ) { $fld_enbl='enabled'; $field_exists=TRUE; } # Если поле существует - возьмём его название
										
										do {
											$dsb=''; #Для запрета выбора отдельных элементов списка
											$ID=$row_ch[0];
											$name=trim($row_ch[$list_field]);
											
											if ( $field_exists AND '0'==$row_ch[$fld_enbl]) { $dsb=' disabled'; }	# Если значение этого поля не 1 - задизаблим строку
											
											$arr_list_fields[$ID]=$name;
											
											$opt_name="$name"; if ($dbg) { $opt_name="$ID $name"; }
											$input.="<option value=\"$ID\"$dsb>$opt_name</option>";
											
											
										} while ($row_ch = $STH_ch->fetch()); 
										
										$input.='</select>';
										
										$arr_tpl_lookup_listbox[$field_name]=$input;							# Тут получили html шаблон списка
										$arr_lookup_fields     [$field_name]=$arr_list_fields;		# Добавим массив ID=>значение в массив 
										
										# Теперь меняем параметр name='[tiny_name]' из выводимого в edit_line селекта
										$input = str_replace('[tiny_name]', $field_tiny_name, $input); # Меняем шаблон
										
										break;
										
					default:	$alert="$module: Field type '$val_type' NOT found !!!"; alert($alert); msg($alert, $dbg); exit;
				}#end switch
				
				$onClick.="$key=encodeURIComponent(document.getElementById(\"$field_tiny_name\").$val_name); \r\n";   ### Чтобы не портились русские буквы
				$req.="$plus_sign\"$amp_sign$key=\"+$key"; #Чтобы ID не добавлялся в INSERT, если он автоинкрементный
				$amp_sign='&'; $plus_sign='+'; # Теперь будем добавлять эти значки перед и после
			} else { # Поле с автоинкрементным IDшником - значит только просмотр
				$input='&nbsp;'; $fields[$key][1]=1;
			}
			
			$edit_line.="<TD>$input</TD>\r\n";
			
			
		}#end if
	}#end foreach
	$req.="+\"&tbl=$tbl&add=new\"; \r\n"; # добавляем в конце сокр.название таблицы "; "
	
	#$onClick.=$req."\r\nalert(\"OK\");";
	#$onClick.=$req."\r\npg(\"$script_name\", \"main\", req);";
	$onClick.=$req."pg(\"$script_name\", \"main\", req);";
	
	if ( $allow_add_line OR $allow_delete_line ) {
		$tbl_hdr.="<TH width='1%'>+</TH>";
	}
	$tbl_hdr.="</TR>\r\n";
	
	$add_btn="<img src='./img/check.png' width=16px style='height:16px;' border=0>";
	
	$edit_line.="<TD><a href='#' onClick='$onClick'>\r\n$add_btn</TD>\r\n";
	
	#message("onClick='$onClick'", 'green'); ###
	#message('edit_line="'.make_harmless($edit_line).'"', 'blue'); ###
	
	if ( $allow_add_line ) {
		$tbl_hdr.="<TR>$edit_line</TR>\r\n";
	}
	####################################################################################################################################################
	
	
	#show_var($arr_lookup_fields, 'arr_lookup_fields');###
	
	
	
	
	
	
	
	

	
	
	#########################################
	#   Шаблоны полей для редактирования    #
	#########################################
	# Color, Integer, String, Date, R(DateTime), Time, Binary, LookUp listbox
	# [tiny_name] - сокращённое название поля   # [line_id] - ID редактируемой строки   # [value] - начальное значение
	$script=" onchange='pg(\"$script_name\", \"[tiny_name][line_id]\", \"tbl=$tbl&set=[tiny_name]&line=[line_id]&val=\"+encodeURI(this.value));'";
	
	#######  Готовим шаблон для текстового поля
	#$onKeyDown="onKeyDown='this.size=this.value.length+3;'"; # AutoSize
	#$tpl_txt_field="<input type='text' id='it_[tiny_name][line_id]' size='[size]' value='[value]' style='width:100%;' $script $onKeyDown>";
	$tpl_txt_field="<input type='text' id='it_[tiny_name][line_id]' size='[size]' value='[value]' style='width:100%;' $script>";
	
	#######  Готовим шаблон для числового поля
	$tpl_num_field="<input type='number' id='it_[tiny_name][line_id]' size='[size]' value='[value]' style='width:100%;' $script>";
	
	#######  Готовим шаблон для цветового поля
	$tpl_clr_field="<input type='color' id='it_[tiny_name][line_id]' value='[value]' style='width:100%;' $script>";
	
	#######  Готовим шаблон для поля даты
	$tpl_date_field="<input type='date' id='it_[tiny_name][line_id]' size='[size]' value='[value]' style='width:100%;' $script>";
	
	#######  Готовим шаблон для поля даты-времени
	$tpl_datetime_field="<input type='datetime' id='it_[tiny_name][line_id]' size='[size]' value='[value]' style='width:100%;' $script>";
	
	#######  Готовим шаблон для поля времени
	$tpl_time_field="<input type='time' id='it_[tiny_name][line_id]' size='[size]' value='[value]' style='width:100%;' $script>";
	
	#######  Готовим шаблон для поля Binary
	$chkd_scrpt=" onclick='pg(\"$script_name\", \"[tiny_name][line_id]\", \"tbl=$tbl&set=[tiny_name]&line=[line_id]&val=[value]\");'";
	$tpl_bin_field="<input type='checkbox' [checked] $chkd_scrpt>";
	
	
	
	
	
	
	### Запись в базу отдельного поля
	if ( !empty($set) ) {  # set=rt&line=$res_id&val=123
		$mes=''; $field=''; $field_name='';
		
		if (array_key_exists($set, $fields) ) { # По сокращённому названию
			$field_name=$fields[$set][0];		# Получим русское название поля
			$field_type=$fields[$set][2];		# Получим тип поля
			$field     =$fields[$set][3];		# Расшифруем сокр. название поля
			
			if ($dbg) { show_var($field_name, 'field_name'); }###
			
			if (empty($line) ) {
				$err.="$module: LineID пустое"; $line=0;
			} else {
				$STH_srch=search($table, 'id=:id', '', array(':id', $line));# Ищем строку Line_ID в $table
				$row_srch = $STH_srch->fetch();
				if ( empty($row_srch) ) {
					$err.="$module: Строка с ID=$line не найдена";
				} else {
					$old_val=$row_srch[$field]; # Сохраним старое значение поля
				}#end if
				
				# Если новое значение пустое, то возвращаем старое и матюгаемся
				#if (empty($new_val) ) {
				#	$err.="$module: Поле \'$field_name\' не может быть пустым"; $val=$old_val;
				#} else {
					$val=$new_val;
				#}
				
				$DATA_TYPE='';
				if ( array_key_exists($field_name, $arr_fields) ) {
					$autoinc			=$arr_fields[$field_name]['colstat'];
					$DATA_TYPE		=$arr_fields[$field_name]['DATA_TYPE'];
					$ORD_POS			=$arr_fields[$field_name]['ORDINAL_POSITION'];
					$CHAR_MAX_LEN	=$arr_fields[$field_name]['CHARACTER_MAXIMUM_LENGTH']; ### Надо использовать чтобы ограничить длину текстовых строк
					$NUM_PREC			=$arr_fields[$field_name]['NUMERIC_PRECISION'];
				}
				
				msg("Прилетели tbl='$tbl'='$table_title' set='$set' line='$line' val='$val'", $dbg);###
				
				# Тут надо в зависимости от ТИПА поля его фильтровать
				# Color, Integer, String, Date, DateTime, Time, Binary, LookUp listbox
				switch ($field_type) {
					case 'C':	$val=substr(filter_hex($val), 0, 6); # Color - C0AFD3
										msg("Тип поля Color", $dbg);###
										$mes = str_replace('[value]',			'#'.$val,	$tpl_clr_field);	# Меняем шаблон цвета и добавляем решётку
										$mes = str_replace('[tiny_name]',	$set,			$mes); 						# Сокращённое название поля - чтобы враг не догадался
										$mes = str_replace('[line_id]',		$line,		$mes);
										break;
					
					case 'I':	$val=get_int_val($val); # Integer
										msg("Тип поля Integer", $dbg);###
										$mes = str_replace('[value]',			$val,		$tpl_txt_field);	# Меняем текстовый шаблон
										$mes = str_replace('[tiny_name]',	$set,		$mes); 						# Сокращённое название поля - чтобы враг не догадался
										$mes = str_replace('[line_id]',		$line,	$mes);
										$mes = str_replace('[size]',			strlen($val),	$mes);
										break;
					
					case 'S':	msg("Тип поля String", $dbg);### # String
										$mes = str_replace('[value]',			$val,		$tpl_txt_field);	#Меняем текстовый шаблон
										$mes = str_replace('[tiny_name]',	$set,		$mes); 						# Сокращённое название поля - чтобы враг не догадался
										$mes = str_replace('[line_id]',		$line,	$mes);
										$mes = str_replace('[size]',			strlen($val),	$mes);
										$val=make_harmless($val);
										break;
					
					case 'D':	$val=filter_date($val); # Date
										msg("Тип поля Date", $dbg);###
										$val=strtotime($val);
										if ( 'int'!=$DATA_TYPE) { # Проверим тип поля таблицы - Если не целое
											$val=date('Y-m-d', $val); # конвертируем в строку даты для MSSQL datetime
										}
										$mes = str_replace('[value]',			$val,		$tpl_date_field);	#Меняем шаблон даты
										$mes = str_replace('[tiny_name]',	$set,		$mes); 						# Сокращённое название поля - чтобы враг не догадался
										$mes = str_replace('[line_id]',		$line,	$mes);
										$mes = str_replace('[size]',			strlen($val),	$mes);
										break;
					
					case 'R':	$val=filter_datetime($val); # DateTime
										msg("Тип поля DateTime", $dbg);###
										$val=strtotime($val);
										if ( 'int'!=$DATA_TYPE) { # Проверим тип поля таблицы - Если не целое
											$val=date('Y-m-d H:i:s', $val); # конвертируем в строку даты для MSSQL datetime
										}
										$mes = str_replace('[value]',			$val,		$tpl_datetime_field);	#Меняем шаблон даты-времени
										$mes = str_replace('[tiny_name]',	$set,		$mes); 						# Сокращённое название поля - чтобы враг не догадался
										$mes = str_replace('[line_id]',		$line,	$mes);
										$mes = str_replace('[size]',			strlen($val),	$mes);
										break;
					
					case 'T':	$val=filter_time($val); # Time
										msg("Тип поля Time", $dbg);###
										$val=strtotime($val);
										if ( 'int'!=$DATA_TYPE) { # Проверим тип поля таблицы - Если не целое
											$val=date('H:i:s', $val); # конвертируем в строку даты для MSSQL datetime
										}
										$mes = str_replace('[value]',			$val,		$tpl_time_field);	#Меняем шаблон времени
										$mes = str_replace('[tiny_name]',	$set,		$mes); 						# Сокращённое название поля - чтобы враг не догадался
										$mes = str_replace('[line_id]',		$line,	$mes);
										$mes = str_replace('[size]',			strlen($val),	$mes);
										break;
					
					case 'B':	$val=substr($val, 0, 2);	# on или ничего не прилетает		# Binary, но на самом деле Integer
										msg("Тип поля Binary", $dbg);###
										$chkd=''; $set_val='1';
										if (!empty($val) ) { $chkd=" checked='checked'"; $set_val='0'; }
										$mes = str_replace('[value]',		$set_val,	$tpl_bin_field);	# Меняем двоичный шаблон
										$mes = str_replace('[tiny_name]',	$set,		$mes); 						# Сокращённое название поля - чтобы враг не догадался
										$mes = str_replace('[line_id]',		$line,	$mes);
										$mes = str_replace('[checked]',	$chkd,	$mes);
										break;
					
					case 'L':	$val=get_int_val($val); # LookUp listbox
										msg("Тип поля LookUp listbox", $dbg);###
										
										$tpl_list_field=$arr_tpl_lookup_listbox[$field]; # Получаем шаблон списка для этого поля
										$arr_list_fields=$arr_lookup_fields[$field];			# Получаем поля таблицы списка для проверки существования поля
										
										# Тут надо проверить, существует ли такой код в Lookup таблице
										if ( array_key_exists($val, $arr_list_fields) ) { # Есть такой id ?
											msg("Значение '$val' найдено ='".$arr_list_fields[$val]."'", $dbg);###
											$mes = str_replace('[line_id]',					$line,	$tpl_list_field); #Меняем шаблон
											$mes = str_replace(" selected", 				"",			$mes); # Снимаем выбор со строки по умолчанию
											$mes = str_replace("value=\"$val\"",		"value=\"$val\" selected", $mes); # Делаем выбранным нужный пункт списка
											$mes = str_replace('[tiny_name]',				$set, $mes);# Меняем шаблон
										} else { $err.="$module: Код '$val' для поля '$set' не найден"; }
										
										break;
										
					default:	$err.="$module: Тип поля \'$field_type\' не найден"; exit;
				}#end switch
				
				
				if (empty($err)) {# Если ошибок нет, то обновляем поле
				# id=:line_id чтобы можно было редактировать поле ID. А иначе получается запрос вида UPDATE $table SET id=:id WHERE id=:id
					$STH_upd=update($table, $field, 'id=:line_id', array( array(':line_id', $line), array(":$field", $val) ) ); # Запишем поле в базу
					
					#######################################################################################
					# ПЕРЕДЕЛАТЬ ЧТОБЫ НЕ НАДО БЫЛО ВСЮ СТРАНИЦУ ПЕРЕЗАГРУЖАТЬ
					# НАДО СДЕЛАТЬ ПЕРЕЗАПИСЬ ОТДЕЛЬНОЙ СТРОКИ ТАБЛИЦЫ С НОВЫМИ IDШНИКАМИ
					# ЛИБО ЗАПИСЫВАТЬ IDШНИК СТРОКИ В ОДНОМ МЕСТЕ И МЕНЯТЬ ЕГО ТАМ И ЧИТАТЬ ОТТУДА ПРИ ОТПРАВКЕ ПАРАМЕТРОВ
					if ( 'ID'===strtoupper($field) ) { #  После смены IDшника НУЖНО ПЕРЕГРУЖАТЬ ВСЮ СТРАНИЦУ (весь DIV main), чтобы сменить IDшники
						script("setTimeout(\"pg('edit_table.php', 'main', '&tbl=$tbl');\", 1);"); # всех TDшек в отредактированной строке
					}#end if
					#######################################################################################
				
				}
				
				echo $mes; # А тут из шаблона выведем поле с новым значением
				exit;
			}#end else NOT empty($line)
			
		} else { $err.="$module: Поле '$set' не найдено"; }
		
		if (empty($err)) {
			alert($err); #Выведем сообщение об ошибке
		}
		
	}#end else NOT empty($set)
	
	
	
	
	
	
	
	
	
	
	
	
	###  Добавление новой строки
	#    Сюда прилетают:    ВСЕ ПОЛЯ ЭТОЙ ТАБЛИЦЫ
	#    параметр add=new - флаг добавления новой строки
	#show_var($_REQUEST, '_REQUEST'); ###
	if ( isset($_REQUEST['add']) AND $allow_add_line ) {
		msg('Добавление новой строки', $dbg); ###
		if ($dbg) { show_var($_REQUEST, '_REQUEST'); }###
		
		$arr_fld_types=array(
												'C'=>array('Color',			'$val=filter_hex($val);'						),
												'I'=>array('Integer',		'$val=get_int_val($val);'						),
												'S'=>array('String',		'$val=make_harmless($val);'					),
												'D'=>array('Date',			'$val=strtotime(filter_date($val));'),
												'R'=>array('DateTime',	'$val=filter_datetime($val);'				),
												'T'=>array('Time',			'$val=filter_time($val);'						),
												'L'=>array('List',			'$val=get_int_val($val);'						),
												'B'=>array('Binary',		'$val=get_int_val($val);'						),
												);
		
		
		$ins_fields=''; $comma=''; $bind=array();
		
		msg("Смотрим какие поля прилетели", $dbg, 'blue'); ###
		foreach($fields as $key=>$value) {
			$field_caption=$value[0];
			$val_type=$value[2];
			$field_name=$value[3];
			
			#$edit_level=$value[1];
			$edit_level = $arr_field_access[$field_name];
			
			$DATA_TYPE='';
			if ( array_key_exists($field_name, $arr_fields) ) {
				$DATA_TYPE=$arr_fields[$field_name]['DATA_TYPE'];
			}
				
			if ($dbg) {
				show_var($key,						'key');###
				show_var($value,					"fields[$key]");###
				show_var($field_caption,	'field_caption');###
				show_var($edit_level,			'<b>edit_level</b>');###
				show_var($val_type,				'val_type');###
				show_var($field_name,			'field_name');###
				show_var($DATA_TYPE,			'DATA_TYPE');###
			}
			
			if ( '2'==$edit_level AND isset($_REQUEST[$key]) ) {
				msg("edit_level=2 AND isset(REQUEST[$key])=TRUE", $dbg, 'magenta'); ###
				
				$val=$_REQUEST[$key];
				
				if (array_key_exists($val_type, $arr_fld_types)) {
					$fld_typ=$arr_fld_types[$val_type][0];
					$oper=$arr_fld_types[$val_type][1];
				}
				
				if ($dbg) {
					show_var($val, 'val'); ###
					show_var($fld_typ, 'fld_typ'); ###
					show_var($oper, 'oper'); ###
				}
				
				msg("$fld_typ $key='$val' '$oper'", $dbg, 'green');###
				
				
				#Если в исполняемом коде присутствует ошибка, то eval() возвращает FALSE
				# и продолжается нормальное выполнение последующего кода.
				# If eval() is the answer, you're almost certainly asking the
				#   wrong question. -- Rasmus Lerdorf, BDFL of PHP
				$evl_res = eval($oper); # Отфильтруем в зависимости от типа поля
				if ($dbg && FALSE===$evl_res) {
					msg("ERROR in eval='$oper'", $dbg); ###
				}
				msg("val='$val'", $dbg, 'blue');###
				
				
				if ('D'==$val_type) {
					$val=strtotime($val);
					if ( 'int' != $DATA_TYPE) { # Проверим тип поля таблицы - Если не целое
						$val=date('Y-m-d', $val); # конвертируем в строку даты для MSSQL datetime
					}
				}
				
				if ('R'==$val_type) {
					$val=strtotime($val);
					if ( 'int'!=$DATA_TYPE) { # Проверим тип поля таблицы - Если не целое
						$val=date('Y-m-d H:i:s', $val); # конвертируем в строку даты для MSSQL datetime
					}
				}
				
				if ('T'==$val_type) {
					$val=strtotime($val);
					if ( 'int'!=$DATA_TYPE) { # Проверим тип поля таблицы - Если не целое
						$val=date('H:i:s', $val); # конвертируем в строку даты для MSSQL datetime
					}
				}
				
				
				/*
				switch ($val_type) { # Отфильтруем в зависимости от типа поля
					case 'C': msg("Color $key='$val'",		$dbg, 'lime');###
										$fld_typ='Color';
										$val=filter_hex($val);
										break;
					
					case 'I': msg("Integer $key='$val'",	$dbg, 'lime');###
										$fld_typ='Integer';
										$val=get_int_val($val);
										break;
					
					case 'S': msg("String $key='$val'",		$dbg, 'lime');###
										$fld_typ='String';
										$val=make_harmless($val);
										break;
					
					case 'D': msg("Date $key='$val'",			$dbg, 'lime');###
										$fld_typ='Date';
										$val=filter_date($val);
										$val=strtotime($val);
										if ( 'int'!=$DATA_TYPE) { # Проверим тип поля таблицы - Если не целое
											$val=date('Y-m-d', $val); # конвертируем в строку даты для MSSQL datetime
										}
										break;
					
					case 'R': msg("DateTime $key='$val'",	$dbg, 'lime');###
										$fld_typ='DateTime';
										$val=filter_datetime($val);
										break;
					
					case 'T': msg("Time $key='$val'",			$dbg, 'lime');###
										$fld_typ='Time';
										$val=filter_time($val);
										break;
					
					case 'L': msg("List $key='$val'",			$dbg, 'lime');###
										$fld_typ='List';
										$val=get_int_val($val);
										break;
					
					case 'B': msg("Binary $key='$val'",		$dbg, 'lime');###
										$fld_typ='Binary';
										$val=get_int_val($val);
										break;
					
					default:	msg("$module: '$val_type' for '$field_caption' not found", $dbg); ###
				}#end switch
				*/
				
				msg("$fld_typ $key='$val'",		$dbg, 'lime');###
				
				$ins_fields.=$comma.$field_name;
				$bind[]=array(":$field_name", $val);
				
				$comma=','; #Теперь будем добавлять запятую спереди
			}#end if
			
		}#end foreach
		if ($dbg) { show_var($ins_fields, 'ins_fields'); } ###
		if ($dbg) { show_var($bind, 'bind'); } ###
		
		if (!empty($ins_fields) ) {
			$STH_ins=insert($table, $ins_fields, $bind); #Попробуем добавить новую строку
			if ( empty($STH_ins) ) {
				$mess="$module: Не удалось добавить строку";
				alert($mess);
				msg($mess, $dbg);
			} else {
				msg("Line inserted", $dbg, 'lime');
			} ###
		}#end if
		
		
	}#end if
	
	
	
	
	
	
	
	
	
	
	
	
	
	##############################################################################
	#if ($dbg) {htmlhead();}### Для тестирования, после встраивания удалить !!!! ОТЛАДКА
	##############################################################################
	
	msg("START", $dbg);###
	
	
	
	
	
	
	####################################################################
	###                      НУМЕРАЦИЯ СТРАНИЦ
	####################################################################
	
	if ((!isset($_REQUEST[$pagenum_param])) || (!is_numeric($_REQUEST[$pagenum_param])) || ($_REQUEST[$pagenum_param] < 1)) {
		$pagenum = 1;
	} else {
		$pagenum = $_REQUEST[$pagenum_param];
	}
	
	msg("pagenum=$pagenum", $dbg); ###
	
	$qry_cou="SELECT COUNT(id) FROM [$mssql_base].[dbo].[$table]";
	$STH_cou=query($qry_cou);
	
	if ($row=$STH_cou->fetch() ) {
		$num_rows=$row[0];
		msg("num_rows=$num_rows", $dbg); ###
	} else {
		msg("Таблица '$table_title' пустая", $dbg);
	}
	msg("rows_per_page=$rows_per_page", $dbg); ###
	
	$pages=ceil($num_rows/$rows_per_page);
	msg("pages=$pages", $dbg); ###
	
	$begin_row=1+($pagenum-1)*$rows_per_page;
	msg("begin_row=$begin_row", $dbg); ###
	
	$end_row=$begin_row+$rows_per_page-1;
	msg("end_row=$end_row", $dbg); ###
	
	
	$prev_page=$pagenum-1;
	msg("prev_page=$prev_page", $dbg); ###
	
	$next_page=$pagenum+1;
	msg("next_page=$next_page", $dbg); ###
	
	
	#$btn_tpl="<input type='button' class='button' value='[value]'[event]>";
	$btn_tpl="<button[event]>[value]</button>";
	
	$onClick=" disabled";
	if ( 1!=$pagenum ) { $onClick=" onClick='pg(\"edit_table.php\", \"main\", \"tbl=$tbl&$pagenum_param=1\");'"; }
	$mes = str_replace('[value]',		'&lt;&lt; Начало',	$btn_tpl);
	$mes = str_replace('[event]',		$onClick,						$mes);
	echo $mes;
	
	$onClick=" disabled";
	if ( $prev_page>0 ) { $onClick=" onClick='pg(\"edit_table.php\", \"main\", \"tbl=$tbl&$pagenum_param=$prev_page\");'"; }
	echo "<button$onClick>&lt; Назад</button>";
	
	$onChange=" onChange='pg(\"edit_table.php\", \"main\", \"tbl=$tbl&$pagenum_param=\"+encodeURI(this.value));'";
	echo " Страница <input type=text value='$pagenum' size='3' style='text-align:center;'$onChange> из $pages&nbsp;";
	
	$onClick=" disabled";
	if ( $next_page<=$pages ) { $onClick=" onClick='pg(\"edit_table.php\", \"main\", \"tbl=$tbl&$pagenum_param=$next_page\");'"; }
	echo "<button$onClick>Вперёд &gt;</button>";
	
	$onClick=" disabled";
	if ( $pages!=$pagenum ) { $onClick=" onClick='pg(\"edit_table.php\", \"main\", \"tbl=$tbl&$pagenum_param=$pages\");'"; }
	echo "<button$onClick>Конец &gt;&gt;</button>&nbsp;";
	
	
	
	
	
	
	
	
	
	##############################################################################
	######                       ФОРМА   ПОИСКА
	##############################################################################
	# Тут проверим есть ли условия для фильтра
	$search_value=''; $where_cond=''; # Условие поиска
	if ( isset($_REQUEST['sf']) ) { # Если есть поле для фильтра
		if ($dbg) { show_var($_REQUEST, '_REQUEST'); } ###
		$tiny_field_name=substr($_REQUEST['sf'], 0, 2);
		if ( array_key_exists($tiny_field_name, $fields) ) {
			$field_name=$fields[$tiny_field_name][3];
			$where_cond="where upper($field_name) LIKE ";
			msg("$module: Found '$tiny_field_name' as '$field_name'", $dbg, 'lime'); ###
		}
		
		if ( isset($_REQUEST['sq']) ) { # Если есть запрос для фильтра
			$search_value=$_REQUEST['sq'];
			$search_query=mb_strtoupper("%$search_value%", 'UTF-8');
			msg("tiny_field_name=$tiny_field_name search_query=$search_query", $dbg); ###
			if ( !empty($where_cond) ) { $where_cond.="'$search_query'"; }
			msg("where_cond='$where_cond'", $dbg, 'blue'); ###
		}
	}
	
	echo "Фильтр по полю:&nbsp;";
	echo "<select id=\"srch_sf\" size=1>";
	echo "<option value=\"0\" disabled style='color:red;'>[Выберите]</option>";
	foreach ( $fields as $key=>$val ) {
		$selected='';
		$opt_name=$val[0]; if ($dbg) { $opt_name="$key ".$val[0]; }
		if ( $tiny_field_name==$key ) { $selected=' selected'; }
		echo "<option value=\"$key\"$selected>$opt_name</option>";
	}
	echo '</select>';
	
	echo "<input type='text' id='srch_sq' value='$search_value'>";
	
	$sf_val="sf=document.getElementById(\"srch_sf\").value;\r\n";
	$sq_val="sq=document.getElementById(\"srch_sq\").value;\r\n";
	$onClick="$sf_val $sq_val pg(\"$script_name\", \"main\", \"tbl=$tbl&$pagenum_param=$pagenum&sf=\"+sf+\"&sq=\"+sq);";
	
	echo "<input type=button name='send' value='Поиск' onClick='$onClick'>";
	
	
	
	
	
	
	
	
	
	echo $tbl_hdr; #Выводим сформированную шапку таблицы
	
	########################################
	#   Выводим строки таблицы с данными   #
	########################################
	/*
	Нужно придумать как посчитать количество строк, которые вернул запрос с условием
	
	SELECT @@ROWCOUNT FROM [coworking].[dbo].[tar_plans]
	WHERE id IN (1,2)
	
	*/
	
	# Делаем хитрый запрос с номерами строк rn, из которого выбираем нужный промежуток
	$qry="select  *
				from    (
									select  row_number() over (order by $order) rn
													,       *
									from    [$mssql_base].[dbo].[$table]
									$where_cond
								) as SubQueryAlias
				where   rn between $begin_row and $end_row";
	
	msg("qry='$qry'", $dbg); ###
	
	$STH=query($qry);
	while ($row = $STH->fetch()) {
		$script=''; $color='';
		
		# Узнаём правильное название первого столбца таблицы - ID, id, Id или iD
		reset($arr_fields); # На всякий случай сбросим указатель массива на начало 
		$id_col=key($arr_fields); # Получим название ключа первого столбца таблицы
		$ID=$row[$id_col]; # Получим IDшник этой строки
		#if ($dbg) { show_var($id_col, 'id_col'); } ###
		
		if ( array_key_exists('color', $row) ) { $color=" bgcolor='#".$row['color']."'"; }
		echo "<TR$color>";
		
		foreach($fields as $key=>$value) {
			$edit_level=$value[1];
			if ('0'==$edit_level) { continue; } # Если поле не надо показывать, продолжаем   # Маленькая оптимизация
			
			$str=''; #$field_caption=$value[0];
			$val_type=$value[2];		# Тип поля
			$field_name=$value[3];	# Полное название поля таблицы
			$val=$row[$field_name];	# Значение поля из таблицы
			
			$DATA_TYPE=''; if ( array_key_exists($field_name, $arr_fields) ) { $DATA_TYPE=$arr_fields[$field_name]['DATA_TYPE']; }
			
			if ('L'==$val_type) {
				$list=$arr_tpl_lookup_listbox[$field_name];				# HTML шаблон списка выбора поля $field_name
				$arr_list_fields=$arr_lookup_fields[$field_name]; # Массив полей списков выбора и их значений вида $arr[$field_name]=array('$ID'=>$name, ...)
			}

			$str=$val;
			switch ($edit_level) { 
				case '1':	if ('L'==$val_type) {#Для списка подставим вместо IDшника значение из LookUp поля другой таблички
										if (array_key_exists($val, $arr_list_fields) ) {
											$str=''; if ($dbg) {	$str=$val.':'; }
											$str.=trim($arr_list_fields[$val]);
										}
									}
									
									if ('D'==$val_type ) {# Если поле Дата
										if ( 'int'==$DATA_TYPE) { # Если поле UnixTime
											$str=date('Y-m-d', $val);
										} else { # Преобразовываем из строки
											$str=date('Y-m-d', strtotime($val));
										}
									}
									
									if ('R'==$val_type ) {# Если поле ДатаВремя
										if ( 'int'==$DATA_TYPE) { # Если поле UnixTime
											$str=date('Y-m-d H:i:s', $val);
										} else { # Преобразовываем из строки
											$str=date('Y-m-d H:i:s', strtotime($val));
										}
									}
									
									if ('T'==$val_type ) {# Если поле Время
										if ( 'int'==$DATA_TYPE) { # Если поле UnixTime
											$str=date('H:i:s', $val);
										} else { # Преобразовываем из строки
											$str=date('H:i:s', strtotime($val));
										}
									}
									
									if ('B'==$val_type ) {# Если Двоичное поле
										$chkd='';
										if (!empty($val) ) { $chkd=" checked='checked'"; }#end if
										$str="<input type='checkbox' $chkd disabled='disabled'>";
									}#end if
									
									if ( empty($str) ) { $str='&nbsp;'; }
									echo "<TD>$str</TD>";
									break;
									
				case '2':	$script=" onchange='pg(\"edit_table.php\", \"$key$ID\", \"tbl=$tbl&set=$key&line=$ID&val=\"+encodeURI(this.value));'";
									
									if ( 'C'===$val_type ) {# Если Цвет
										$str = str_replace('[value]',			'#'.$val, 		$tpl_clr_field);			#Меняем шаблон цвета
										$str = str_replace('[tiny_name]',	$key,					$str); 		# Сокращённое название поля - чтобы враг не узнал
										$str = str_replace('[line_id]',		$ID, 					$str);
										$str = str_replace('[script]',		$script,			$str);
										$str = str_replace('[size]',			'',						$str); # strlen($val),
									}
									
									if ( 'D'===$val_type ) {# Если Дата
										$val=date('Y-m-d', strtotime($val));
										$str = str_replace('[value]',			$val, 				$tpl_date_field);			#Меняем шаблон даты
										$str = str_replace('[tiny_name]',	$key,					$str); 		# Сокращённое название поля - чтобы враг не узнал
										$str = str_replace('[line_id]',		$ID, 					$str);
										$str = str_replace('[script]',		$script,			$str);
										$str = str_replace('[size]',			'',						$str); # strlen($val),
									}
									
									if ( 'R'===$val_type ) { # Если ДатаВремя
										if ( 'int'===$DATA_TYPE) { # Если UnixTime
											$val=date('Y-m-d H:i:s', $val);
										} else {
											#$val=substr($val, 0, 20);
											$date = new DateTime($val);
											$val=$date->format('Y-m-d H:i:s');
											$val=date('Y-m-d H:i:s', strtotime($val));
										}
										$str = str_replace('[value]',			$val, 				$tpl_datetime_field);			#Меняем  шаблон даты-времени
										$str = str_replace('[tiny_name]',	$key,					$str); 		# Сокращённое название поля - чтобы враг не узнал
										$str = str_replace('[line_id]',		$ID, 					$str);
										$str = str_replace('[script]',		$script,			$str);
										$str = str_replace('[size]',			'',						$str); # strlen($val),
									}
									
									if ( 'T'===$val_type ) {# Если Время
										$val=date('H:i:s', strtotime($val));
										$str = str_replace('[value]',			$val, 				$tpl_time_field);			#Меняем  шаблон времени
										$str = str_replace('[tiny_name]',	$key,					$str); 		# Сокращённое название поля - чтобы враг не узнал
										$str = str_replace('[line_id]',		$ID, 					$str);
										$str = str_replace('[script]',		$script,			$str);
										$str = str_replace('[size]',			'',						$str); # strlen($val),
									}
									
									if ( 'I'===$val_type ) {# Если Целое
										$str = str_replace('[value]',			$val, 				$tpl_num_field);			#Меняем шаблон целого
										$str = str_replace('[tiny_name]',	$key,					$str); 		# Сокращённое название поля - чтобы враг не узнал
										$str = str_replace('[line_id]',		$ID, 					$str);
										$str = str_replace('[script]',		$script,			$str);
										$str = str_replace('[size]',			'',						$str); # strlen($val),
									}
									
									if ( 'S'===$val_type ) {# Если Строка
										$str = str_replace('[value]',			remove_slashes($val),	$tpl_txt_field);			#Меняем текстовый шаблон
										$str = str_replace('[tiny_name]',	$key,									$str); 		# Сокращённое название поля - чтобы враг не узнал
										$str = str_replace('[line_id]',		$ID, 									$str);
										$str = str_replace('[script]',		$script,							$str);
										$str = str_replace('[size]',			'',										$str); # strlen($val),
									}
									
									if ('L'==$val_type) { # Если Список выбора
										$str=str_replace(' selected', '', $list); # Снимаем выбор со строки по умолчанию
										$str=str_replace("value=\"$val\"", "value=\"$val\" selected", $list); # Делаем выбранным определенный пункт списка
										$str=str_replace('size=1', "size=1 $script", $str); # Прицепляем к списку выбора скрипт реакции на событие
										$str=str_replace(' id="[tiny_name]"', '', $str); 		# IDшник тут не нужен - всё равно передаём this.value
									}
									
									if ('B'==$val_type ) {# Если Двоичное поле
										$chkd=''; $set_val='1';
										if (!empty($val) ) { $chkd=" checked='checked'"; $set_val='0'; }#end if
										$chkd_scrpt=" onclick='pg(\"edit_table.php\", \"$key$ID\", \"tbl=$tbl&set=$key&line=$ID&val=$set_val\");'";
										$str="<input type='checkbox' $chkd $chkd_scrpt>";
									}#end if
									
									echo "<TD id='$key$ID'>$str</TD>";
									break;
			}#end switch
			
		}#end foreach
		
		$td_txt='&nbsp;';
		if ( $allow_delete_line ) {
			$del_sign="<img src='./img/cross.png' width=16px style='height:16px;' border=0 title='Удалить' alt='Удалить'>"; #Значок удаления
			
			$del_cmd=" onClick='if (confirm(\"Удалить строку $ID ?\") ) {pg(\"$script_name\", \"main\", \"tbl=$tbl&del=$ID\");}; return false;'";
			#$del_cmd="alert(\"Триггер на удаление в процессе разработки\");";
			$link="$script_name?tbl=$tbl&del=$ID"; # Чтобы сработало даже при отключенном JavaScript'е
			$td_txt="<a href='$link'$del_cmd>$del_sign</a>";
		}
		
		if ( $allow_add_line OR $allow_delete_line ) {
			echo "<TD>$td_txt</TD>";
		}
		
		echo "</TR>\r\n";
	}#end while
	echo '</TABLE>';
	
	
	
	msg("END", $dbg);###
	
	
	##############################################################################
	#if ($dbg) {htmlfoot();} ### Для тестирования, после встраивания удалить !!!! ОТЛАДКА
	##############################################################################
	
	
	
	
?>
