<?php
/**
 * @author selcukmart
 * 8.02.2022
 * 09:58
 */

namespace FormGenerator\FormGeneratorClassTraits;

use FormGenerator\Tools\DB\DBInterface;
use FormGenerator\Tools\Row;

trait FormGeneratorClassDataPrepareTrait
{
    protected
        $table,
        $id,
        $id_column_name,
        $row,
        $db;

    private function setDB(): void
    {

        $db_class = $this->generator_array['data']['connection']['db']['object'] ?? '';
        if (!$db_class) {
            return;
        }
        $db_object = new $db_class;
        if ($db_object instanceof DBInterface) {
            $this->db = $db_class;
            $this->generator_array['data']['from'] = 'db';
        }
    }

    /**
     * @return mixed
     */
    public function getDb()
    {
        return $this->db;
    }

    private function databaseVariables(): void
    {
        $this->table = $this->generator_array['data']['table'] ?? '';
        $this->id = $this->generator_array['data']['id'] ?? '';
        $this->id_column_name = $this->generator_array['data']['id_column_name'] ?? 'id';
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    public function setRow(): void
    {
        if ($this->isAdd()) {
            return;
        }
        $row_table_detection = new Row($this, $this->generator_array);
        $row_table_detection->setRow();
        $this->row = $row_table_detection->getRow();

    }

    /**
     * @return mixed
     */
    public function getRow()
    {
        return $this->row;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getIdColumnName()
    {
        return $this->id_column_name;
    }
}