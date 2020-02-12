<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


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
    public function postProductsAction(Product $product)
    {
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
}
