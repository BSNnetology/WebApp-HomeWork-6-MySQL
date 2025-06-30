<!doctype html>
<html lang="ru">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" 
	integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"
	rel="stylesheet" crossorigin="anonymous">

	<title>Оформление заказа</title>

	<style>		 
		body {padding: 5px;}
		h2 { font-size: 19px; }
		legend { font-size: 17px; }
		.mb-3, .form-control { font-size: 14px; }
	</style>
</head>

<body>
	<div class="container">
		<h2>Оформление заказа</h2>
		<form action="" method="POST">
			<fieldset>
				<legend>Контактная информация</legend>
				<div class="mb-3">
					<label class="form-label">Ваше имя<span class="mf-req">*</span></label>
					<input type="text" name="user_name" id="user_name" class="form-control" value="">
				</div>
				<div class="mb-3">
					<label class="form-label">Ваше отчество<span class="mf-req">*</span></label>
					<input type="text" name="user_second_name" id="user_second_name" class="form-control" value=""><br>
				</div>
				<div class="mb-3">
					<label class="form-label">Ваша фамилия<span class="mf-req">*</span></label>
					<input type="text" name="user_last_name" id="user_last_name" class="form-control" value=""><br>
				</div>
			</fieldset>
			<fieldset>
				<legend>Адрес доставки</legend>
				<div class="mb-3">
					<label class="form-label">Город<span class="mf-req">*</span></label>
					<input type="text" name="user_address_city" id="user_address_city" class="form-control" value=""><br>
				</div>
				<div class="mb-3">
					<label class="form-label">Улица<span class="mf-req">*</span></label>
					<input type="text" name="user_address_street" id="user_address_street" class="form-control" value=""><br>
				</div>
				<div class="mb-3">
					<label class="form-label">Дом и корпус<span class="mf-req">*</span></label>
					<input type="text" name="user_address_house" id="user_address_house" class="form-control" value=""><br>
				</div>
				<div class="mb-3">
					<label class="form-label">Квартира<span class="mf-req">*</span></label>
					<input type="text" name="user_address_flat" id="user_address_flat" class="form-control" value="">
				</div>
			</fieldset>
			<button type="submit" name="submit" class="btn btn-primary" value="submit">Заказать</button>
		</form>
		<div id="result"></div>
	</div>

	<?php
		try { 	// Только так работают проверки запроса 	
			if($_REQUEST["submit"]){
				$result = null;				
				$resultText = "";

				// Проверим, все ли поля переданы, или вызовем исключение
				$test = array_filter($_REQUEST);
				if(count($test) !== count($_REQUEST)) {
					throw new Exception("Необхобимо заполнить все обязательные поля заказа...");
				}

				// Получим параметры подключаения к БД	
				mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL);
				$param = file_get_contents("../security_sql.txt");
			
				// Подключаемся к БД	 				
				$DB_HOST="localhost"; //:3306 // С предложенным портом - не соединяется!!!!
				$DB_NAME="dbbx-stud12";
				// $DB_PASS='';
				// $DB_NAME='';	

				$link = mysqli_connect( $DB_HOST
				, json_decode(base64_decode($param))->user
				, json_decode(base64_decode($param))->pass
				, $DB_NAME );

				// Если не подключились (проверка не работает!!!!)
				if (!$link) { 
					throw new Exception("Ошибка: Невозможно установить соединение с MySQL.<br>"
					. "Код ошибки errno: " . mysqli_connect_errno( ) . "<br>"
					. "Текст ошибки error: " . mysqli_connect_error( ) . "<br>");
				} 
				// Если таки подключились
				else {
					// Запись контакта
					$sql_contact = "INSERT INTO contacts (Name, SecondName, LastName)" 
					. "VALUES ('" . $_REQUEST['user_name'] 
					. "', '" . $_REQUEST['user_second_name']
					."', '" . $_REQUEST['user_last_name'] . "')";
					$result = mysqli_query($link, $sql_contact);
					$contact_id = mysqli_insert_id($link);

					// Запись заказа
					$sql_order = "INSERT INTO orders (ContactId, City, Street, House, Flat)" 
					. "VALUES ('" . $contact_id 
					. "', '" . $_REQUEST['user_address_city'] 
					. "', '" . $_REQUEST['user_address_street'] 
					. "', '" . $_REQUEST['user_address_house'] 
					. "', '"  . $_REQUEST['user_address_flat'] . "')";
					$result = $result && mysqli_query($link, $sql_order);
					$order_id = mysqli_insert_id($link); 

					// Подготовка строки ответа
					$resultText = "<br>Спасибо за ваш заказ, " . $_REQUEST['user_last_name']
					. " " . $_REQUEST['user_name']. " " . $_REQUEST['user_second_name'] . "!<br>"
					. "Ему присвоен номер: $order_id<br>"
					. "Заказ будет доставлен по адресу: " . $_REQUEST['user_address_city'] . ", "
					. $_REQUEST['user_address_street'] . ", д." . $_REQUEST['user_address_house'] 
					. ", кв." . $_REQUEST['user_address_flat'] . "<br>";	
				}	
			}
		}
		// Обработка исключения - подготовка строки ответа
		catch (exception $e) { 
			$result = null;
			$resultText = "<br>" . $e->getMessage();   
		}
		// Отображение строки ответа 
		finally {
			echo "<pre>";
			echo $resultText;

			// Отображение дополнительных данных
			if($result !== null) {
				echo "<br>Параметры запроса:<br>";
				print_r($_REQUEST);
				echo "<br>Параметры соединения:<br>";
				print_r($link);
			}

			echo "</pre>";
		}
	?>

	<script>
		// Блокировка повторной отправки данных формы
		if ( window.history.replaceState ) {
			window.history.replaceState( null, null, window.location.href );
		}
	</script>

</body>
</html>
