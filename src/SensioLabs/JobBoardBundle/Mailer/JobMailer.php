<?php


namespace SensioLabs\JobBoardBundle\Mailer;

use SensioLabs\JobBoardBundle\Entity\Job;

class JobMailer extends Mailer
{
    /**
     * Sends an email to the admin email defined in config file, to alert a published job update.
     *
     * @param Job $job
     * @param Job $oldJob
     * @return bool|int
     * @throws \Exception
     * @throws \Twig_Error
     */
    public function jobUpdate(Job $job, Job $oldJob)
    {
        if (!$this->adminEmail) {
            return false;
        }

        $message = $this->newMessage('Job announcement update')
            ->setTo($this->adminEmail)
            ->setBody($this->templating->render(
                '@SensioLabsJobBoard/Mail/updateNotification.html.twig',
                array(
                    'job'    => $job,
                    'oldjob' => $oldJob
                )
            ));

        return $this->send($message);
    }
}
