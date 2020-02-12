<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tag;
use AppBundle\Exception\ValidationException;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class TagsController extends Controller
{
    use ControllerTrait;

    /**
     * @Rest\View()
     */
    public function getTagsAction()
    {
        $tags = $this->getDoctrine()->getRepository('AppBundle:Tag')->findAll();

        return $tags;
    }

    /**
     * @Rest\View(statusCode=201)
     * @ParamConverter("tag", converter="fos_rest.request_body")
     * @Rest\NoRoute() 
     */
    public function postTagsAction(Tag $tag, ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            throw new ValidationException($validationErrors);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($tag);
        $entityManager->flush();
    }

    /**
     * @Rest\View()
     */
    public function getTagAction(?Tag $tag)
    {
        if (null === $tag) {
            return $this->view(null, 404);
        }

        return $tag;
    }
}
