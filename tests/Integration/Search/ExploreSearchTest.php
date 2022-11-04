<?php

declare(strict_types=1);

namespace ChrisIdakwo\GoogleTrends\Tests\Integration\Search;

use DateTimeImmutable;
use ChrisIdakwo\GoogleTrends\Error\GoogleTrendsException;
use ChrisIdakwo\GoogleTrends\Result\ExploreResult;
use ChrisIdakwo\GoogleTrends\Result\ExploreResultCollection;
use ChrisIdakwo\GoogleTrends\Search\ExploreSearch;
use ChrisIdakwo\GoogleTrends\Search\SearchFilter;
use ChrisIdakwo\GoogleTrends\Search\SearchRequest;
use PHPUnit\Framework\TestCase;

class ExploreSearchTest extends TestCase
{
    /**
     * @var SearchRequest
     */
    private $searchRequest;

    /**
     * @var SearchFilter
     */
    private $searchFilter;

    /**
     * @var ExploreSearch
     */
    private $sut;

    public function setUp(): void
    {
        $this->searchFilter = (new SearchFilter(new DateTimeImmutable('2010-10-10 00:00:00')))
            ->withSearchTerm('_keyword_');
        $this->searchRequest = $this->createMock(SearchRequest::class);
        $this->sut = new ExploreSearch($this->searchRequest);
    }

    public function testSearchWillReturnResult(): void
    {
        $relatedQueriesResult = new ExploreResult('RELATED_QUERIES', 'TOKEN');
        $relatedTopicsResult = new ExploreResult('RELATED_TOPICS', 'TOKEN');

        $collection = new ExploreResultCollection($relatedQueriesResult, $relatedTopicsResult);

        $this->searchRequest
            ->expects($this->once())
            ->method('search')
            ->with('https://trends.google.com/trends/api/explore?hl=en-US&tz=-120&req=%7B%22comparisonItem%22:%5B%7B%22geo%22:%22US%22,%22time%22:%222010-09-10+2010-10-10%22,%22keyword%22:%22_keyword_%22%7D%5D,%22category%22:0,%22property%22:%22%22%7D')
            ->willReturn(
                [
                    'widgets' => [
                        [
                            'id' => 'RELATED_QUERIES',
                            'token' => 'TOKEN',
                        ],
                        [
                            'id' => 'RELATED_TOPICS',
                            'token' => 'TOKEN',
                        ]
                    ]
                ]
            );

        $this->assertEquals(
            $collection,
            $this->sut->search($this->searchFilter)
        );
        $this->assertEquals(
            $collection->getRelatedQueriesResult(),
            $relatedQueriesResult
        );
        $this->assertEquals(
            $collection->getRelatedTopicsResult(),
            $relatedTopicsResult
        );
    }

    public function testSearchWillThrowExceptionWhenInvalidContentIsReturned(): void
    {
        $this->searchRequest
            ->expects($this->once())
            ->method('search')
            ->willReturn(
                [
                    'widgets' => [
                        [
                            'a' => 'RELATED_QUERIES'
                        ]
                    ]
                ]
            );

        $this->expectException(GoogleTrendsException::class);
        $this->expectExceptionMessage('GoogleTrends error: Missing request data for explore search. Got a');

        $this->sut->search($this->searchFilter);
    }
}
