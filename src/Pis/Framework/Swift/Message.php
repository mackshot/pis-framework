<?php

namespace Pis\Framework\Swift;

class Message extends \Swift_Message
{

    protected $content;
    protected $salutation;
    protected $farewell;
    protected $signature;

    public function setContent($content) {
        $this->content = $content;
    }

    public function getContent() {
        return $this->content;
    }

    public function setSalutation($salutation) {
        $this->content = $salutation;
    }

    public function getSalutation() {
        return $this->salutation;
    }

    public function setFarewell($farewell) {
        $this->farewell = $farewell;
    }

    public function getFarewell() {
        return $this->farewell;
    }

    public function setSignature($signature) {
        $this->signature = $signature;
    }

    public function getSignature() {
        return $this->signature;
    }


    public function generateBody() {
        return $this->getSalutation() . "\n\n" .
            $this->getContent() . "\n\n" .
            $this->getFarewell() . "\n\n--\n" .
            $this->getSignature();
    }

}
