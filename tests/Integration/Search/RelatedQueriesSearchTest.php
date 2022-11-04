<?php

declare(strict_types=1);

namespace ChrisIdakwo\GoogleTrends\Tests\Integration\Search;

use DateTimeImmutable;
use ChrisIdakwo\GoogleTrends\Error\GoogleTrendsException;
use ChrisIdakwo\GoogleTrends\Result\ExploreResult;
use ChrisIdakwo\GoogleTrends\Result\ExploreResultCollection;
use ChrisIdakwo\GoogleTrends\Result\RelatedResult;
use ChrisIdakwo\GoogleTrends\Result\RelatedResultCollection;
use ChrisIdakwo\GoogleTrends\Search\ExploreSearch;
use ChrisIdakwo\GoogleTrends\Search\RelatedQueriesSearch;
use ChrisIdakwo\GoogleTrends\Search\SearchFilter;
use ChrisIdakwo\GoogleTrends\Search\SearchRequest;
use PHPUnit\Framework\TestCase;

class RelatedQueriesSearchTest extends TestCase
{
    private const SEARCH_URL = 'https://trends.google.com/trends/api/widgetdata/relatedsearches?hl=en-US&tz=-120&req=%7B%22restriction%22:%7B%22geo%22:%7B%22country%22:%22US%22%7D,%22time%22:%222010-09-10+2010-10-10%22,%22originalTimeRangeForExploreUrl%22:%222010-09-10+2010-10-10%22%7D,%22keywordType%22:%22QUERY%22,%22metric%22:%5B%22TOP%22,%22RISING%22%5D,%22trendinessSettings%22:%7B%22compareTime%22:%222010-08-10+2010-09-09%22%7D,%22requestOptions%22:%7B%22property%22:%22%22,%22backend%22:%22IZG%22,%22category%22:0%7D,%22language%22:%22en%22,%22userCountryCode%22:%22US%22%7D&token=TOKEN';

    /**
     * @var SearchRequest
     */
    private $searchRequest;

    /**
     * @var ExploreSearch
     */
    private $exploreSearch;

    /**
     * @var SearchFilter
     */
    private $searchFilter;

    /**
     * @var RelatedQueriesSearch
     */
    private $sut;

    public function setUp(): void
    {
        $this->searchFilter = (new SearchFilter(new DateTimeImmutable('2010-10-10 00:00:00')))
            ->withSearchTerm('_keyword_');
        $this->searchRequest = $this->createMock(SearchRequest::class);
        $this->exploreSearch = $this->createMock(ExploreSearch::class);
        $this->sut = new RelatedQueriesSearch($this->exploreSearch, $this->searchRequest);
    }

    /**
     * @param SearchFilter    $searchFilter
     * @param array           $rawResult
     * @param RelatedResult[] $results
     *
     * @throws GoogleTrendsException
     *
     * @dataProvider searchWillReturnResultProvider
     */
    public function testSearchWillReturnResult(
        SearchFilter $searchFilter,
        array $rawResult,
        array $results
    ): void {
        $this->expectSearchFilter();
        $this->expectExploreResult($searchFilter);

        $this->searchRequest
            ->expects($this->once())
            ->method('search')
            ->with(self::SEARCH_URL)
            ->willReturn(
                [
                    'default' => [
                        'rankedList' => [
                            [
                                'rankedKeyword' => $rawResult
                            ]
                        ]
                    ]
                ]
            );

        $this->assertEquals(
            new RelatedResultCollection(self::SEARCH_URL, ...$results),
            $this->sut->search($searchFilter)
        );
    }

    public function searchWillReturnResultProvider(): array
    {
        return [
            'Searching TOP and RISING' => [
                'filter' => (new SearchFilter(new DateTimeImmutable('2010-10-10 00:00:00')))
                    ->withTopMetrics()
                    ->withRisingMetrics(),
                'rawResult' => [
                    [
                        'query' => 'term',
                        'value' => 100,
                        'link' => '/link',
                        'hasData' => true,
                        'formattedValue' => '100'
                    ],
                    [
                        'query' => 'term2',
                        'value' => 99,
                        'link' => '/link2',
                        'hasData' => true,
                        'formattedValue' => '+99%'
                    ]
                ],
                'results' => [
                    new RelatedResult(
                        'term',
                        true,
                        100,
                        'https://trends.google.com/link',
                        'TOP'
                    ),
                    new RelatedResult(
                        'term2',
                        true,
                        99,
                        'https://trends.google.com/link2',
                        'RISING'
                    )
                ]
            ],
            'Searching TOP only' => [
                'filter' => (new SearchFilter(new DateTimeImmutable('2010-10-10 00:00:00')))
                    ->withTopMetrics(),
                'rawResult' => [
                    [
                        'query' => 'term',
                        'value' => 100,
                        'link' => '/link',
                        'hasData' => true,
                        'formattedValue' => '100'
                    ],
                    [
                        'query' => 'term2',
                        'value' => 99,
                        'link' => '/link2',
                        'hasData' => true,
                        'formattedValue' => '+99%'
                    ]
                ],
                'results' => [
                    new RelatedResult(
                        'term',
                        true,
                        100,
                        'https://trends.google.com/link',
                        'TOP'
                    )
                ]
            ],
            'Searching RISING only' => [
                'filter' => (new SearchFilter(new DateTimeImmutable('2010-10-10 00:00:00')))
                    ->withRisingMetrics(),
                'rawResult' => [
                    [
                        'query' => 'term',
                        'value' => 100,
                        'link' => '/link',
                        'hasData' => true,
                        'formattedValue' => '100'
                    ],
                    [
                        'query' => 'term2',
                        'value' => 99,
                        'link' => '/link2',
                        'hasData' => true,
                        'formattedValue' => '+99%'
                    ]
                ],
                'results' => [
                    new RelatedResult(
                        'term2',
                        true,
                        99,
                        'https://trends.google.com/link2',
                        'RISING'
                    )
                ]
            ],
        ];
    }

    public function testSearchWillNoMetricWillNotReturnResult(): void
    {
        $this->searchRequest
            ->expects($this->never())
            ->method('search');

        $this->assertEmpty($this->sut->search($this->searchFilter)->getResults());
    }

    public function testSearchWillThrowExceptionWhenMissingRequiredKeys(): void
    {
        $this->expectSearchFilter();
        $this->expectExploreResult($this->searchFilter);

        $this->searchRequest
            ->expects($this->once())
            ->method('search')
            ->willReturn(
                [
                    'default' => [
                        'rankedList' => [
                            [
                                'rankedKeyword' => [
                                    [
                                        'a' => ''
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );

        $this->expectException(GoogleTrendsException::class);
        $this->expectExceptionMessage('GoogleTrends error: Google ranked list does not contain all keys. Only has: a');

        $this->sut->search($this->searchFilter);
    }

    public function testSearchWillThrowExceptionWhenInvalidResult(): void
    {
        $this->expectSearchFilter();
        $this->expectExploreResult($this->searchFilter);

        $this->searchRequest
            ->expects($this->once())
            ->method('search')
            ->willReturn(
                [
                    'a' => []
                ]
            );

        $this->expectException(GoogleTrendsException::class);
        $this->expectExceptionMessage('GoogleTrends error: Invalid google response body ""');

        $this->sut->search($this->searchFilter);
    }

    private function expectSearchFilter(): void
    {
        $this->searchFilter
            ->withTopMetrics()
            ->withRisingMetrics();
    }

    private function expectExploreResult(SearchFilter $searchFilter): void
    {
        $this->exploreSearch
            ->expects($this->any())
            ->method('search')
            ->with($searchFilter)
            ->willReturn(
                new ExploreResultCollection(
                    new ExploreResult('RELATED_QUERIES', 'TOKEN')
                )
            );
    }
}
