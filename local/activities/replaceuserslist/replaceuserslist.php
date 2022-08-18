<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$runtime = CBPRuntime::GetRuntime()->IncludeActivityFile('getusersforstagecomplex');


/**
 * @description Активити для замена списка пользователей
 * Class CBPGetUsersForStageComplexWithCheck
 */
class CBPReplaceUsersList extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPReplaceUsersList constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'newUsersArray' => '',
            'inputUsersArray' => '',
            'userFind' => '',
            'userReplace' => '',
        ];
    }

    /**
     * @description Метод для замена пользователя в списке
     * @return int
     */
    public function Execute()
    {
        $arUsers = $this->inputUsersArray;
        $userFind = $this->userFind;
        $userReplace = $this->userReplace;
        if (empty($arUsers) or empty($userFind) or empty($userReplace)) {
            return CBPActivityExecutionStatus::Closed;
        }

        $arUsers = CBPHelper::ExtractUsers($arUsers, $this->GetRootActivity()->GetDocumentId());
        $userFind = current(CBPHelper::ExtractUsers($userFind, $this->GetRootActivity()->GetDocumentId()));
        $userReplace = current(CBPHelper::ExtractUsers($userReplace, $this->GetRootActivity()->GetDocumentId()));

        foreach ($arUsers as $index => $id) {
            if ($id == $userFind) {
                $id = $userReplace;
            }

            $arNewUsers[$index] = "user_{$id}";
        }

        $this->newUsersArray = $arNewUsers ?? [];

        return CBPActivityExecutionStatus::Closed;
    }
}
?>