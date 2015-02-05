<?php


namespace SensioLabs\JobBoardBundle\Mailer;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Translation\TranslatorInterface;

class Mailer
{

    /** @var \Swift_Mailer */
    protected $mailer;

    /** @var TwigEngine */
    protected $templating;

    /** @var TranslatorInterface */
    protected $tranlator;

    protected $fromName;
    protected $fromEmail;
    protected $adminEmail;

    public function __construct(\Swift_Mailer $mailer, TwigEngine $templating, TranslatorInterface $tranlator, $mailerFromEmail, $mailerFromName, $adminEmail = '')
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->tranlator = $tranlator;
        $this->fromEmail = $mailerFromEmail;
        $this->fromName = $mailerFromName;
        $this->adminEmail = $adminEmail;
    }

    public function newMessage($subject)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('[JOBS] ' . $subject)
            ->setFrom($this->fromEmail, $this->fromName);

        return $message;
    }

    public function send(\Swift_Message $message)
    {
        return $this->mailer->send($message);
    }
}
