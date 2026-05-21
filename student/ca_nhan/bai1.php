<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng cửu chương</title>
</head>

<body>
    <div class="box">
        <h2>Bảng cửu chương (2 -> 9)</h2>
        <?php
        for ($i = 2; $i <= 9; $i++) {
            echo "<b>Bảng $i:</b><br>";
            for ($j = 1; $j <= 10; $j++) {
                echo "$i x $j = " . ($i * $j) . "<br>";
            }
            echo "<br>";
        }
        ?>
    </div>
</body>

</html>