<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Активити для проверки Пользователей по графику отсутсвий/увольнений/уже согласовывал
 * Class CBPGetUsersWithCheckIblock
 */
class CBPGetUsersWithCheckIblock extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPGetUsersWithCheckIblock constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'user' => '',
            'userStr' => '',
            'error' => '',
            'depId' => '',
            'depIdOld' => '',
            'beId' => '',
            'beIdOld' => '',
            'status' => '',
            'doneUsers' => ''
        ];
    }

    /**
     * @description Проверка пользователей
     * @return int
     */
    public function Execute()
    {
        $rootActivity = $this->GetRootActivity();
        $documentId = $rootActivity->GetDocumentId();

        $singeUser = false;

        $usersParam = $this->user;
        if (!is_array($usersParam)) {
            if (empty($usersParam) or count($usersParam) < 1) {
                return CBPActivityExecutionStatus::Closed;
            }

            $usersParam = explode(',', str_replace([';', ' '], '', $usersParam));
            $singeUser = (count($usersParam) == 1);
        }

        foreach ($usersParam as $index => $user) {
            $userId = (int)trim($user);
            if (strstr($user, 'user_') !== false or $userId <= 0) {
                $prepareUsers[$index] = $user;
                continue;
            }

            $prepareUsers[$index] = "user_{$userId}";
        }

        $users = $prepareUsers ?? [];

        if (empty($users)) {
            return CBPActivityExecutionStatus::Closed;
        }

        $arUsers = CBPHelper::ExtractUsers($users, $documentId);
        $arNew = [];

        foreach ($arUsers as $user){
            $result = (new \Immo\Structure\Organization($user))->findUserActing($this->collectParams());
            if ($result['status'] == 'error') {
                $this->status = 'error';
                $this->error = $result['error'];
                return CBPActivityExecutionStatus::Closed;
            } else {
                $arNew[] = 'user_'.$result['userId'];
            }
        }

        $doneUsers = $this->doneUsers;
        $doneUsers = (empty($doneUsers)) ? [] : $doneUsers;

        if ($singeUser) {
            $this->userStr = current(array_diff(((is_array($arNew)) ? $arNew : []), $doneUsers));
        } else {
            $this->userStr = array_values(array_diff(((is_array($arNew)) ? $arNew : []), $doneUsers));
        }

        return CBPActivityExecutionStatus::Closed;
    }

    /**
     * @description Формирует массив параметров для проверки пользователей
     * @return int[][]
     */
    protected function collectParams(): array
    {
        $arParams = [
            'COMPANY' => [
                'ID' => 0
            ],
            'BE' => [
                'ID' => 0
            ],
        ];

        foreach (['new' => $this->depId, 'old' => $this->depIdOld] as $type => $id) {
            if ((int)$id <= 0) {
                continue;
            }

            $arParams['BE'] = [
                'TYPE' => $type,
                'ID' => $id,
            ];
            break;
        }

        foreach (['new' => $this->beId, 'old' => $this->beIdOld] as $type => $id) {
            if ((int)$id <= 0) {
                continue;
            }

            $arParams['COMPANY'] = [
                'TYPE' => $type,
                'ID' => $id,
            ];
            break;
        }

        return $arParams;
    }
}
?>