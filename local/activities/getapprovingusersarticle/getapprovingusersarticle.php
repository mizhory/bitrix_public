<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Активити для получения согласующих по статье бюджета
 * Class CBPGetApprovingUsersArticle
 */
class CBPGetApprovingUsersArticle extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPGetApprovingUsersArticle constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'arUsers' => '',
            'arUsersArray' => '',
            'articleId'=>'',
            'doneUsers'=>'',
        ];
    }

    /**
     * @description Достает согласующих по статье бюджета
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function Execute()
    {
        $articleId = $this->articleId;
        $users = ($articleId > 0)
            ? \Immo\Tools\ActivityTools::getUsersArticle($articleId)
            : [];

        if (!empty($users)) {
            foreach ($users as $userId) {
                $usersPrepared[] = "user_{$userId}";
            }

            // исключаем уже согласовавших пользователей
            if (!empty($usersPrepared) and !empty($doneUsers = $this->doneUsers)) {
                $usersPrepared = array_values(array_diff($usersPrepared, $doneUsers));
            }

            $this->arUsers = $usersPrepared ?? [];
            $this->arUsersArray = $usersPrepared ?? [];
        } else {
            $this->arUsers = [];
            $this->arUsersArray = [];
        }

        return CBPActivityExecutionStatus::Closed;
    }
}
?>