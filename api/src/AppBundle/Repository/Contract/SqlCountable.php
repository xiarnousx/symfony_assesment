<?php

namespace AppBundle\Repository\Contract;

interface SqlCountable
{
    public function total(array $criteria = []): int;
}
