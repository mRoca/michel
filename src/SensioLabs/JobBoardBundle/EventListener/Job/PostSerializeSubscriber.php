<?php

namespace SensioLabs\JobBoardBundle\EventListener\Job;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class PostSerializeSubscriber implements EventSubscriberInterface
{
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @inheritdoc
     */
    static public function getSubscribedEvents()
    {
        return array(
            array(
                'event'  => 'serializer.post_serialize',
                'class'  => 'SensioLabs\JobBoardBundle\Entity\Job',
                'method' => 'onPostSerialize'
            ),
        );
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        $event->getVisitor()->addData('url', $this->router->generate('job_show', $event->getObject()->getUrlParameters(), true));
    }
}
