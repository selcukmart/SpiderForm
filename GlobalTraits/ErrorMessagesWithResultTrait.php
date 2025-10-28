<?php
/**
 * @author selcukmart
 * 31.10.2021
 * 11:22
 */

namespace GlobalTraits;

trait ErrorMessagesWithResultTrait
{
    use ResultsTrait;
    private
        $error_message = '';

    /**
     * @param string $error_message
     */
    public function setErrorMessage(string $error_message): void
    {
        $this->result = false;
        $this->error_message .= $error_message . PHP_EOL;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->error_message;
    }
}