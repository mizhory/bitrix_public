<?

use Vigr\Helpers\Helper;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$runtime = CBPRuntime::GetRuntime()->IncludeActivityFile('getusersforstagecomplex');


/**
 * @description Активити для получения согласующих, с проверками
 * Class CBPGetUsersForStageComplexWithCheck
 */
class CBPGetUsersForStageComplexWithCheck extends CBPGetUsersForStageComplex
{
    /**
     * CBPGetUsersForStageComplexWithCheck constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'bp' => '',
            'stage' => '',
            'depId' => '',
            'depIdOld' => '',
            'beId' => '',
            'beIdOld' => '',
            'userStr' => '',
            'userArray' => '',
            'doneUsers' => ''
        ];
    }

    /**
     * @description Метод для получения пользователей по стадии с проверками
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function Execute()
    {
        $params = $this->collectParams();
        if (empty($params['COMPANY']['ID']) and empty($params['BE']['ID'])) {
            return CBPActivityExecutionStatus::Closed;
        }

        $departmentId = 0;

        if (!empty($params['COMPANY']['ID'])) {
            $departmentId = ($params['COMPANY']['TYPE'] == 'old')
                ? \Immo\Structure\Organization::defineCompanyComp($params['COMPANY']['ID'])
                : $params['COMPANY']['ID'];
        } elseif (!empty($params['BE']['ID'])) {
            $departmentId = ($params['BE']['TYPE'] == 'old')
                ? \Immo\Structure\Organization::defineBeComp($params['BE']['ID'])
                : $params['BE']['ID'];
        }

        $arUsers = static::getUsersForStageActivity((string)$this->stage, (int)$this->bp, (int)$departmentId);

        $doneUsers = $this->doneUsers;
        $checkedUsers = static::checkUsers($arUsers, (array)$doneUsers, $params);

        $this->userArray = $checkedUsers['array'];
        $this->userStr = $checkedUsers['string'];

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

            $arParams['COMPANY'] = [
                'TYPE' => $type,
                'ID' => $id,
            ];
            break;
        }

        foreach (['new' => $this->beId, 'old' => $this->beIdOld] as $type => $id) {
            if ((int)$id <= 0) {
                continue;
            }

            $arParams['BE'] = [
                'TYPE' => $type,
                'ID' => $id,
            ];
            break;
        }

        return $arParams;
    }

    /**
     * @description Проверка переданных пользователей
     * @param array $arUsers
     * @param array $doneUsers
     * @param array $params
     * @return array
     */
    public static function checkUsers(array $arUsers, array $doneUsers = [], array $params = []): array
    {
        foreach ($arUsers['array'] as $index => $user) {
            $result = static::checkSingleUser((int)str_replace('user_', '', $user), $params);
            if ($result['status'] != 'ok') {
                unset($arUsers['array'][$index]);
                continue;
            }

            $arUsers['array'][$index] = "user_{$result['userId']}";
        }

        if (empty($doneUsers)) {
            return $arUsers;
        }

        $arUsers['array'] = array_values(array_diff($arUsers['array'], $doneUsers));
        $arUsers['string'] = implode(',', $arUsers['array']);

        return $arUsers;
    }

    /**
     * @description Проверка пользователя по графику отсутствия и листу увовльнения
     * @param int $userId
     * @param array $params
     * @return array
     */
    public static function checkSingleUser(int $userId, array $params): array
    {
        return (new \Immo\Structure\Organization($userId))->findUserActing($params);
    }
}
?>