<?php
/**
 * @author selcukmart
 * 3.02.2021
 * 13:14
 */

namespace FormGenerator\Tools;


use FormGenerator\FormGeneratorDirector;

class CheckedControl
{
    private
        $checked = false,
        $row = null,
        $control_array,
        $checkeds = [],
        $ckeck_id = 0,
        $field,
        $from,
        $formGenerator;

    public function __construct(FormGeneratorDirector $formGenerator, array $control_array, $field, $row = null)
    {
        $this->formGenerator = $formGenerator;
        $this->row = $row;
        $this->field = $field;
        $this->control_array = $control_array;
        if (!$this->formGenerator->isAdd()) {
            $this->from = $this->control_array['from'] ?? $this->detectFrom();
        }

    }

    private function detectFrom(): string
    {
        $from = '';
        foreach ($this->control_array as $option => $value) {
            if (isset($this->control_array[$option])) {
                $from = $option;
                break;
            }
        }
        return $from;
    }

    public function control($id): bool
    {
        if ($this->formGenerator->isAdd()) {
            return false;
        }
        if (empty($id)) {
            $this->checked = false;
            return $this->checked;
        }

        if (isset($this->checkeds[$id])) {
            $this->checked = $this->checkeds[$id];
            return $this->checked;
        }
        $this->checked = false;
        $this->ckeck_id = $id;

        $this->checkeds[$id] = $this->{$this->from}();
        return $this->checked;
    }

    private function key_label_array()
    {
        if (in_array($this->ckeck_id, $this->control_array['key_label_array'], true)) {
            $this->checked = true;
            return $this->checked;
        }
    }

    private function sql()
    {
        $sql = $this->control_array['sql'];
        $this_field = $this->thisField();
        $foreign_field = $this->foreignField();
        $has_where = false;
        if (isset($this->control_array['has_where'])) {
            $has_where = $this->control_array['has_where'];
        } elseif (false !== stripos($sql, "where")) {
            $has_where = true;
        }
        $start = $has_where ? ' AND ' : ' WHERE ';

        $sql .= $start . $this_field . "='" . $this->ckeck_id . "' AND " . $foreign_field . "=" . $this->row['id'] . " LIMIT 1";
        $this->checked = (bool)$this->getDb()::rowCount($this->getDb()::query($sql));
        return $this->checked;
    }

    /**
     * @return bool
     */
    public function isChecked(): bool
    {
        return $this->checked;
    }

    public function __destruct()
    {

    }

    /**
     * @return mixed
     * @author selcukmart
     * 5.02.2022
     * 12:01
     */
    private function thisField()
    {
        return $this->control_array['parameters']['this_field'] ?? $this->field;
    }

    /**
     * @return mixed
     * @author selcukmart
     * 5.02.2022
     * 12:02
     */
    private function foreignField()
    {
        return $this->control_array['parameters']['foreign_field'];
    }

    /**
     * @return mixed
     * @author selcukmart
     * 5.02.2022
     * 12:03
     */
    private function getTable()
    {
        return $this->control_array['parameters']['table'];
    }

    /**
     * @return mixed
     * @author selcukmart
     * 5.02.2022
     * 12:03
     */
    private function getDb()
    {
        return $this->formGenerator->getDb();
    }
}