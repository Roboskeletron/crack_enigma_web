<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<?php
    $db = pg_connect("host=localhost port=5432 dbname=testdb user=admin password=SWBattlefront.Roboskeletron1920")
    or die("Can't connect to database".pg_last_error());
    //echo $db;
    $data = pg_query($db, "SELECT * FROM users");
    
    while ($row = pg_fetch_row($data)){
        foreach ($row as $col){
            echo $col." ";
        }
        echo "<br/>\n";
    }
    pg_close($db);
    ?>
</body>
</html>