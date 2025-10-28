<?php
/**
 * Prepares row
 * @author selcukmart
 * 24.01.2021
 * 16:56
 */

namespace FormGenerator\Tools;

use FormGenerator\FormGeneratorDirector;
use GlobalTraits\ErrorMessagesWithResultTrait;
use Helpers\Classes;

class Row
{
    use ErrorMessagesWithResultTrait;

    protected
        $row,
        $formGenerator,
        $generator_array = [],
        $data = [],
        $multiple_like_radio_checkbox_select = false,
        $options = [
        'query',
        'sql',
        'row',
        'rows'
    ];

    public function __construct(FormGeneratorDirector $formGenerator, array $generator_array)
    {
        $this->generator_array = $generator_array;
        $this->formGenerator = $formGenerator;
    }

    public function setRow(): void
    {
        if ($this->isFrom()) {
            $from = $this->generator_array['data']['from'];
            $this->execute($from);
        } elseif ($this->isFromDbTable()) {
            $this->execute('db_table');
        } else {
            $this->detectFrom();
        }
    }

    /**
     * @param $from
     * @author selcukmart
     * 5.02.2022
     * 13:50
     */
    private function execute($from): void
    {
        $from = __NAMESPACE__ . '\FormDataProviders\\' . Classes::prepareFromString($from);
        $class = $from::getInstance($this->formGenerator);
        if ($this->isMultipleLikeRadioCheckboxSelect()) {
            $this->row = $class->execute4multiple($this->generator_array);
        } else {
            $this->row = $class->execute($this->generator_array);
        }
    }

    public function getOptionsSettings()
    {
        if (isset($this->generator_array['data']['settings'])) {
            return $this->generator_array['data']['settings'];
        }
    }

    /**
     * @return mixed
     */
    public function getRow()
    {
        return $this->row;
    }


    /**
     * @return bool
     * @author selcukmart
     * 5.02.2022
     * 14:10
     */
    private function isFromDbTable(): bool
    {
        return isset($this->generator_array['data']['id'], $this->generator_array['data']['table']) && !empty($this->generator_array['data']['id']) && !empty($this->generator_array['data']['table']);
    }

    /**
     * @return bool
     * @author selcukmart
     * 5.02.2022
     * 14:10
     */
    private function isFrom(): bool
    {
        return isset($this->generator_array['data']['from']) && $this->generator_array['data']['from'] !== 'db';
    }

    private function detectFrom(): void
    {
        foreach ($this->options as $option) {
            if (isset($this->generator_array['data'][$option])) {
                $this->execute($option);
                break;
            }
        }
    }

    /**
     * @param bool $multiple_like_radio_checkbox_select
     */
    public function setMultipleLikeRadioCheckboxSelect(bool $multiple_like_radio_checkbox_select): void
    {
        $this->multiple_like_radio_checkbox_select = $multiple_like_radio_checkbox_select;
    }

    /**
     * @return bool
     */
    public function isMultipleLikeRadioCheckboxSelect(): bool
    {
        return $this->multiple_like_radio_checkbox_select;
    }

    public function __destruct()
    {

    }
}