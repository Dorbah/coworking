function getXmlHttp(){
	var xmlhttp;
	try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (E) {
			xmlhttp = false;
		}
	}
	if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		xmlhttp = new XMLHttpRequest();
	}
	return xmlhttp;
}

function pg(res, dist, pars){
	var xmlhttp = getXmlHttp();
	var scripts = [];
	var regular = /<script.*?>([\s\S]*?)<\/script>/gim;
	
	if(dist != ""){
		dist = document.getElementById(dist);
		dist.innerHTML = '<img src="img/wait.gif">';
	}
	
	xmlhttp.open("POST", res, true);
	
	xmlhttp.onreadystatechange = function(){
	
		if(xmlhttp.readyState == 4){
			if(xmlhttp.status == 200){
				dist.innerHTML = xmlhttp.responseText;
				while ((scripts = regular.exec(xmlhttp.responseText)) != null){
					eval(scripts[1]);
				}	
			} else {
				dist.innerHTML = xmlhttp.responseText;
			}
		}
	};
	
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
	xmlhttp.send(pars);
}


function load(res, dist, pars){
	var xmlhttp = getXmlHttp();
	var xdist = '';
	var tryr = 0;
	
	if (dist != '') xdist = document.getElementById(dist);
	
	xmlhttp.onreadystatechange = function () {
		if((xmlhttp.readyState==4)&&(xmlhttp.status==200)) {
		
			var a = xmlhttp.responseText;
			var s = '';
			while ((b=a.indexOf('<s\cript>'))!=-1) {
				c = a.indexOf('</s\cript>');
				s = s + a.substring(b + 8, c);
				a = a.substring(0, b) + a.substring(c + 9, a.length);
			}
			xdist.innerHTML = a;
			eval(s);
			
		}
		else
			if ((xmlhttp.readyState==4)&&(xmlhttp.status!=200)) {
				tryr++;
				if (tryr<10) {
					pg(res, dist, pars);
					xdist.innerHTML = xmlhttp.responseText + "Error with server or link! Try " + tryr + " to request...";
				}
					xdist.innerHTML = xmlhttp.responseText + "Error with server or link! Trying stopped.";
			}
	}
	xmlhttp.open("POST", res, true);
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
	xmlhttp.send(encodeURI(pars));
	
}



// Функция собирает данные с инпутов в форме по formId
function send_regs(res, dist, formId){
	var pr = '';
	var arr = document.getElementById(formId).getElementsByTagName('input');
	pr=arr[0].id+"="+encodeURIComponent(arr[0].checked);//Добавляем нулевой элемент БЕЗ начального амперсанда '&'
	len=arr.length;
	for (var i = 1; i < len; i++){// А тут перебираем с первого элемента
		if(arr[i].id != '') {
			pr += '&'+arr[i].id+"="+encodeURIComponent(arr[i].checked); // For CheckBox value IS read from CHECKED property !!!!
		}
	}
	//alert('pr:'+pr);
	pg(res, dist, pr);
}


// Функция 
function highlight(item_name, formId){
	//Сначала сбросим цвет всех элементов
	var arr = document.getElementById(formId).getElementsByTagName('a');
	var len = arr.length;
	for (var i = 0; i < len; i++){// Перебираем все ссылки
		arr[i].style.color='blue';
	}
	
	//Теперь установим цвет нужного пункта
	item = document.getElementById(item_name);
	item.style.color='red';
}





















