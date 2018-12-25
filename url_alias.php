<?php
error_reporting(-1);
header('Content-Type: text/html; charset=utf-8');

require_once('config.php');
require_once(DIR_SYSTEM . 'startup.php');

// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

function seoURL($args) {
          $force_keyword = false;

          $rules = array(
            '¹' => 1,
            '²' => 2,
            '³' => 3,

            'º' => 0,
            '°' => 0,
			'А' => 'a',
			'Б'	=> 'b',
			'В'	=> 'v',
			'Г'	=> 'g',
			'Д'	=> 'd',
			'Е'	=> 'e',
			'Ж'	=> 'zh',
			'З'	=> 'z',
			'И'	=> 'i',
			'Й'	=> 'y',
			'К'	=> 'k',
			'Л'	=> 'l',
			'М'	=> 'm',
			'Н'	=> 'n',
			'О'	=> 'o',
			'П'	=> 'p',
			'Р'	=> 'r',
			'С'	=> 's',
			'Т'	=> 't',
			'У'	=> 'u',
			'Ф'	=> 'f',
			'Х'	=> 'h',
			'Ц'	=> 'ts',
			'Ч'	=> 'ch',
			'Ш'	=> 'sh',
			'Щ'	=> 'sht',
			'Ъ'	=> 'a',
			'Ь'	=> 'y',
			'Ю'	=> 'yu',
			'Я'	=> 'ya',
			
			'а' => 'a',
			'б'	=> 'b',
			'в'	=> 'v',
			'г'	=> 'g',
			'д'	=> 'd',
			'е'	=> 'e',
			'ж'	=> 'zh',
			'з'	=> 'z',
			'и'	=> 'i',
			'й'	=> 'y',
			'к'	=> 'k',
			'л'	=> 'l',
			'м'	=> 'm',
			'н'	=> 'n',
			'о'	=> 'o',
			'п'	=> 'p',
			'р'	=> 'r',
			'с'	=> 's',
			'т'	=> 't',
			'у'	=> 'u',
			'ф'	=> 'f',
			'х'	=> 'h',
			'ц'	=> 'ts',
			'ч'	=> 'ch',
			'ш'	=> 'sh',
			'щ'	=> 'sht',
			'ъ'	=> 'a',
			'ь'	=> 'y',
			'ю'	=> 'yu',
			'я'	=> 'ya'


          );

            // Setting url string
            $str = $args;
            $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
            $str = strtr($str, $rules);
            $str = preg_replace('/([^a-zA-Z0-9]|-)+/', '-', $str);
            $str = trim($str, '-');
            $str = strtolower($str);

            // Avoid duplication
            global $db;
            if ($db) {
              $okay = false;
              $counter = 1;
              $modifier = '';
              do {
                if($counter > 1)
                  $modifier = '-' . $counter;
                $result = $db->query("SELECT COUNT(*) as `total` FROM " . DB_PREFIX . "url_alias WHERE keyword = '" . $db->escape($str . $modifier) . "'");
                if($result->row['total'] == 0) {
                  $str .= $modifier;
                  $okay = true;
                } else
                  $counter++;
              } while($okay == false);
            }

            return $str;
          }
?>
<html>
    <head>
	<title>Создание SEO-URL</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700&subset=latin,cyrillic' rel='stylesheet' type='text/css'>        
    </head>
    <style>
        body {
            background-color: #f1f4f5;
            color: #37474f;
            line-height: 1.4;
            font-family: 'Open Sans', sans-serif;
            margin: 70px;
            padding: 0;
            }
        .back_button {
            background-color: #399bff;
            color: #fff;
            margin-top: 15px;
            font-size: 14px;
            padding: 7px 20px 7px 20px;
            border: none;
            border-radius: 3px;
            vertical-align: middle;
            cursor: pointer;
			text-decoration: none;
            }
    </style>
    <body>
        <center>
        <h1>Script by WebMakers</h1>
		<p>https://webmakers.com.ua</a></p>
        <br>
        <h1>Modified by BigMaverick</h1>
        <p><a href="https://github.com/BigMaverick" target="_blank">https://github.com/BigMaverick</a></p>
		<br>
<?php
$force = isset($_GET['force']);

if(isset($_GET['products'])) {
$products   = $db->query("SELECT * FROM " . DB_PREFIX . "product");
$products   = $products->rows;

foreach($products as $product) {
    
    $url = $db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product['product_id'] . "'");
    $url = $url->rows;
    
    if(!empty($url) && !$force) {
        echo 'В товаре с id=' . $product['product_id'] . '. URL уже существует (перезапись не производится).<br><hr>';
    } else {
        echo 'Получение информации о товаре с id=' . $product['product_id'] . '...<br>';
        $info = $db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . $product['product_id'] . "' LIMIT 1");
        $info = $info->rows;
        
        foreach($info as $data) {        
            if(!empty($url))
                $db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE url_alias_id = " . $url[0]["url_alias_id"] . "");
            echo 'Товар: ' . $data['name'] . ' | URL: ' . seoURL($data['name']);
            $data['name'] = seoURL($data['name']);
            $db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product['product_id'] . "', keyword = '" . $db->escape($data['name']) . "'");
            echo '<br>URL сгенерирован!<br><hr>';
        }
    }
}

echo '<h2>Все сделано!</h3><a href="javascript:history.back(1)" class="back_button">Вернуться назад</a>';

}


elseif(isset($_GET['categories'])) {
$categories   = $db->query("SELECT * FROM " . DB_PREFIX . "category");
$categories   = $categories->rows;

foreach($categories as $category) {
    
    $url = $db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE query = 'category_id=" . (int)$category['category_id'] . "'");
    $url = $url->rows;
    
    if(!empty($url) && !$force) {
        echo 'В категории с id=' . $category['category_id'] . '. URL уже существует (перезапись не производится).<br><hr>';
    } else {
        echo 'Получение информации о категории с id=' . $category['category_id'] . '...<br>';
        $info = $db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . $category['category_id'] . "' LIMIT 1");
        $info = $info->rows;
        
        foreach($info as $data) {        
            if(!empty($url))
                $db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE url_alias_id = " . $url[0]["url_alias_id"] . "");
            echo 'Категория: ' . $data['name'] . ' | URL: ' . seoURL($data['name']);
            $data['name'] = seoURL($data['name']);
            $db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'category_id=" . (int)$category['category_id'] . "', keyword = '" . $db->escape($data['name']) . "'");
            echo '<br>URL сгенерирован!<br><hr>';
        }
    }
}

echo '<h2>Все сделано!</h3><a href="javascript:history.back(1)" class="back_button">Вернуться назад</a>';

}


elseif(isset($_GET['manufacturers'])) {
$manufacturers   = $db->query("SELECT * FROM " . DB_PREFIX . "manufacturer");
$manufacturers   = $manufacturers->rows;

foreach($manufacturers as $manufacturer) {
    
    $url = $db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer['manufacturer_id'] . "'");
    $url = $url->rows;
    
    if(!empty($url) && !$force) {
        echo 'В производителе с id=' . $manufacturer['manufacturer_id'] . '. URL уже существует (перезапись не производится).<br><hr>';
    } else {
        echo 'Получение информации о производителе с id=' . $manufacturer['manufacturer_id'] . '...<br>';
        $info = $db->query("SELECT * FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . $manufacturer['manufacturer_id'] . "' LIMIT 1");
        $info = $info->rows;
        
        foreach($info as $data) {        
            if(!empty($url))
                $db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE url_alias_id = " . $url[0]["url_alias_id"] . "");
            echo 'производитель: ' . $data['name'] . ' | URL: ' . seoURL($data['name']);
            $data['name'] = seoURL($data['name']);
            $db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'manufacturer_id=" . (int)$manufacturer['manufacturer_id'] . "', keyword = '" . $db->escape($data['name']) . "'");
            echo '<br>URL сгенерирован!<br><hr>';
        }
    }
}

echo '<h2>Все сделано!</h3><a href="javascript:history.back(1)" class="back_button">Вернуться назад</a>';

}

elseif(isset($_GET['information'])) {
$informationp    = $db->query("SELECT * FROM " . DB_PREFIX . "information");
$informationp    = $informationp->rows;

foreach($informationp as $information) {
    
    $url = $db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE query = 'information_id=" . (int)$information['information_id'] . "'");
    $url = $url->rows;
    
    if(!empty($url) && !$force) {
        echo 'В статье с id=' . $information['information_id'] . '. URL уже существует (перезапись не производится).<br><hr>';
    } else {
        echo 'Получение информации для статьи с id=' . $information['information_id'] . '...<br>';
        $info = $db->query("SELECT * FROM " . DB_PREFIX . "information_description WHERE information_id = '" . $information['information_id'] . "' LIMIT 1");
        $info = $info->rows;
        
        foreach($info as $data) {        
            if(!empty($url))
                $db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE url_alias_id = " . $url[0]["url_alias_id"] . "");
            echo 'Статья: ' . $data['title'] . ' | URL: ' . seoURL($data['title']);
            $data['title'] = seoURL($data['title']);
            $db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'information_id=" . (int)$information['information_id'] . "', keyword = '" . $db->escape($data['title']) . "'");
            echo '<br>URL сгенерирован!<br><hr>';
        }
    }
}

echo '<h2>Все сделано!</h3><a href="javascript:history.back(1)" class="back_button">Вернуться назад</a>';
    
}

else {
    echo '<p>Сгенерировать SEO-URL для <a href="?products">товаров</a>. ';
    echo 'Перегенерировать все SEO-URL для <a href="?products&force">товаров</a></p>';
    
    echo '<p>Сгенерировать SEO-URL для <a href="?categories">категорий</a>. ';
    echo' Перегенерировать все SEO-URL для <a href="?categories&force">категорий</a></p>';
    
    echo '<p>Сгенерировать SEO-URL для <a href="?manufacturers">производителей</a>. ';
    echo 'Перегенерировать все SEO-URL для <a href="?manufacturers&force">производителей</a></p>';
    
    echo '<p>Сгенерировать SEO-URL для <a href="?information">статей</a>. ';
    echo 'Перегенерировать все SEO-URL для <a href="?information&force">статей</a></p>';
}

?>
</center>
</body>
</html>