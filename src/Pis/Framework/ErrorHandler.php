<?php

namespace Pis\Framework;

use Pis\Framework\Swift\Message;
use Whoops\Run;

class ErrorHandler
{

    public Run $run;

    static $lastErrorMail = 0;

    protected $send;
    protected $mailer;
    protected $sender;
    protected $receiverMail;
    protected $receiverName;

    public function __construct($send, Swift\Mailer $mailer, $sender, $receiverMail, $receiverName) {
        $this->send = $send;
        $this->mailer = $mailer;
        $this->sender = $sender;
        $this->receiverMail = $receiverMail;
        $this->receiverName = $receiverName;

        $this->run = new Run();
        $this->run->writeToOutput($this->send);
        $this->run->allowQuit($this->send);
    }

    public function handle($exception) {
        $output = $this->run->handleException($exception);
        if (!$this->send && self::$lastErrorMail < time() - 100)
            $this->sendMail($output);
    }

    public function sendMail($output) {
        self::$lastErrorMail = time();
        $message = new Message();
        $message->setSubject('An error occured');
        $message->setBody('HTML Message');
        $message->addPart($output, 'text/html');
        $message->setSender($this->sender);
        $this->mailer->SetReceiver($this->receiverMail, $this->receiverName);
        $this->mailer->SetMessage($message);
        $this->mailer->Send();
    }

}
