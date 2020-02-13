<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\Tag;
use AppBundle\Exception\ValidationException;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ProductsController extends AbstractController
{
    use ControllerTrait;

    /**
     * @Rest\View()
     *
     */
    public function getProductsAction()
    {
        $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findAll();

        return $products;
    }

    /**
     * @Rest\View(statusCode=201)
     * @ParamConverter("product", converter="fos_rest.request_body")
     * @Rest\NoRoute() 
     */
    public function postProductsAction(Product $product, ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            throw new ValidationException($validationErrors);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($product);
        $entityManager->flush();
    }


    /**
     * @Rest\View()
     * 
     */
    public function getProductAction(?Product $product)
    {
        if (null === $product) {
            return $this->view(null, 404);
        }

        return $product;
    }

    /**
     * @Rest\View()
     *
     */
    public function getProductTagsAction(Product $product)
    {
        return $product->getTags();
    }

    /**
     * @Rest\View(statusCode=201)
     * @Rest\NoRoute()
     * 
     */
    public function postProductTagAction(Request $request, Product $product)
    {
        $tags = $request->get("tags");

        $entityManager = $this->getDoctrine()->getManager();

        foreach ($tags as $tagId) {
            $tag = $entityManager->find(Tag::class, $tagId);
            $product->addTag($tag);
        }

        $entityManager->persist($product);
        $entityManager->flush();
    }
}
