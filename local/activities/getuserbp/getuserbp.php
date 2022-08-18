<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Возвращает строку пользователя
 * Class CBPGetUserBp
 */
class CBPGetUserBp extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPGetUserBp constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'idUser' => '',
            'userString' => ''
        ];
    }

    /**
     * @description Возвращает строку пользователя. Если передан массив, возвращает массив строк
     * @return int
     */
    public function Execute()
    {
        $idUsers = $this->idUser;
        if (is_array($idUsers)) {
            $users = [];
            foreach ($idUsers as $id) {
                if ($id <= 0) {
                    continue;
                }

                $users[] = "user_{$id}";
            }

            $this->userString = (!empty($users)) ? $users : [];
        } else {
            $id = (int)$this->idUser;
            $this->userString = ($id > 0) ? "user_{$id}" : '';
        }
        return CBPActivityExecutionStatus::Closed;
    }
}
?>