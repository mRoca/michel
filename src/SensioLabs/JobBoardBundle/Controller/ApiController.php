<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    /**
     * @Route("/api/random", name="api_random")
     */
    public function randomAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SensioLabsJobBoardBundle:Job');

        $job = $repository->getRandom();

        if (null === $job) {
            throw $this->createNotFoundException('Random entity not found');
        }

        $job->incrementApiViews();
        $em->flush($job);

        $serializer = $this->get('jms_serializer');

        return new Response(
            $serializer->serialize($job, 'json', SerializationContext::create()->setGroups(array('api'))),
            200,
            array('Content-Type' => 'application/json')
        );
    }
}
