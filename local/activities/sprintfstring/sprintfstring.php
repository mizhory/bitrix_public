<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Активити прокидывания строк в шаблон (sprintf)
 * Class CBPBudgetActivityIblock
 */
class CBPSprintFString extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPBudgetActivityIblock constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'stringTemplate' => '',
            'variables' => '',
            'stringFinal' => '',
        ];
    }

    /**
     * @description Заменяет в строки в шаблоне
     * @return int
     */
    public function Execute()
    {
		$template = $this->stringTemplate;
		if (empty($template)) {
            return CBPActivityExecutionStatus::Closed;
        }

		$variables = $this->variables;
        foreach (explode('; ', $variables) as $index => $value) {
            $arVars[$index] = (empty(trim($value))) ? '-' : (string)trim($value);
		}

        if (empty($arVars)) {
            return CBPActivityExecutionStatus::Closed;
        }

		$this->stringFinal = sprintf($template, ...$arVars);

        return CBPActivityExecutionStatus::Closed;
    }
}