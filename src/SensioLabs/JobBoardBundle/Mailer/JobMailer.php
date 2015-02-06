<?php


namespace SensioLabs\JobBoardBundle\Mailer;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\User;

class JobMailer extends Mailer
{
    /**
     * Sends an email to the admin email defined in config file, to alert a published job update.
     *
     * @param Job $job
     * @param Job $oldJob
     * @return bool|int
     */
    public function jobUpdate(Job $job, Job $oldJob)
    {
        $title = $this->tranlator->trans('email.job_update.subject');
        return $this->updateNotification($job, $oldJob, $title, $this->adminEmail, false);
    }

    /**
     * Sends an email to the user when an admin publish his announcement
     *
     * @param Job $job
     * @param Job $oldJob
     * @return bool|int
     */
    public function jobPublish(Job $job, Job $oldJob)
    {
        if (!$job->getUser() instanceof User) {
            return false;
        }

        $title = $this->tranlator->trans('email.job_publish.subject');
        return $this->updateNotification($job, $oldJob, $title, $job->getUser()->getEmail());
    }

    /**
     * @param Job $job
     * @param Job $oldJob
     * @param string $subject
     * @param string $to
     * @param bool $frontendLink
     * @return bool|int
     */
    private function updateNotification(Job $job, Job $oldJob, $subject, $to, $frontendLink = true)
    {
        if (!$to) {
            return false;
        }

        $message = $this->newMessage($subject)
            ->setTo($to)
            ->setBody($this->templating->render(
                '@SensioLabsJobBoard/Mail/updateNotification.html.twig',
                array(
                    'frontend_link' => $frontendLink,
                    'job'           => $job,
                    'oldjob'        => $oldJob
                )
            ));

        return $this->send($message);
    }

}
