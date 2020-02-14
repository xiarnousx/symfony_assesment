<?php

namespace AppBundle\Repository\Contract;

interface SqlProductSearchable
{
    public function searchByTags(array $tags);
}
