<?php

namespace Immo\Statements\Helpers\Migrations;

use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Web\Json;
use Sprint\Migration\Helper;

Loader::includeModule('sprint.migration');

class CustomEntityHelper extends Helper
{
    private Query $query;

    /**
     * @param Query $query
     */
    public function setEntity(Query $query)
    {
        $this->query = $query;
        return $this;
    }

    public function getFields()
    {
        $fields = $this->query->getEntity()->getFields();
        $select = [];

        foreach ($fields as $field) {
            if($field->getDataType() === 'integer' && $field->getName() === 'ID') {
                continue;
            }

            $select[] = $field->getName();
        }

        return $select;
    }

    public function getElements()
    {
        return $this->query
            ->setSelect($this->getFields())
            ->fetchAll();
    }

    public function export()
    {
        $data = Json::encode($this->getElements(), JSON_UNESCAPED_UNICODE);
        $exportFile = $this->getEntityFile();

        if(File::putFileContents($exportFile, $data)) {
            return File::isFileExists($exportFile);
        }
    }

    public function import()
    {

        $data = $this->prepareForImport();
        $class = $this->query->getEntity()->getDataClass();


        /**
         * @var Result $add
         */
        $add = call_user_func_array([$class, 'addMulti'], $data);

        return $add->isSuccess();
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     */
    public function prepareForImport()
    {
        $file = $this->getEntityFile();

        if(File::isFileExists($file)) {
            return Json::decode(File::getFileContents($file));
        }
    }

    /**
     * @return Query
     */
    public function getQuery(): Query
    {
        return $this->query;
    }

    public function getEntityFile()
    {
        return sprintf(
            '%s/local/php_interface/migrations/custom_entity/%s.json',
            Application::getDocumentRoot(),
            $this->query->getEntity()->getDBTableName()
        );
    }
}