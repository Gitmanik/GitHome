<?php

class GitHomeDevice
{
    public string $id;
    public string $handler;
    public ?string $firmware = null;
    public string $name;
    public ?string $version = null;
    public ?string $lastReportTimestamp = null;
    public ?string $lastReportIP = null;

    function __construct($row)
    {
        if (!isset($row))
            GitHome::die("Tried to create device with unset row!");

        $this->id = $row["id"];
        $this->handler = $row["handler"];
        $this->firmware = $row["firmware"];
        $this->name = $row["name"];
        $this->version = $row["version"];
        $this->lastReportTimestamp = $row["lastReportTimestamp"];
        $this->lastReportIP = $row["lastReportIP"];
        $this->loadData(json_decode($row["data"], true));
    }

    public function save()
    {
        $stmt = GitHome::db()->prepare("UPDATE devices SET handler = :handler, firmware = :firmware, name = :name, version = :version, lastReportTimestamp = :ts, lastReportIP = :ip, data = :data WHERE id = :id");
        $stmt->bindValue(":id", $this->id);
        $stmt->bindValue(":handler", $this->handler);
        $stmt->bindValue(":firmware", $this->firmware);
        $stmt->bindValue(":name", $this->name);
        $stmt->bindValue(":version", $this->version);
        $ts = GitPHP::CURRENT_TIMESTAMP(); $stmt->bindValue(":ts", $ts);
        $stmt->bindValue(":ip", $_SERVER['REMOTE_ADDR']);
        $data = json_encode($this->exportData()); $stmt->bindValue(":data", $data);
        
        $stmt->execute();
    }

    public function shouldUpdate()
    {
        if (is_null($this->firmware) || $this->firmware == "")
            return false;

        $ver = -1;
        if (!is_null($this->version))
            $ver = intval($this->version);

        $newestVer = GitHome::$firmware->listVersions($this->firmware)[0];

        if ($newestVer > $ver)
            return true;

        return false;
    }

    public static function createFromID($id)
    {
        $stmt = GitHome::db()->prepare("SELECT * FROM devices WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row == false)
            return null;

        return GitHomeDevice::createFromRow($row);
    }

    public static function createFromRow($row)
    {
        if (!in_array($row['handler'], GitHome::$handlers))
            GitHome::die("Tried to create device without supported handler class!");

        return new $row['handler']($row);
    }

    public static function createNew($id)
    {
        $stmt = GitHome::db()->prepare("INSERT INTO devices (id, handler, name, data) VALUES (:id, :handler, :name, :data)");
        $stmt->bindValue(":id", $id);
        $stmt->bindValue(":handler", "BlankDevice");
        $stmt->bindValue(":name", $id);
        $stmt->bindValue(":data", json_encode(array()));
        
        $stmt->execute();

        return GitHomeDevice::createFromID($id);
    }

    public static function deleteDevice($id)
    {
        $stmt = GitHome::db()->prepare("DELETE FROM devices WHERE id = :id");
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        GitHome::logNormal("Removed device {$id}");
        return;
    }

    public function loadData($data)
    {
        if (!isset($data))
            GitHome::die("Tried to load unset data!");

        $reflect = new ReflectionClass($this);
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($props as $prop)
        {
            if (isset($data[$prop->name]))
                $prop->setValue($this, $data[$prop->name]);
        }
    }

    public function exportData()
    {
        $reflect = new ReflectionClass($this);
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
        $data = array();
        foreach ($props as $prop)
        {
            if ($prop->class == "GitHomeDevice")
                continue;
            if ($prop->isInitialized($this))
                $data[$prop->name] = $prop->getValue($this);
            else
                $data[$prop->name] = "";
        }
        return $data;
    }

    public function logNormal($text) {GitHome::logNormal($text, $this);}
    public function logWarn($text) {GitHome::logWarn($text, $this);}
    public function logError($text) {GitHome::logError($text, $this);}

    public function endpoint($elements) { /* VIRTUAL */ }
    public function legacy($elements) { /* VIRTUAL */ }
    public function render() { /* VIRTUAL */ }
}

class BlankDevice extends GitHomeDevice
{
    public function endpoint($elements)
    {
        $imploded = implode(",", $elements);
        $this->logWarn("Endpoint reached on Blank: {$imploded}");
    }

    public function legacy($elements)
    {
        $imploded = implode(",", $elements);
        $this->logWarn("Legacy reached on Blank: {$imploded}");
    }
}

?>