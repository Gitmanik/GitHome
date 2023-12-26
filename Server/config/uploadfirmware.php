<?php
if (isset($_POST["submit"]))
{
    $file = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);
    require_once "../api/pdo_connect.php";
    
    if ($stmt = $pdo->prepare("SELECT count(*) FROM versions WHERE version = :version"))
    {
        $stmt->bindParam(':version', $_POST["version"]);
        if($stmt->execute())
        {
            if ($stmt->fetchColumn() > 0)
            {
                echo "Error: already exists";
                die;
            }
        }
    }

    if($stmt = $pdo->prepare("INSERT INTO versions (version,firmware,file) VALUES(:version, :firmware,:file)")) {
        $stmt->bindParam(':firmware', $_POST["firmware"]);
        $stmt->bindParam(':version', $_POST["version"]);
        $stmt->bindParam(':file', $file);
        $stmt->execute();
    }
    unlink($_FILES["fileToUpload"]["tmp_name"]);
    echo "Upload successful";
}
?>