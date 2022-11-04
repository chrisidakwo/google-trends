<?php

declare(strict_types=1);

namespace ChrisIdakwo\GoogleTrends\Tests\Integration\Search;

use DateTimeImmutable;
use ChrisIdakwo\GoogleTrends\Error\GoogleTrendsException;
use ChrisIdakwo\GoogleTrends\Result\ExploreResult;
use ChrisIdakwo\GoogleTrends\Result\ExploreResultCollection;
use ChrisIdakwo\GoogleTrends\Result\InterestByRegionCollection;
use ChrisIdakwo\GoogleTrends\Result\InterestByRegionResult;
use ChrisIdakwo\GoogleTrends\Search\ExploreSearch;
use ChrisIdakwo\GoogleTrends\Search\InterestByRegionSearch;
use ChrisIdakwo\GoogleTrends\Search\SearchFilter;
use ChrisIdakwo\GoogleTrends\Search\SearchRequest;
use PHPUnit\Framework\TestCase;

class InterestByRegionSearchTest extends TestCase
{
    private const CURRENT_DATE = '2010-01-01 00:00:00';
    private const SEARCH_URL = 'https://trends.google.com/trends/api/widgetdata/comparedgeo?hl=en-US&tz=-60&req=%7B%22geo%22:%7B%22country%22:%22US%22%7D,%22comparisonItem%22:%5B%7B%22time%22:%222010-09-10+2010-10-10%22,%22complexKeywordsRestriction%22:%7B%22keyword%22:%5B%7B%22type%22:%22BROAD%22,%22value%22:%22_keyword_%22%7D%5D%7D%7D%5D,%22resolution%22:%22REGION%22,%22locale%22:%22en-US%22,%22requestOptions%22:%7B%22property%22:%22%22,%22backend%22:%22IZG%22,%22category%22:0%7D%7D&token=TOKEN';

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
     * @var InterestByRegionSearch
     */
    private $sut;

    public function setUp(): void
    {
        $this->searchFilter = (new SearchFilter(new DateTimeImmutable('2010-10-10 00:00:00')))
            ->withSearchTerm('_keyword_');
        $this->searchRequest = $this->createMock(SearchRequest::class);
        $this->exploreSearch = $this->createMock(ExploreSearch::class);
        $this->sut = new InterestByRegionSearch($this->exploreSearch, $this->searchRequest);

        $this->exploreSearch
            ->expects($this->once())
            ->method('search')
            ->with($this->searchFilter)
            ->willReturn(
                new ExploreResultCollection(
                    new ExploreResult('GEO_MAP', 'TOKEN')
                )
            );
    }

    public function testSearchWillReturnResult(): void
    {
        $currentDate = new DateTimeImmutable(self::CURRENT_DATE);

        $result = new InterestByRegionResult(
            'US-NY',
            'New York',
            100,
            0,
            true
        );

        $this->searchRequest
            ->expects($this->once())
            ->method('search')
            ->with(self::SEARCH_URL)
            ->willReturn(
                [
                    'default' => [
                        'geoMapData' => [
                            [
                                'geoCode' => 'US-NY',
                                'geoName' => 'New York',
                                'value' => [
                                    100
                                ],
                                'maxValueIndex' => 0,
                                'hasData' => [
                                    true
                                ]
                            ]
                        ]
                    ]
                ]
            );

        $this->assertEquals(
            new InterestByRegionCollection(self::SEARCH_URL, $result),
            $this->sut->search($this->searchFilter)
        );
    }

    public function testSearchWillThrowExceptionWhenMissingRequiredKeys(): void
    {
        $this->searchRequest
            ->expects($this->once())
            ->method('search')
            ->with(self::SEARCH_URL)
            ->willReturn(
                [
                    'default' => [
                        'geoMapData' => [
                            [
                                'a' => '',
                            ]
                        ]
                    ]
                ]
            );

        $this->expectException(GoogleTrendsException::class);
        $this->expectExceptionMessage('Google compared geo list does not contain all keys. Only has: a');

        $this->sut->search($this->searchFilter);
    }

    public function testSearchWillThrowExceptionWhenInvalidResult(): void
    {
        $this->searchRequest
            ->expects($this->once())
            ->method('search')
            ->with(self::SEARCH_URL)
            ->willReturn(
                [
                    'a' => []
                ]
            );

        $this->expectException(GoogleTrendsException::class);
        $this->expectExceptionMessage('GoogleTrends error: Invalid google response body ""');

        $this->sut->search($this->searchFilter);
    }
}
