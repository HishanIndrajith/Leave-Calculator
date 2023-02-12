<?php

class Leave
{
    // Properties
    private $startDate;
    private $noOfDays;
    private $type;
    private $isConsideredForCalculation;

    // Methods
    function set_startDate($startDate)
    {
        $this->startDate = $startDate;
    }
    function get_startDate()
    {
        return $this->startDate;
    }

    function set_noOfDays($noOfDays)
    {
        $this->noOfDays = $noOfDays;
    }
    function get_noOfDays()
    {
        return $this->noOfDays;
    }

    function set_type($type)
    {
        $this->type = $type;
    }
    function get_type()
    {
        return $this->type;
    }

    function set_isConsideredForCalculation($isConsideredForCalculation)
    {
        $this->isConsideredForCalculation = $isConsideredForCalculation;
    }
    function get_isConsideredForCalculation()
    {
        return $this->isConsideredForCalculation;
    }
}

?>
