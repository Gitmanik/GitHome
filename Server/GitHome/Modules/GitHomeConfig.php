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

        if ($elements[1] == "uploadFirmware")
        {
            $this->handleUploadFirmware($elements);
            header("Location: /config");
            die;
        }

        // TODO: Validate incoming data
        if ($elements[1] == "device")
        {
            if (isset($_POST["save"]))
                $this->handleSave($elements);
            else if (isset($_POST["delete"]))
                GitHomeDevice::deleteDevice($_POST["id"]);
            else if (isset($_POST["new"]))
                GitHomeDevice::createNew("NEW_" . time());
            else GitHome::die("GitHomeConfig device: No valid subaction.");

            header("Location: /config");
            die;
        }

        // TODO: Validate incoming data
        if ($elements[1] == "task")
        {
            if (isset($_POST["save"]))
                GitHomeCron::saveTask($_POST["id"], $_POST["name"], $_POST["code"]);
            else if (isset($_POST["delete"]))
                GitHomeCron::deleteTask($_POST['id']);
            else if (isset($_POST["new"]))
                GitHomeCron::newTask();
            else GitHome::die("GitHomeConfig task: No valid subaction.");

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

    public function static($filename) {return GitPHP::static("/GitHome/Config/{$filename}");}
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
