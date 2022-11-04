<?php
require __DIR__ . '/../vendor/autoload.php';

use ChrisIdakwo\GoogleTrends\Search\Psr7\Search;
use ChrisIdakwo\GoogleTrends\Search\InterestByRegionSearch;
use ChrisIdakwo\GoogleTrends\Search\InterestOverTimeSearch;
use ChrisIdakwo\GoogleTrends\Search\RelatedTopicsSearch;
use ChrisIdakwo\GoogleTrends\Search\RelatedQueriesSearch;
use ChrisIdakwo\GoogleTrends\Search\SearchInterface;
use GuzzleHttp\Psr7\ServerRequest;

function getUri(): string
{
    $uri = trim($_SERVER['REQUEST_URI'], '/');

    if (strpos($uri, '?') === false) {
        return $uri;
    }

    return substr(
        $uri,
        0,
        strpos(
            $uri,
            '?'
        )
    );
}

function getSearchByUri($uri): SearchInterface
{
    if ($uri === 'search-related-queries') {
        return new RelatedQueriesSearch();
    }

    if ($uri === 'search-related-topics') {
        return new RelatedTopicsSearch();
    }

    if ($uri === 'search-interest-over-time') {
        return new InterestOverTimeSearch();
    }

    if ($uri === 'search-interest-by-region') {
        return new InterestByRegionSearch();
    }
}

$httpSearch = new Search(getSearchByUri(getUri()));

header('Content-Type', 'application/json');

echo (string)$httpSearch->search(ServerRequest::fromGlobals())->getBody();
