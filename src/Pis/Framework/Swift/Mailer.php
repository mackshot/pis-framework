<?php

namespace Pis\Framework\Swift;

use Pis\Framework\Exception\MailReceiverMissingException;

class Mailer
{

    /** @var \Swift_Transport  */
    private $transport;
    /** @var \Swift_Mailer  */
    private $mailer;
    /** @var \Swift_Message */
    private $message;
    /** @var array */
    private $receiverData;

    public function __construct(\Swift_Transport $transport) {
        $this->transport = $transport;
        $this->mailer = \Swift_Mailer::newInstance($this->transport);
        $this->receiverData = array();
    }

    public function getMailer() {
        return $this->mailer;
    }

    public function SetMessage(\Swift_Message $message) {
        $this->message = $message;
    }

    public function SetReceiver($email, $username, $data = array()) {
        $rData = array(
            '{email}' => $email,
            '{username}' => $username
        );
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $rData['{'.$key.'}'] = $value;
            }
        }
        $this->receiverData[$email] = $rData;
    }

    public function Send() {
        $plugin = new \Swift_Plugins_DecoratorPlugin($this->receiverData);
        $this->mailer->registerPlugin($plugin);
        if (empty($this->receiverData))
            throw new MailReceiverMissingException();
        foreach ($this->receiverData as $data) {
            $this->message->setTo($data['{email}'], $data['{username}']);
            $this->mailer->send($this->message);
        }
    }

}