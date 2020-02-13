<?php

namespace AppBundle\Controller;

use AppBundle\Entity\EntityMerger;
use AppBundle\Entity\Product;
use AppBundle\Entity\Tag;
use AppBundle\Exception\ValidationException;
use AppBundle\Repository\ProductRepository;
use finfo;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductsController extends AbstractController
{
    /**
     * 
     * @var EntityMerger
     */
    protected $merger;

    /**
     * 
     * @var string
     */
    protected $imageDirectory;

    /**
     * 
     * @var string
     */
    protected $imageBaseUrl;


    use ControllerTrait;

    /**
     *      
     * @param EntityMerger $merger
     */
    public function __construct(
        EntityMerger $merger,
        string $imageDirectory,
        string $imageBaseUrl
    ) {
        $this->merger = $merger;
        $this->imageDirectory = $imageDirectory;
        $this->imageBaseUrl = $imageBaseUrl;
    }

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
     * @ParamConverter("product", converter="fos_rest.request_body",
     *  options={"deserializationContext"={"groups"={"deserialize"}}}
     * )
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

        return $this->view($product, Response::HTTP_CREATED)->setHeader(
            'Location',
            $this->generateUrl(
                'products_image_upload_put',
                ['product' => $product->getId()]
            )
        );
    }

    /**
     * 
     * @Rest\NoRoute()
     */
    public function putProductImageUploadAction(?Product $product, Request $request)
    {
        if (null === $product) {
            return $this->view(null, 404);
        }

        // Read Image from request body
        $content = $request->getContent();

        $tempFile = tmpfile();

        $tempFilePath = stream_get_meta_data($tempFile)['uri'];

        file_put_contents($tempFilePath, $content);

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $tempFilePath);

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($mimeType, $allowedTypes)) {
            throw new UnsupportedMediaTypeHttpException('Uploaded File is not valid png/jpg/gif image');
        }

        $fileExtensionFinder = ExtensionGuesser::getInstance();

        $newImage = md5(uniqid()) . '.' . $fileExtensionFinder->guess($mimeType);

        copy($tempFilePath, $this->imageDirectory . DIRECTORY_SEPARATOR . $newImage);

        $product->setImage($this->imageBaseUrl . $newImage);
        $this->persistImage($product);

        return new Response(null, Response::HTTP_OK);
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
     *
     * @Rest\NoRoute()
     * @ParamConverter("patched", converter="fos_rest.request_body", 
     *  options={"validator" = {"groups" = {"patch"}}}
     * )
     */
    public function patchProductAction(?Product $product, Product $patched, ConstraintViolationListInterface $validationErrors)
    {
        if (null === $product) {
            return $this->view(null, 404);
        }

        if (count($validationErrors) > 0) {
            throw new ValidationException($validationErrors);
        }

        $this->merger->merge($product, $patched);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($product);
        $entityManager->flush();

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
            return $this->view(null, 400);
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

    private function persistImage(?Product $product)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($product);
        $entityManager->flush();
    }
}
