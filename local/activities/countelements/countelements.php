<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Активити для подсчета кол-ва элементов в массиве
 * Class CBPCountElements
 */
class CBPCountElements extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPCountElements constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'count' => '',
            'inputArray'=>'',
        ];
    }

    /**
     * @description Считает кол-во элементов в массиве
     * @return int
     */
    public function Execute()
    {
        $inputArray = $this->inputArray;
        $this->count = (is_array($inputArray)) ? count($inputArray) : 0;
        return CBPActivityExecutionStatus::Closed;
    }
}
?>