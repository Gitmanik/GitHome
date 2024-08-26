<?php

class GitHomeCron implements GitPHPAction 
{
    function __construct()
    {
        GitPHP::register_action("cron", $this);
    }

    public function render($elements)
    {
        if (!isset($elements[1]))
        {
            $tasks = $this->getTasks();
            foreach ($tasks as $task)
                $this->executeTask($task);
        }

    }

    private function executeTask($task)
    {
        $hour = intval(date("G"));
        $minute = intval(date("i"));
        $second = intval(date("s"));

        try
        {
            eval($task['code']);
        }
        catch (Throwable $e)
        {
            GitHome::logError("Cron Task threw: " . $e->getMessage());
        }
    }

    public static function newTask()
    {
        $stmt = GitHome::db()->prepare("INSERT INTO cron (name, code) VALUES (:name, :code)");
        $stmt->bindValue(":name", "New Task");
        $stmt->bindValue(":code", "echo 'Hello Task!';");
        $stmt->execute();
    } 

    public static function saveTask($id, $name, $code)
    {
        $stmt = GitHome::db()->prepare("UPDATE cron SET name = :name, code = :code WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name" , $name);
        $stmt->bindparam(":code", $code);

        $stmt->execute();
    }


    public static function deleteTask($id)
    {
        $stmt = GitHome::db()->prepare("DELETE FROM cron WHERE id = :id");
        $stmt->bindParam(":id", $id);

        $stmt->execute();
        
    }

    public static function getTasks()
    {
        $stmt = GitHome::db()->prepare("SELECT id, name, code FROM cron");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>