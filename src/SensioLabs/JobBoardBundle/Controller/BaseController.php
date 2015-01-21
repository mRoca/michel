<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $data = array();
        $page = max(1, intval($this->get('request')->query->get('page'))) - 1;

        $em = $this->getDoctrine()->getManager();

        $data['jobs'] = $em->getRepository('SensioLabsJobBoardBundle:Job')->findBy(array(), array('createdAt' => 'desc'), 10, 10 * $page);

        if ($request->isXmlHttpRequest()) {
            return $this->render('SensioLabsJobBoardBundle:Includes:job_container.html.twig', $data);
        }

        return $data;
    }

    /**
     * @Route("/manage", name="manage")
     * @Template()
     */
    public function manageAction()
    {
        return array();
    }
}
