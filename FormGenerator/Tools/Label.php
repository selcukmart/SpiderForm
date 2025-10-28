<?php
/**
 * @author selcukmart
 * 27.01.2021
 * 22:37
 */

namespace FormGenerator\Tools;


class Label
{
    private
        $label = '',
        $label_conf,
        $found_label = false,
        $translate = true,
        $label_with_help,
        $label_without_help,
        $already_set_label = true;

    private $name2label = [
    ];

    public function __construct(array $label_conf)
    {

        if (isset($label_conf['translate'])) {
            $this->translate = $label_conf['translate'];
        }

        $this->label_conf = $label_conf;

        if (isset($this->label_conf['label']) && is_string($this->label_conf['label']) && !empty($this->label_conf['label'])) {
            $this->label = $this->label_conf['label'];
            $this->setFoundLabel(true);

            return;
        }

        if (!$this->isFoundLabel()) {
            $this->detectLabel();

            if (!$this->isFoundLabel() && isset($this->label_conf['label']) && is_array($this->label_conf['label']) && isset($this->label_conf['label']['callback']) && is_callable($this->label_conf['label']['callback'])) {
                $this->processCallback();
            }
        }
    }

    public function detectLabel()
    {
        $has_label = isset($this->label_conf['attributes']['name'], $this->name2label[$this->label_conf['attributes']['name']]);

        if ($has_label) {
            $this->setFoundLabel(true);
            $this->label = $this->name2label[$this->label_conf['attributes']['name']];
            return $this->label;
        }

        if (!isset($this->label_conf['attributes']['name'])) {
            return false;
        }

        $x = explode('_', $this->label_conf['attributes']['name']);

        foreach ($x as  $item) {

            $this->label .= mb_ucfirst($item, 'UTF-8') . ' ';
        }

        $this->label = trim($this->label);

        if (!empty($this->label)) {
            $this->setFoundLabel(true);
        }

        return $this->label;
    }

    public function processCallback()
    {
        $this->label = call_user_func_array($this->label_conf['label']['callback'], [$this->label_conf['label']]);

        if (is_string($this->label) && !empty($this->label)) {
            $this->setFoundLabel(true);
        }
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        $return = '';
        if ($this->isFoundLabel()) {
            $return = $this->label;
        } elseif (isset($this->label_conf['attributes']['name'])) {
            $return = $this->label_conf['attributes']['name'];
        } elseif ($this->already_set_label) {
            $return = 'Untitled';
        }

        $this->label_without_help = $return;
        $this->label_with_help = $this->label_without_help;

        return $this->label_with_help;
    }


    /**
     * @return mixed
     */
    public function getLabelWithoutHelp()
    {
        return $this->label_without_help;
    }

    /**
     * @param bool $translate
     */
    public function setTranslate(bool $translate): void
    {
        $this->translate = $translate;
    }

    /**
     * @param bool $found_label
     */
    private function setFoundLabel(bool $found_label): void
    {
        $this->found_label = $found_label;
    }

    /**
     * @return bool
     */
    public function isFoundLabel(): bool
    {
        return $this->found_label;
    }

    /**
     * @return mixed
     */
    public function getLabelWithHelp()
    {
        return $this->label_with_help;
    }

    /**
     * @param bool $already_set_label
     */
    public function setAlreadySetLabel(bool $already_set_label): void
    {
        $this->already_set_label = $already_set_label;
    }

    public function __toString(): string
    {
        return $this->getLabel();
    }

    public function __destruct()
    {

    }
}