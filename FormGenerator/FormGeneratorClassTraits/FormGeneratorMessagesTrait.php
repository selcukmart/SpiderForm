<?php
/**
 * @author selcukmart
 * 8.02.2022
 * 10:15
 */

namespace FormGenerator\FormGeneratorClassTraits;

use GlobalTraits\ResultsTrait;

trait FormGeneratorMessagesTrait
{
    use ResultsTrait;

    protected
        $error_message = '',
        $message = '';

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->error_message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @param string $error_message
     */
    public function setErrorMessage(string $error_message): void
    {
        $this->setResult(false);
        $error_message = ' - ' . $error_message . '<br>';
        $this->error_message .= $error_message;
    }
}