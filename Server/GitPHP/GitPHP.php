<?
class GitPHP 
{
    private static array $actions = array();
    private static PDO $pdo;

    private static GitPHPAction $current_action;

    public static function index()
    {
        foreach (glob("Modules/*/module.php") as $module)
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

    public static function static($path)
    {
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
            $username = getenv('MYSQL_USER');
            $password = getenv('MYSQL_PASSWORD');
            $server = getenv('MYSQL_SERVER');
            $db = getenv('MYSQL_DB');

            GitPHP::$pdo = new PDO("mysql:charset=utf8mb4;host={$server};dbname={$db}", $username, $password);
            GitPHP::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            die("GitPHP: Could not connect to MySQL: <br>". $e->getMessage());
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
}

interface GitPHPAction
{
    public function render($elements);
}

?>