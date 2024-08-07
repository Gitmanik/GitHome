<?
class GitPHP 
{
    private static GitPHP $instance;

    private static $actions = array();
    private static PDO $pdo;

    public static function register_action($name, $action)
    {
        GitPHP::$actions[$name] = $action;
    }

    public static function get_action($name)
    {
        if (array_key_exists($name, GitPHP::$actions))
            return GitPHP::$actions[$name];
        else return null;
    }

    private static function connect_db()
    {
        try
        {
            GitPHP::$pdo = new PDO("sqlite:/var/www/html/data/SmartHome.db"); //TODO: Move to config file
            GitPHP::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            die("ERROR: Could not connect. " . $e->getMessage()); //TODO: Better exception handling
        }
    }

    public static function db()
    {
        if (!isset(GitPHP::$pdo))
        {
            GitPHP::connect_db();
        }
        return GitPHP::$pdo;
    }


    public static function log_common($id, $level, $data)
    {
        if($stmt = GitPHP::$instance->db()->prepare("INSERT INTO logs (device_id, data, level) VALUES (:id, :data, :level)")){
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':data', $data);
            $stmt->bindParam(':level', $level);
            $stmt->execute();
        }
    }
}

interface GitPHPRenderer
{
    public function render($elements);
}

interface GitHomeDevice
{
    public function get_name();
}

?>