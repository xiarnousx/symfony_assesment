<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Tag
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TagRepository")
 * @UniqueEntity("name")
 */
class Tag
{
    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)    
     *  
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\Collection|Product[]
     * 
     * @ORM\ManyToMany(targetEntity="Product", mappedBy="tags", fetch="EXTRA_LAZY")
     *
     */
    private $products;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Tag
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param Product $product
     */
    public function addProduct(Product $product)
    {
        if (is_null($this->products) || $this->products->contains($product)) {
            return;
        }

        $this->products->add($product);
        $product->addTag($this);
    }

    /**
     *
     * @param Product $product
     */
    public function removeProduct(Product $product)
    {
        if (!$this->products->contains($product)) {
            return;
        }

        $this->products->removeElement($product);
        $product->removeTag($this);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection|Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }
}
