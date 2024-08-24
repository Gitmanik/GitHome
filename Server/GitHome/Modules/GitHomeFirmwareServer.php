<?php

class GitHomeFirmwareServer
{
    public function getNewestFirmware($name)
    {
        $newest_ver = $this->listVersions($name);
        if (is_null($newest_ver) || count($newest_ver) == 0) {
            GitHome::logError("FirmwareServer: No firmware for {$name}");
            return null;
        }
        return $this->getFirmware($name, $newest_ver[0]);
    }

    public function listFirmware($limit)
    {
        $stmt = GitHome::db()->prepare("SELECT name, version FROM firmware " . ($limit ? " GROUP BY name" : "") . " ORDER BY version DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listVersions($name)
    {
        $stmt = GitHome::db()->prepare("SELECT version FROM firmware WHERE name = :name ORDER BY version DESC");
        $stmt->bindValue(":name", $name);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getFirmware($name, $version)
    {
        if (!in_array($version, $this->listVersions($name))) {
            GitHome::logError("FirmwareServer: Tried to get nonexistent version {$version} for {$name}!");
            return null;
        }

        $stmt = GitHome::db()->prepare("SELECT data FROM firmware WHERE name = :name AND version = :version");
        $stmt->bindValue(":name", $name);
        $stmt->bindValue(":version", $version);
        $stmt->execute();
        $stmt->bindColumn(1, $content, PDO::PARAM_LOB);
        $stmt->fetch();
        return $content;
    }

    public function addFirmware($name, $version, $data)
    {
        $stmt = GitHome::db()->prepare("INSERT INTO firmware (name, version, data) VALUES (:name, :version, :data)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':version', $version);
        $stmt->bindParam(':data', $data);
        $stmt->execute();
        GitHome::logNormal("FirmwareServer: Upload for {$name}, version {$version} successful.");
        return true;
    }
}
