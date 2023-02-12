<?php

class Usage
{
    private $email;
    private $lastAccessedTime;
    private $usageCount;

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setLastAccessedTime($lastAccessedTime)
    {
        $this->lastAccessedTime = $lastAccessedTime;
    }

    public function getLastAccessedTime()
    {
        return $this->lastAccessedTime;
    }

    public function setUsageCount($usageCount)
    {
        $this->usageCount = $usageCount;
    }

    public function getUsageCount()
    {
        return $this->usageCount;
    }
}

?>
