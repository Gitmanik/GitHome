<?php

class CustomCodeDevice extends GitHomeDevice
{
    public ?string $customCode = null;

    public function endpoint($elements)
    {
        $this->legacy($elements);
    }

    public function render()
    {
        $this->runCustomCode(null);
    }

    public function legacy($elements)
    {
        $this->runCustomCode($elements);
    }

    private function runCustomCode($elements)
    {
        if (!isset($this->customCode) || empty($this->customCode))
            return;

        $hour = intval(date("G"));
        $minute = intval(date("i"));
        $second = intval(date("s"));

        try
        {
            eval($this->customCode);
        }
        catch (Throwable $e)
        {
            $this->logError("Custom Code threw: " . $e->getMessage());
        }
    }
}

?>