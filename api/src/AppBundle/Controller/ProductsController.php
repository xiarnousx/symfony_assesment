<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\Tag;
use AppBundle\Exception\ValidationException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Exception;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * SPECIAL NOTE:
     * **************
     * I have decided not to use converter=fos_rest.request_body, Because it requires custom Annotation for
     * the relations tags in addition it requires custom Serializer\DoctrineEntityDeserializationSubscriber.
     * However, it would make the controller method body thinner. My Decision was due that I need to understnad it
     * better and have deep investigation about it before using it.
     * **************
     * 
     * @Rest\View(statusCode=201)
     * @Rest\NoRoute()
     * 
     */
    public function postProductTagAction(Request $request, Product $product, ValidatorInterface $validator)
    {
        $tags = $request->get("tags");

        if (!is_array($tags) || 0 === count($tags)) {
            // @todo write proper exception handling for none array submited tags value.
            $tags = [null];
        }

        $entityManager = $this->getDoctrine()->getManager();

        $tagsConstraints = new Assert\All([
            'constraints' => [
                new Assert\NotNull(),
                new Assert\NotBlank(),
                new Assert\Callback(function (
                    $payload,
                    ExecutionContextInterface $context
                ) use ($entityManager) {
                    if (empty($payload)) {
                        $context->buildViolation("Invalid id value cannot be empty")
                            ->atPath('tags')
                            ->addViolation();

                        return;
                    }

                    if (!is_numeric($payload)) {
                        $context->buildViolation("Invalid integer value '{$payload}'")
                            ->atPath('tags')
                            ->addViolation();

                        return;
                    }

                    if (!$entityManager->find(Tag::class, $payload)) {
                        $context->buildViolation("Invalid tag id '{$payload}'")
                            ->atPath('tags')
                            ->addViolation();
                        return;
                    }
                }),
            ],
        ]);

        $errors = $validator->validate(
            $tags,
            $tagsConstraints
        );

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        foreach ($tags as $tagId) {
            $tag = $entityManager->find(Tag::class, $tagId);
            $product->addTag($tag);
        }

        $entityManager->persist($product);
        $entityManager->flush();
    }
}
