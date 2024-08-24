<?

class GitHomeConfig implements GitPHPAction
{
    function __construct()
    {
        GitPHP::register_action("config", $this);
    }

    public function render($elements)
    {
        chdir (__DIR__ . "/../Config");
        if (!isset($elements[1]))
        {
            require "index.php";
            die;
        }

        if ($elements[1] == "saveDevice")
        {
            $this->handleSave($elements);
            header("Location: /config");
            die;
        }
        if ($elements[1] == "newDevice")
        {
            GitHomeDevice::createNew("NEW_" . time());
            header("Location: /config");
            die;
        }

        if ($elements[1] == "deleteDevice")
        {
            GitHomeDevice::deleteDevice($elements[2]);
            header("Location: /config");
            die;
        }

        if ($elements[1] == "uploadFirmware")
        {
            $this->handleUploadFirmware($elements);
            header("Location: /config");
            die;
        }
        
        if ($elements[1] == "newTask")
        {
            GitHomeCron::newTask();
            header("Location: /config");
            die;
        }
        if ($elements[1] == "saveTask")
        {
            GitHomeCron::saveTask($_POST["id"], $_POST["name"], $_POST["code"]);
            header("Location: /config");
            die;
        }
        if ($elements[1] == "deleteTask")
        {
            GitHomeCron::deleteTask($_POST["id"]);
            header("Location: /config");

            die;
        }
    
        GitHome::die("GitHomeConfig: No valid subaction.");
    }

    private function handleUploadFirmware($elements)
    {
        $name = $_POST["name"];
        $version = $_POST["version"];

        if (!isset($_FILES["fileToUpload"]))
            GitHome::die("handleUploadFirmware: File not submitted!");

        if (!isset($name))
            GitHome::die("handleUploadFirmware: Name not submitted!");

        if (!isset($version))
            GitHome::die("handleUploadFirmware: Version not submitted!");

        $data = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);

        if (!GitHome::$firmware->addFirmware($name, $version, $data))
            GitHome::die("handleUploadFirmware: Upload unsuccesful!");
    }

    private function handleSave($elements)
    {
        if (!isset($_POST['id']))
            GitHome::die("GitHomeConfig handleSave: No id provived.");
        $data = $_POST;

        $dev = GitHomeDevice::createFromID($data['id']); unset($data['id']);
        $dev->name = $data['name']; unset($data['name']);
        $dev->firmware = $data['firmware'] == "" ? null : $data['firmware']; unset($data['firmware']);
        $dev->version = $data['version'] == "" ? null : $data['version']; unset($data['version']);
        $dev->handler = $data['handler']; unset($data['handler']);
        $dev->loadData($data);

        $dev->save();
    }

    private function isCustomCodeEditor($val)
    {
        foreach ($val["attributes"] as $attrib)
        {
            if ($attrib->getName() == "CustomEdit")
                return $attrib->newInstance()->type;
        }

        return false;
    }

    public function static($filename) {return "/GitHome/Config/{$filename}";}
}

enum CustomEditorType
{
    case CODE_EDITOR;
}

#[Attribute]
class CustomEdit
{
    public readonly CustomEditorType $type;
    public function __construct(CustomEditorType $type)
    {
        $this->type = $type;
    }
}

?>
