<?php

declare(strict_types=1);

namespace ChrisIdakwo\GoogleTrends\Result;

/**
 * @author Gabriel Felipe Soares <gabrielfs7@gmail.com>
 */
class ExploreResult
{
    public const ID_INTEREST_OVER_TIME = 'TIMESERIES';
    public const ID_INTEREST_BY_REGION = 'GEO_MAP';
    public const ID_RELATED_TOPICS = 'RELATED_TOPICS';
    public const ID_RELATED_QUERIES = 'RELATED_QUERIES';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $token;

    public function __construct(string $id, string $token)
    {
        $this->id = $id;
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function isRelatedQueriesSearch(): bool
    {
        return self::ID_RELATED_QUERIES === $this->id;
    }

    public function isRelatedTopicsSearch(): bool
    {
        return self::ID_RELATED_TOPICS === $this->id;
    }

    public function isInterestOverTimeSearch(): bool
    {
        return self::ID_INTEREST_OVER_TIME === $this->id;
    }

    public function isInterestByRegionSearch(): bool
    {
        return self::ID_INTEREST_BY_REGION === $this->id;
    }
}
