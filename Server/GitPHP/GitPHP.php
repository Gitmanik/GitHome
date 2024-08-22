<?

GitPHP::$SQLITE_DB_PATH = "/var/www/html/data/SmartHome.db";

class GitPHP 
{
    public static string $SQLITE_DB_PATH = "";
    private static array $actions = array();
    private static PDO $pdo;

    private static GitPHPAction $current_action;

    public static function index()
    {
        foreach (glob("modules/*/module.php") as $module)
        {
            require_once $module;
        }

        foreach (get_declared_classes() as $class)
            if (is_subclass_of($class, 'GitPHPAction'))
                new $class;

        $path = parse_url($_SERVER['REQUEST_URI'])['path'];
        $path = trim($path, '/');

        $elements = explode('/', $path);

        if ($elements[0] == "")
        {
            if (GitPHP::get_action("default") == null)
            {
                echo "GitPHP: No default action specified!";
                exit();
            }
            GitPHP::$current_action = GitPHP::get_action("default");
            GitPHP::get_action("default")->render($elements);
            exit();
        }

        $action = GitPHP::get_action($elements[0]);
        if ($action != null)
        {
            GitPHP::$current_action = $action;
            $action->render($elements);
            exit();
        }

        echo "GitPHP: Requested non-existent action!<br>";
        var_dump($elements);
    }

    public static function static($filename)
    {
        $path = GitPHP::$current_action->static($filename);
        $modtime = filemtime($_SERVER['DOCUMENT_ROOT'] . $path);
        echo "{$path}?filever={$modtime}";
    }

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
            GitPHP::$pdo = new PDO("sqlite:" . GitPHP::$SQLITE_DB_PATH);
            GitPHP::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            GitPHP::$pdo->exec("PRAGMA journal_mode = WAL");
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


    public static function CURRENT_TIMESTAMP() { return date("Y-m-d H:i:s");}
    public static function logNormal($text, $id = null) { GitPHP::log_common($id, 0, $text); }
    public static function logError($text, $id = null) { GitPHP::log_common($id, 2, $text); }
    public static function logWarn($text, $id = null) { GitPHP::log_common($id, 1, $text); }

    public static function log_common($id, $level, $data)
    {
        if($stmt = GitPHP::db()->prepare("INSERT INTO logs (device_id, data, level, date) VALUES (:id, :data, :level, :time)")){
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':data', $data);
            $stmt->bindParam(':level', $level);
            $ts = GitPHP::CURRENT_TIMESTAMP(); $stmt->bindParam(':time', $ts);
            $stmt->execute();
        }
    }

    public static function getLogs($maxCount, $minLevel = PHP_INT_MAX)
    {
        if($stmt = GitPHP::db()->prepare("SELECT * FROM logs WHERE level <= :level ORDER BY id DESC LIMIT :count")){
            $stmt->bindParam(":level", $minLevel);
            $stmt->bindParam(":count", $maxCount);
            if($stmt->execute())
            {
                return $stmt->fetchAll();
            }
        }
    }
}

interface GitPHPAction
{
    public function render($elements);
    public function static($file);
}

?>