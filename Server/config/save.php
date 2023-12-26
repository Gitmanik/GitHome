<?php
require_once "../api/pdo_connect.php";

if (isset($_POST['addBox']))
{
    $stmt = $pdo->prepare("INSERT INTO boxes (name, code) VALUES (:name, :code)");
    $stmt->bindParam(':name', $s = "New Box");
    $stmt->bindParam(':code', base64_encode('echo "Hello Box!";'));
    $stmt->execute();
    die;
}

if (isset($_POST['isBox']))
{
    if($stmt = $pdo->prepare("SELECT * FROM boxes WHERE id = :id")){
        $stmt->bindParam(":id", $_POST["id"]);
        if($stmt->execute())
        {
            if($stmt = $pdo->prepare("UPDATE boxes SET code= :code, name = :name, visible = :visible WHERE id = :id")){
                $stmt->bindParam(':id', $_POST["id"]);
                $stmt->bindParam(':name', $_POST['name']);
                $stmt->bindParam(':code', $_POST['code']);
                $vis = $_POST['visible'] == "true" ? 1 : 0;
                $stmt->bindParam(':visible', $vis);
                if (!$stmt->execute())
                {
                    echo "err2";
                }
                else
                echo "OK";
            }
        }
    }
} else if (isset($_POST['isDevice']))
{
    if($stmt = $pdo->prepare("SELECT * FROM devices WHERE device_id = :device_id")){
        $stmt->bindParam(":device_id", $_POST["id"]);
        if($stmt->execute())
        {
            if($row = $stmt->fetch())
            {
            
            if($stmt = $pdo->prepare("UPDATE devices SET type = :type, name = :name, data = :data, firmware = :firmware, visible = :visible, code = :code, command = :command WHERE device_id = :device_id")){
                $stmt->bindParam(':device_id', $_POST["id"]);

                $stmt->bindValue(':name',  $_POST["name"] ?? $row["name"]);
                $stmt->bindValue(':type', $_POST["type"] ?? $row["type"]);
                $stmt->bindValue(':firmware', $_POST["firmware"] ?? $row["firmware"]);
                $stmt->bindValue(':visible', ($_POST['visible'] ?? "false") == "true" ? 1 : 0);

                $stmt->bindValue(':data', $_POST["data"] ?? $row["data"]);
                $stmt->bindValue(':code', $_POST['code'] ?? $row["code"]);
                $stmt->bindValue(':command', $_POST["datatosend"] ?? $row["command"]);

                if (!$stmt->execute())
                {
                    echo "err2";
                }
                else
                echo "OK";
            }
        }
    }
    }

}



die;
?>