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
        if ($stmt = GitHome::db()->prepare("SELECT name, version FROM firmware " . ($limit ? " GROUP BY name" : "") . " ORDER BY version DESC")) {
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                GitHome::logError("FirmwareServer: listFirmware unsuccesful! (2)");
                return null;
            }
        } else {
            GitHome::logError("FirmwareServer: listFirmware unsuccesful! (1)");
            return null;
        }
    }

    public function listVersions($name)
    {
        if ($stmt = GitHome::db()->prepare("SELECT version FROM firmware WHERE name = :name ORDER BY version DESC")) {
            $stmt->bindValue(":name", $name);
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            } else {
                GitHome::logError("FirmwareServer: List for {$name} unsuccesful! (2)");
            }
        } else {
            GitHome::logError("FirmwareServer: List for {$name} unsuccesful! (1)");
        }
        return null;
    }
    public function getFirmware($name, $version)
    {
        if (!in_array($version, $this->listVersions($name))) {
            GitHome::logError("FirmwareServer: Tried to get nonexistent version {$version} for {$name}!");
            return null;
        }

        if ($stmt = GitHome::db()->prepare("SELECT data FROM firmware WHERE name = :name AND version = :version")) {
            $stmt->bindValue(":name", $name);
            $stmt->bindValue(":version", $version);
            if ($stmt->execute()) {
                $stmt->bindColumn(1, $content, PDO::PARAM_LOB);
                $stmt->fetch();
                return $content;
            } else {
                GitHome::logError("FirmwareServer: Get for {$name}, version {$version} unsuccesful! (2)");
            }
        } else {
            GitHome::logError("FirmwareServer: Get for {$name}, version {$version} unsuccesful! (1)");
        }
    }

    public function addFirmware($name, $version, $data)
    {
        if ($stmt = GitHome::db()->prepare("INSERT INTO firmware (name, version, data) VALUES (:name, :version, :data)")) {
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':version', $version);
            $stmt->bindParam(':data', $data);
            if ($stmt->execute()) {
                GitHome::logNormal("FirmwareServer: Upload for {$name}, version {$version} successful.");
                return true;
            } else {
                GitHome::logError("FirmwareServer: Upload for {$name}, version {$version} unsuccesful! (2)");
            }
        } else {
            GitHome::logError("FirmwareServer: Upload for {$name}, version {$version} unsuccesful! (1)");
        }
        return false;
    }
}
