<?php

class Box {
    public $id;
    public $code;
    public $name;
    public $visible;

    public function __construct($row)
    {
        $this->id = $row['id'];
        $this->code = base64_decode($row['code']);
        $this->name = $row['name'];
        $this->visible = $row['visible'] == "1";
    }

    public static function fromRow($row)
    {
        return new Box($row);
    }

    public function evalCode($pdo)
    {
        try
        {
            return eval($this->code);
        }
        catch(Throwable $e)
        {
            echo 'Error occured while running '. $this->name . ' code: ' . $e->getMessage();
        }
    }

    public function updateSQL($pdo)
    {
        if($stmt = $pdo->prepare("UPDATE boxes SET code = :code WHERE id = :id")){
            $stmt->bindParam(':device_id', $this->id);
            $stmt->bindParam(':data', base64_encode($this->code));
            
            if (!$stmt->execute())
            {
                echo "err";
            }
        }
    }
}

?>