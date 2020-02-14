<?php

namespace AppBundle\Controller;

use AppBundle\Entity\EntityMerger;
use AppBundle\Entity\Tag;
use AppBundle\Exception\ValidationException;
use AppBundle\Util\Pagination;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Controller\Annotations as Rest;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class TagsController extends Controller
{
    /**
     * 
     * @var EntityMerger
     */
    protected $merger;

    /**
     *
     * @var Pagination
     */
    protected $paginator;

    use ControllerTrait;

    /**
     *      
     * @param EntityMerger $merger
     */
    public function __construct(EntityMerger $merger, Pagination $paginator)
    {
        $this->merger = $merger;
        $this->paginator = $paginator;
    }

    /**
     * @Rest\View()
     */
    public function getTagsAction(Request $request)
    {
        $paginated = $this->paginator->paginate(
            $request,
            'AppBundle:Tag',
            'get_tags',
            [],
            []
        );

        return $paginated;
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
     * 
     * @ParamConverter("patched", converter="fos_rest.request_body",
     * options={"validator" = {"groups" = {"patch"}}}
     * )
     * @Rest\NoRoute() 
     */
    public function patchTagsAction(?Tag $tag, Tag $patched, ConstraintViolationListInterface $validationErrors)
    {
        if (null === $tag) {
            return $this->view(null, 404);
        }

        if (count($validationErrors) > 0) {
            throw new ValidationException($validationErrors);
        }

        $this->merger->merge($tag, $patched);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($tag);
        $entityManager->flush();

        return $tag;
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
