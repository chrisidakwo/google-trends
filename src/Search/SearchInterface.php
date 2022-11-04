<?php

declare(strict_types=1);

namespace ChrisIdakwo\GoogleTrends\Search;

use ChrisIdakwo\GoogleTrends\Error\GoogleTrendsException;
use ChrisIdakwo\GoogleTrends\Result\AbstractResultCollection;
use ChrisIdakwo\GoogleTrends\Result\ResultCollectionInterface;

/**
 * @author Gabriel Felipe Soares <gabrielfs7@gmail.com>
 */
interface SearchInterface
{
    /**
     * @param SearchFilter $searchFilter
     *
     * @return AbstractResultCollection
     *
     * @throws GoogleTrendsException
     */
    public function search(SearchFilter $searchFilter): ResultCollectionInterface;
}
