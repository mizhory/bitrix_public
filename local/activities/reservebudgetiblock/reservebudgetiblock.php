<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Активити для резервирования бюджета
 * Class CBPReserveBudgetIblock
 */
class CBPReserveBudgetIblock extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPReserveBudgetIblock constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'cardId'=>'',
            'iblockId'=>'',
            'reserveStatus' => '',
            'errorMessage' => '',
        ];
    }

    /**
     * @description Резервирует бюджет
     * @return int
     */
    public function Execute()
    {
        $result = \Immo\Tools\ActivityTools::tryReserveBudgetIblock($this->cardId, $this->iblockId);
        $this->reserveStatus = ($result->isSuccess()) ? 'Y' : 'N';
        $this->errorMessage = ($result->isSuccess()) ? '' : implode('; ', $result->getErrorMessages());

        return CBPActivityExecutionStatus::Closed;
    }
}
?>