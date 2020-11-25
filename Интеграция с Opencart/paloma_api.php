<?php
include('config.php'); 
//-----Необходимо установить токен
$OpenCart_token = '';
//----Проверка токена
if (!isset($_REQUEST['token'])) {
    echo 'Ошибка, нет данных для обработки';
    die;
}
else{

	$mas = json_decode($_POST['json'],true);
	$token=$_REQUEST['token'];
 $action=$_REQUEST['action'];
}
if ($token<>$OpenCart_token){
	echo 'Ошибка, Токен не совпадает';
    die;
}
//echo 'Пятое значение массива: '. print_r($mas[5]).'<br>';
// die;
//Подключение к бд
$db = mysql_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD);
mysql_select_db(DB_DATABASE, $db);
mysql_query("set names 'utf8'");

if ($db) {
  //  echo 'Соединение установленно успешно<br>';
} else {
    echo 'Ошибка соединение не установленно<br>'; 
    die;
}
if ($action=='orders'){
  //echo 'Вставка заказов<br>';
  echo GetOrders($db,$OpenCart_token);
  die;
 }else
   if($action!='items'){
    echo 'Неизвестное событие<br>';
    die;
   }
   
 function GetOrders($db1,$OpenCart_token1){
    $from=$_REQUEST['dtfrom'];
    if (!isset($_REQUEST['dtfrom']) || empty($_REQUEST['dtfrom'])){
       $from = "";
     } else{
       $from = " AND DATE(o.date_modified) >= DATE(DATE_FORMAT('".$_REQUEST['dtfrom']."','%Y-%m-%d %H:%i:%S')) ";
     }
    // Проставляем статусы заказов
    // Поиск удаленных заказов
     $sql = "update paloma_orders po 
             set po.action='delete', 
               po.import_date=now() 
             where po.order_id not in 
              (select oo.order_id from oc_order oo)";
     mysql_query($sql, $db1);
    //Поиск измененных заказов
     $sql = "update paloma_orders po 
             set po.action='update', 
               po.import_date=now() 
             where po.order_id in 
              (select oo.order_id 
              from oc_order oo 
              where oo.date_modified>=po.import_date)
             and po.action<>'delete' ";
     mysql_query($sql, $db1);
    //Вставка новых заказов
     $sql = "insert paloma_orders (order_id,import_date,action)
             (select order_id,now(),'insert'
             from oc_order oo
             where oo.order_id not in (select po.order_id from paloma_orders po))";
     mysql_query($sql, $db1); 
    //Получение списка заказов
     $sql = "SELECT 
             o.order_id
             ,o.date_modified
             ,o.date_added
             ,o.invoice_prefix
             ,o.shipping_address_1
             ,o.total
             ,o.shipping_firstname
             ,o.shipping_lastname
             ,o.email
             ,o.telephone
             ,op.name
             ,op.model
             ,op.quantity
             ,op.price
             ,op.total as `summ`
             ,pa.id_paloma
             ,po.action
             FROM 	oc_order o
             JOIN 	oc_order_product op on op.order_id = o.order_id
             JOIN 	paloma_api pa on pa.id_opencard = op.product_id
             JOIN  paloma_orders po on po.order_id = o.order_id 
             WHERE	o.order_status_id = 1 and o.date_modified>=(select `value` from paloma_config where `key`='last_orders_export') and pa.isgroup = 0 
             ORDER BY 1";
     $table = mysql_query($sql, $db1);
    // $rez = mysql_fetch_array($table);
     if (!$table){
      echo 'Заказы не найдены<br>';
      die;
     } else {
     $sql = "update paloma_config set `value`=now() where `key`='last_orders_export'";
     mysql_query($sql, $db1);
      //Передаем заказы на сервер paloma365
      $orders = array();
      while ($row = mysql_fetch_array($table) ) $orders[] = $row;
      
      return json_encode($orders);}
 }

//Проверка существования таблицы paloma_orders и ее создание
$table_create = mysql_query("SELECT * FROM paloma_orders", $db);
if ($table_create) {
    //Таблица paloma_api существует
    $table_orders = mysql_fetch_array($table_create);
} else {
    $sql = "CREATE TABLE `paloma_orders` (
            `id`  int NOT NULL AUTO_INCREMENT ,
            `order_id`  int NOT NULL ,
            `import_date`  datetime NOT NULL ,
            `action`  varchar(10) NOT NULL ,
            PRIMARY KEY (`id`)
            )";
    if (mysql_query($sql)) {
        echo 'Создана таблица paloma_orders<br>';
    } else {
        echo 'Ошибка таблица paloma_orders не создана<br>';
        die;
    }
} 
 
//Проверка существования таблицы paloma_api и ее создание
$table_create = mysql_query("SELECT * FROM paloma_api", $db);
if ($table_create) {
    //Таблица paloma_api существует
    $table_paloma = mysql_fetch_array($table_create);
} else {
    $sql = "CREATE TABLE `paloma_api` (`id` INT NOT NULL AUTO_INCREMENT,`id_paloma` INT NOT NULL , `id_opencard` INT NOT NULL, `isgroup` INT NOT NULL, `parentid` INT NOT NULL, PRIMARY KEY (  `id` ))";
    if (mysql_query($sql)) {
        echo 'Создана таблица paloma_api<br>';
    } else {
        echo 'Ошибка таблица paloma_api не создана<br>';
        die;
    }
}

//Проверка существования таблицы paloma_config_api и проверка токена
$table_create = mysql_query("SELECT * FROM paloma_config WHERE `key`='token'", $db);
if ($table_create) {
    //Таблица paloma_api_config существует
    $table_paloma_config = mysql_fetch_array($table_create);
    if ($table_paloma_config['value']!=$token){
        echo 'Ошибка, не верный токен';
        //print_r($token);
        die;
    }
    
} else {
    $sql = "CREATE TABLE `paloma_config` (`id` INT NOT NULL AUTO_INCREMENT,`key` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, `value` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, PRIMARY KEY (`id`))";
    if (mysql_query($sql)) {
        echo 'Создана таблица paloma_config<br>';
        mysql_query("INSERT INTO paloma_config (`key`,`value`) VALUES ('last_orders_export',now())");
        $r=mysql_query("INSERT INTO paloma_config (`key`,`value`) VALUES ('token','$token')");
    } else {
        echo 'Ошибка таблица paloma_config не создана<br>';
        die;
    }
}

//Функция добавление нового товара
function add_product($id_paloma,$name,$parentid,$price,$db,$img,$qty){
if ($img !=''){   
    $fn=explode("/",$img);
    $filename=$fn[count($fn)-1];
    
    $imag='catalog/'.$filename;
    
    $local='image/'.$imag;
    file_put_contents($local, file_get_contents($img));       
}
else{
    $imag="";
}

    
$d = Date('Y-m-d');
//Добавление в таблицу oc_product
            $r = mysql_query("INSERT INTO oc_product (model,price,date_added,status,image,quantity) VALUES ('$name','$price','$d','1','$imag','$qty')");
            $idd = mysql_insert_id();
             
            if (!$r){
                echo 'error oc_product<br>';
            }
            
//Добавление в таблицу paloma_api    
            $r = mysql_query("INSERT INTO paloma_api (id_paloma,id_opencard,isgroup,parentid) VALUES ('$id_paloma','$idd','0','$parentid')");
            
            if (!$r){
                echo 'error paloma_api<br>';
            }
            
            
//Добавление в таблицу oc_product_to_store
            $r = mysql_query("INSERT INTO oc_product_to_store (product_id,store_id) VALUES ('$idd','0')");

            if (!$r){
                echo 'error oc_product_to_store<br>';
            }
            
//Добавление в таблицу oc_product_to_layout  
            $r = mysql_query("INSERT INTO oc_product_to_layout (product_id,store_id,layout_id) VALUES ('$idd','0','0')");

            if (!$r){
                echo 'error oc_product_to_layout<br>';
            }
            
//Добавление в таблицу oc_product_description
            //$meta_title = 'Мета описание';
            //$description = 'Описание';
            $meta_title=$name;
            $description=$name;
            $r = mysql_query("INSERT INTO oc_product_description (product_id,language_id,name,description,meta_title) VALUES ('$idd','1','$name','$description','$meta_title')");
            
            if (!$r){
                echo 'error oc_product_description<br>';
            }
            
//Добавление в таблицу oc_product_to_category
            $result = mysql_query("SELECT * FROM paloma_api WHERE id_paloma='$parentid'", $db);
            $categ = mysql_fetch_array($result);
            
            $cat=$categ['id_opencard'];

            $r = mysql_query("INSERT INTO oc_product_to_category (product_id,category_id) VALUES ('$idd','$cat')");
            
            if (!$r){
                echo 'error oc_product_to_category<br>';
            }
    return 'Создан товар '.$name.'<br>';
}

//Функция обновления товара
function update_product($id_paloma,$name,$parentid,$price,$db,$img,$qty){
if ($img !=''){   
    $fn=explode("/",$img);
    $filename=$fn[count($fn)-1];
    
    $imag='catalog/'.$filename;
    
    $local='image/'.$imag;
    file_put_contents($local, file_get_contents($img));       
}
else{
    $imag="";
}

 $d = Date('Y-m-d');
 
 mysql_query("UPDATE paloma_api SET parentid='$parentid' WHERE id_paloma='$id_paloma'");
 
 //Новая категория товара
 $result = mysql_query("SELECT * FROM paloma_api WHERE id_paloma='$parentid'", $db);
 $categ_paloma = mysql_fetch_array($result);
 $new_categ_opencard=$categ_paloma['id_opencard'];
 
 //ID продукта в opencard
 $result = mysql_query("SELECT * FROM paloma_api WHERE id_paloma='$id_paloma'", $db);
 $categ_paloma = mysql_fetch_array($result);
 $id_product_opencard=$categ_paloma['id_opencard'];
    
 //Обновление категорий в таблице oc_product_to_category
 mysql_query("UPDATE oc_product_to_category SET category_id='$new_categ_opencard' WHERE product_id='$id_product_opencard'");
 
 //Обновление в таблице oc_product
 mysql_query("UPDATE oc_product SET model='$name', price='$price', date_modified='$d', image='$imag', quantity='$qty' WHERE product_id='$id_product_opencard'");
 
 //Обновление в таблице oc_product_description
 mysql_query("UPDATE oc_product_description SET name='$name', description='$name', meta_title='$name' WHERE product_id='$id_product_opencard'");

 return 'Обновлен товар '.$name.'<br>';
}


//Функция добавление новой категорий
function add_category($id_paloma,$name,$parentid,$db){

$parent_opencard=0;    
if ($parentid != 0){
    $result = mysql_query("SELECT * FROM paloma_api WHERE id_paloma='$parentid'", $db);
    $categ = mysql_fetch_array($result);
    $parent_opencard=$categ['id_opencard'];
} 

$d = Date('Y-m-d');
//Добавление в таблицу oc_category
                mysql_query("INSERT INTO oc_category (parent_id,status,date_added,`column`,top) VALUES ('$parent_opencard','1','$d','1','1')");
$id_categ_opencard = mysql_insert_id();       

//Добавление в таблицу paloma_api    
                mysql_query("INSERT INTO paloma_api (id_paloma,id_opencard,isgroup,parentid) VALUES ('$id_paloma','$id_categ_opencard','1','$parentid')");  

//Добавление в таблицу oc_category_to_store
                mysql_query("INSERT INTO oc_category_to_store (category_id) VALUES ('$id_categ_opencard')");

//Добавление в таблицу oc_category_path
                mysql_query("INSERT INTO oc_category_path (category_id,path_id) VALUES ('$id_categ_opencard','$id_categ_opencard')");
                if ($parent_opencard != 0){
                    mysql_query("INSERT INTO oc_category_path (category_id,path_id) VALUES ('$id_categ_opencard','$parent_opencard')");
                }

//Добавление в таблицу oc_category_to_layout
                mysql_query("INSERT INTO oc_category_to_layout (category_id) VALUES ('$id_categ_opencard')");

//Добавление в таблицу oc_category_description
                mysql_query("INSERT INTO oc_category_description (category_id,language_id,name,meta_title,meta_description) VALUES ('$id_categ_opencard','1','$name','$name','$name')"); 
    return 'Создана категория '.$name.'<br>';
}

//Функция обновления категорий
function update_category($id_paloma,$name,$parentid,$db){
    $d = Date('Y-m-d');
    
    mysql_query("UPDATE paloma_api SET parentid='$parentid' WHERE id_paloma='$id_paloma'");
    
    $result = mysql_query("SELECT * FROM paloma_api WHERE id_paloma='$parentid'", $db);
    $categ_paloma = mysql_fetch_array($result);
    $new_categ_opencard=$categ_paloma['id_opencard'];
    
    $result = mysql_query("SELECT * FROM paloma_api WHERE id_paloma='$id_paloma'", $db);
    $categ_paloma = mysql_fetch_array($result);
    
    $id_categ_opencard=$categ_paloma['id_opencard'];
    
    //Обновление таблицы oc_category
    mysql_query("UPDATE oc_category SET parent_id='$new_categ_opencard',date_modified='$d' WHERE category_id='$id_categ_opencard'");
    //Обновление таблицы oc_category_description
    mysql_query("UPDATE oc_category_description SET name='$name', meta_title='$name', meta_keyword='$name' WHERE category_id='$id_categ_opencard'");
    return 'Обновлена категория '.$name.'<br>';
}

//Очистка таблицы paloma_api от удаленных категорий
$res_categ = mysql_query("SELECT GROUP_CONCAT(id_opencard) as mascat_opencard FROM paloma_api WHERE isgroup='1'", $db);
$gr_cat = mysql_fetch_array($res_categ);

$mas_opencard_categ = explode(",", $gr_cat['mascat_opencard']);

$res_categ = mysql_query("SELECT GROUP_CONCAT(category_id) as opencard FROM oc_category", $db);
$gr_cat = mysql_fetch_array($res_categ);

$cat_opencard = explode(",", $gr_cat['opencard']);

for ($i = 0; $i <= count($mas_opencard_categ) - 1; $i++) {
    if (!in_array($mas_opencard_categ[$i], $cat_opencard)) {
        mysql_query("DELETE FROM paloma_api WHERE id_opencard='$mas_opencard_categ[$i]'");
    }
}

//Синхронизация категорий
$mas_paloma_categ = array();
$res_categ = mysql_query("SELECT id_paloma FROM paloma_api WHERE isgroup='1'", $db);
while ($row = mysql_fetch_array($res_categ) ) $mas_paloma_categ[] = $row['id_paloma'];
for ($i = 0; $i <= count($mas) - 1; $i++) {
    $isgroup = $mas[$i]['isgroup'];
    if ($isgroup != 0){
		//echo "<br>";
       $id_paloma=$mas[$i]['id'];
	   //echo $id_paloma."<br>";
       $parentid = $mas[$i]['parentid'];
       $name = $mas[$i]['name'];
       if (in_array($id_paloma, $mas_paloma_categ)) {
                //Обновление категорий
                echo update_category($id_paloma,$name,$parentid,$db);
            }
            else
            {
                //Добавление новой категорий
                echo add_category($id_paloma,$name,$parentid,$db);
            }   
    }
}

//Очистка таблицы paloma_api от удаленных товаров
$res_categ = mysql_query("SELECT GROUP_CONCAT(id_opencard) as mascat_opencard FROM paloma_api WHERE isgroup='0'", $db);
$gr_cat = mysql_fetch_array($res_categ);

$mas_opencard_product = explode(",", $gr_cat['mascat_opencard']);

$res_categ = mysql_query("SELECT GROUP_CONCAT(product_id) as opencard FROM oc_product", $db);
$gr_cat = mysql_fetch_array($res_categ);

$cat_opencard = explode(",", $gr_cat['opencard']);
                            
for ($i = 0; $i <= count($mas_opencard_product) - 1; $i++) {                             
    if (!in_array($mas_opencard_product[$i], $cat_opencard)) {           
        mysql_query("DELETE FROM paloma_api WHERE id_opencard='$mas_opencard_product[$i]'");
    }
}


//Синхронизация товаров
$mas_paloma_product = array();
$res = mysql_query("SELECT id_paloma as product FROM paloma_api WHERE isgroup='0'", $db);
while ($row = mysql_fetch_array($res) ) $mas_paloma_product[] =$row['product'];
for ($i = 0; $i <= count($mas) - 1; $i++) {	
    $isgroup = $mas[$i]['isgroup'];
    if ($isgroup == 0){
        $name=$mas[$i]['name'];
        $id_paloma = $mas[$i]['id'];
        $d = Date('Y-m-d');
        
        $parentid = $mas[$i]['parentid'];
        $price = $mas[$i]['price'];
        $img = $mas[$i]['img'];
        $qty = $mas[$i]['remains'];
        if (in_array($id_paloma, $mas_paloma_product)) {
            echo update_product($id_paloma,$name,$parentid,$price,$db,$img,$qty);
        }
        else
        {
            echo add_product($id_paloma,$name,$parentid,$price,$db,$img,$qty);
        }
    }
}
?>
