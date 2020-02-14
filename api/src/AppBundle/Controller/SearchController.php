<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\ControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;


class SearchController extends AbstractController
{
    use ControllerTrait;

    /**
     * 
     * @Rest\View()
     */
    public function getSearchAction(Request $request)
    {
        $tags = $request->get('tags', []);

        if (!is_array($tags) || count($tags) == 0) {
            return $this->view(null, 400);
        }
        $repository = $this->getDoctrine()->getRepository('AppBundle:Product');

        return $repository->searchByTags($tags);
    }
}
