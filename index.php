<?php
require_once 'App/Infrastructure/sdbh.php';
use sdbh\sdbh;
$dbh = new sdbh();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Прокат Y</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="assets/css/style.css" rel="stylesheet"/>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
    <div class="row row-header">
        <div class="col-12" id="count">
            <img src="assets/img/logo.png" alt="logo" style="max-height:50px"/>
            <h1>Прокат Y</h1>
        </div>
    </div>

    <div class="row row-form">
        <div class="col-12">
            <form action="App/calculate.php" method="POST" id="form">

                <?php
                $products = $dbh->make_query('SELECT * FROM a25_products');
                if (is_array($products)) {
                    ?>
                    <label class="form-label" for="product">Выберите продукт:</label>
                    <select class="form-select" name="product" id="product">
                        <?php foreach ($products as $product) {
                            $name = $product['NAME'];
                            $price = $product['PRICE'];
                            $tarif = $product['TARIFF'];
                            ?>
                            <option value="<?= $product['ID']; ?>"><?= $name; ?></option>
                        <?php } ?>
                    </select>
                <?php } ?>

                <div>
                    <label for="datepicker_start" class="form-label">Дата начала аренды:</label>
                    <input type="text" id="datepicker_start" name="start_date" class="form-control">
                </div>
                <div>
                    <label for="datepicker_end" class="form-label">Дата окончания аренды:</label>
                    <input type="text" id="datepicker_end" name="end_date" class="form-control">
                </div>
                <div id="days-display" class="mt-2"></div>


                <?php
                $services = unserialize($dbh->mselect_rows('a25_settings', ['set_key' => 'services'], 0, 1, 'id')[0]['set_value']);
                if (is_array($services)) {
                    ?>
                    <label for="customRange1" class="form-label">Дополнительно:</label>
                    <?php
                    $index = 0;
                    foreach ($services as $k => $s) {
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="services[]" value="<?= $s; ?>" id="flexCheck<?= $index; ?>">
                            <label class="form-check-label" for="flexCheck<?= $index; ?>">
                                <?= $k ?>: <?= $s ?>
                            </label>
                        </div>
                    <?php $index++;
                    } ?>
                <?php } ?>

                <button type="submit" class="btn btn-primary mt-2">Рассчитать</button>
            </form>

            <h5>Итоговая стоимость: <span id="total-price"></span></h5>
        </div>
    </div>
</div>

<script>
    $(function() {
        $("#datepicker_start, #datepicker_end").datepicker({
            dateFormat: "yy-mm-dd",
            onSelect: function(dateText, inst) {
                updateDays();
                calculateRent();
            }
        });
        updateDays(); // Изначально отображаем 0 дней

        function updateDays() {
            let startDate = $("#datepicker_start").val();
            let endDate = $("#datepicker_end").val();
            if (startDate && endDate) {
                let start = new Date(startDate);
                let end = new Date(endDate);
                let diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)); //Количество дней
                $("#days-display").text("Количество дней: " + diff);
            } else {
                $("#days-display").text("Количество дней: 0");
            }
        }

        $("#form").submit(function(event) {
            event.preventDefault();
            calculateRent();
        });

        function calculateRent() {
            $.ajax({
                url: 'App/calculate.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $("#total-price").text(response);
                },
                error: function() {
                    $("#total-price").text('Ошибка при расчете');
                }
            });
        }
    });
</script>
</body>
</html>