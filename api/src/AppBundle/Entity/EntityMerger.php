<?php

namespace AppBundle\Entity;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Id;

class EntityMerger
{
    /**
     * 
     * @var AnnotationReader
     */
    private $annotationReader;


    public function __construct()
    {
        $this->annotationReader = new AnnotationReader();
    }

    /**
     *
     * @param  $old
     * @param  $changes
     * @return void
     */
    public function merge($old, $changes)
    {
        $entityClassName = get_class($old);

        if (false === $entityClassName) {
            throw new \InvalidArgumentException("$old is not a class");
        }

        $changesClassName = get_class($changes);

        if (false === $changesClassName) {
            throw new \InvalidArgumentException("$changes is not a class");
        }

        if (!is_a($changes, $entityClassName)) {
            throw new \InvalidArgumentException("Can not merge entities, of different types");
        }

        $oldReflection = new \ReflectionObject($old);
        $changesReflection = new \ReflectionObject($changes);

        foreach ($changesReflection->getProperties() as $changedProperty) {
            $changedProperty->setAccessible(true);
            $changedPropertyValue = $changedProperty->getValue($changes);

            // ignore null value changes
            if (null === $changedProperty->getValue($changes)) {
                continue;
            }

            // ignore changes if not present on property
            if (!$oldReflection->hasProperty($changedProperty->getName())) {
                continue;
            }

            $oldProperty = $oldReflection->getProperty($changedProperty->getName());
            $annotation = $this->annotationReader->getPropertyAnnotation($oldProperty, Id::class);

            // ignore property with @Id annotation
            if (null !== $annotation) {
                continue;
            }

            $oldProperty->setAccessible(true);
            $oldProperty->setValue($old, $changedPropertyValue);
        }
    }
}
