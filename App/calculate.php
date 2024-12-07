<?php 
namespace App; 
require_once 'Infrastructure/sdbh.php'; 
use sdbh\sdbh;  

class Calculate 
{ 
    public function calculate1() 
    { 
        $dbh = new sdbh(); 
        $product_id = isset($_POST['product']) ? (int)$_POST['product'] : 0; 
        $selected_services = isset($_POST['services']) ? $_POST['services'] : []; 

        // Получаем startDate и endDate из POST
        $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null; 
        $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null; 

        // Проверка на наличие дат
        if ($startDate && $endDate) {
            $startDateTimestamp = strtotime($startDate); 
            $endDateTimestamp = strtotime($endDate); 

            if ($startDateTimestamp === false || $endDateTimestamp === false) { 
                echo "Ошибка: неверный формат даты!"; 
                return; 
            }

            $days = floor(($endDateTimestamp - $startDateTimestamp) / (60 * 60 * 24)); 
        } else {
            echo "Ошибка: даты не указаны!"; 
            return; 
        }

        // Получение данных о продукте
        $product = $dbh->make_query("SELECT * FROM a25_products WHERE ID = $product_id"); 
        if ($product) { 
            $product = $product[0]; 
            $price = $product['PRICE']; 
            $tarif = $product['TARIFF']; 
        } else { 
            echo "Ошибка, товар не найден!"; 
            return; 
        } 

        // Обработка тарифов
        $tarifs = unserialize($tarif); 
        if (is_array($tarifs)) { 
            $product_price = $price; 
            foreach ($tarifs as $day_count => $tarif_price) { 
                if ($days >= $day_count) { 
                    $product_price = $tarif_price; 
                } 
            } 
            $total_price = $product_price * $days; 
        } else { 
            $total_price = $price * $days; 
        } 

        // Расчет цены услуг
        $services_price = 0; 
        foreach ($selected_services as $service) { 
            $services_price += (float)$service * $days; 
        } 

        // Итоговая цена
        $total_price += $services_price; 
        echo $total_price; 
    } 
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $instance = new Calculate(); 
    $instance->calculate1(); 
} 
