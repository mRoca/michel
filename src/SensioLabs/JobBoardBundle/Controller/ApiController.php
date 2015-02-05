<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends Controller
{
    /**
     * @Route("/api/random", name="api_random")
     *
     * Temporary method used for views counting, waiting for US #18
     */
    public function randomAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SensioLabsJobBoardBundle:Job');

        $job = $repository->getRandom();

        if (null === $job) {
            throw $this->createNotFoundException('Random entity not found');
        }

        $job->incrementApiViews();
        $em->flush($job);

        return new JsonResponse(array(
            'id' => $job->getId()
        ));
    }
}
