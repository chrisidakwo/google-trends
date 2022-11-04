<?php

declare(strict_types=1);

namespace ChrisIdakwo\GoogleTrends\Search;

use ChrisIdakwo\GoogleTrends\Error\GoogleTrendsException;
use ChrisIdakwo\GoogleTrends\Result\ExploreResult;
use ChrisIdakwo\GoogleTrends\Result\ExploreResultCollection;

/**
 * @author Gabriel Felipe Soares <gabrielfs7@gmail.com>
 */
class ExploreSearch
{
    private const EXPLORE_URL = 'https://trends.google.com/trends/api/explore';

    /**
     * @var SearchRequest
     */
    private $searchRequest;

    public function __construct(SearchRequest $searchRequest = null)
    {
        $this->searchRequest = $searchRequest ?: new SearchRequest();
    }

    /**
     * @param SearchFilter $searchFilter
     *
     * @return ExploreResultCollection
     *
     * @throws GoogleTrendsException
     */
    public function search(SearchFilter $searchFilter): ExploreResultCollection
    {
        $response = $this->searchRequest->search($this->buildUrl($searchFilter));

        $results = [];

        foreach ($response['widgets'] as $widget) {
            if (!isset($widget['token'], $widget['id'])) {
                throw new GoogleTrendsException(
                    sprintf(
                        'Missing request data for explore search. Got %s',
                        implode(', ', array_keys($widget))
                    )
                );
            }

            $results[] = new ExploreResult(
                $widget['id'],
                $widget['token']
            );
        }

        return new ExploreResultCollection(...$results);
    }

    private function buildUrl(SearchFilter $searchFilter): string
    {
        $request = [
            'comparisonItem' => [
                [
                    'geo' => $searchFilter->getLocation(),
                    'time' => $searchFilter->getTime()
                ]
            ],
            'category' => $searchFilter->getCategory(),
            'property' => $searchFilter->getSearchType(),
        ];

        if (!empty($searchFilter->getSearchTerm())) {
            $request['comparisonItem'][0]['keyword'] = $searchFilter->getSearchTerm();
        }

        $query = [
            'hl' => $searchFilter->getLanguage(),
            'tz' => '-120',
            'req' => json_encode($request),
        ];

        $queryString = str_replace(
            [
                '%3A',
                '%2C',
                '%2B'
            ],
            [
                ':',
                ',',
                '+',
            ],
            http_build_query($query)
        );

        return self::EXPLORE_URL . '?' . $queryString;
    }
}
