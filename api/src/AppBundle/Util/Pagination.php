<?php

namespace AppBundle\Util;

use AppBundle\Repository\Contract\SqlCountable;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use Symfony\Component\HttpFoundation\Request;

class Pagination
{
    private const KEY_LIMIT = 'limit';
    private const KEY_PAGE = 'page';
    private const DEFAULT_LIMIT = 10;
    private const DEFAULT_PAGE = 1;

    /**
     *
     * @var Registery
     */
    private $doctrineRegistry;

    public function __construct(Registry $doctrineRegistry)
    {
        $this->doctrineRegistry = $doctrineRegistry;
    }

    public function paginate(
        Request $request,
        string $entityName,
        string $route,
        array $routeParams = [],
        array $criteria = []
    ): PaginatedRepresentation {
        $repository = $this->doctrineRegistry->getRepository($entityName);

        if (!($repository instanceof SqlCountable)) {
            throw new \InvalidArgumentException('Entity repository should implement interface SqlCountable');
        }

        $limit = $request->get(self::KEY_LIMIT, self::DEFAULT_LIMIT);
        $page = $request->get(self::KEY_PAGE, self::DEFAULT_PAGE);
        $offset = ($page - 1) * $limit;


        // @todo Add support for sorting currently null is passed.
        $resources = $repository->findBy($criteria, null, $limit, $offset);

        $rescourcesCount = $repository->total($criteria);
        $pageCount = (int) ceil($rescourcesCount / $limit);
        $collection = new CollectionRepresentation($resources);

        return new PaginatedRepresentation(
            $collection,
            $route,
            $routeParams,
            $page,
            $limit,
            $pageCount
        );
    }
}
