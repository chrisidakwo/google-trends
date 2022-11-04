# Google Trends PHP API

[![Total Downloads](https://img.shields.io/packagist/dt/gabrielfs7/google-trends?server=https%3A%2F%2Fpackagist.org)](https://packagist.org/packages/gabrielfs7/google-trends)
[![Latest Stable Version](https://img.shields.io/packagist/v/gabrielfs7/google-trends.svg?style=flat-square)](https://packagist.org/packages/gabrielfs7/google-trends)
![Branch master](https://img.shields.io/badge/branch-master-brightgreen.svg?style=flat-square)

An easier way to search on Google Trends and get a standard response in JSON or PHP DTO.

## Dependencies

- PHP 8.0+
- PHP ext-json

## Advantages on using this API

- Get standard response that can be easily imported to your BI system.
- No need to have a Google account.
- No need for web scraping data from Google Trends UI.
- We deal with Google request token handling for you.
- Allows you to create custom reports that better fit to your business. 

## Current support

- PSR7 Support
- Related topics Search.
- Related queries Search.
- Interest over time Search.
- Interest by region Search.
- Search by categories.
- Search by location.
- Language support.
- Includes top or rising metrics.
- Search type:
  - Web
  - Image
  - News
  - Youtube
  - Google Shopping



## Usage

### Using Open API and PSR7

The library provides PSR7 http message support. Check [\ChrisIdakwo\GoogleTrends\Search\Psr7\Search](./src/Search/Psr7/Search.php).

Example:

```php
<?php
use ChrisIdakwo\GoogleTrends\Search\Psr7\Search;
use ChrisIdakwo\GoogleTrends\Search\RelatedQueriesSearch;
use GuzzleHttp\Psr7\ServerRequest;

$search = new RelatedQueriesSearch();
// $search = new RelatedTopicsSearch();
// $search = new InterestOverTimeSearch();
// $search = new InterestByRegionSearch();

echo (string)(new Search($search))->search(ServerRequest::fromGlobals())->getBody();
```

You can check all Open API specs [here](./doc/openapi.yml). 

![Open_API_Specs](./misc/open-api.png)

And you can view the open api using [swagger editor](https://editor.swagger.io/) with (File -> Import URL -> Select [openapi.yaml](./doc/openapi.yml) URL).

![Swagger_Editor_Instructions](./misc/swagger-editor-instructions.png)

If you prefer your own implementation, please use the steps bellow:

### 1) Create a `SearchFilter` with your restrictions

```php
$searchFilter = (new ChrisIdakwo\GoogleTrends\Search\SearchFilter())
        ->withCategory(0) //All categories
        ->withSearchTerm('google')
        ->withLocation('US')
        ->withinInterval(
            new DateTimeImmutable('now -7 days'),
            new DateTimeImmutable('now')
        )
        ->withLanguage('en-US')
        ->considerWebSearch()
        # ->considerImageSearch() // Consider only image search
        # ->considerNewsSearch() // Consider only news search
        # ->considerYoutubeSearch() // Consider only youtube search
        # ->considerGoogleShoppingSearch() // Consider only Google Shopping search
        ->withTopMetrics()
        ->withRisingMetrics();
```

### 2) Execute the search you wish to

#### Related Queries

```php
$result = (new ChrisIdakwo\GoogleTrends\Search\RelatedQueriesSearch())
    ->search($searchFilter)
    ->jsonSerialize();
```

JSON response example:

```json
{  
   "searchUrl":"http://www.google.com/trends/...",
   "totalResults":2,
   "results":[  
      {  
         "term":"hair salon",
         "value":100,
         "hasData": true,
         "searchUrl":"http://trends.google.com/...",
         "metricType":"TOP"
      },
      {  
         "term":"short hair",
         "value":85,
         "hasData": true,
         "searchUrl":"http://trends.google.com/...",
         "metricType":"RISING"
      }
   ]
}
```
#### Related Topics

```php
$result = (new ChrisIdakwo\GoogleTrends\Search\RelatedTopicsSearch())
    ->search($searchFilter)
    ->jsonSerialize();
```

JSON response example:

```json
{  
   "searchUrl":"http://www.google.com/trends/...",
   "totalResults":2,
   "results":[  
      {  
         "term":"Google Search - Topic",
         "value":100,
         "hasData": true,
         "searchUrl":"http://trends.google.com/...",
         "metricType":"TOP"
      },
      {  
         "term":"Google - Technology company",
         "value":85,
         "hasData": true,
         "searchUrl":"http://trends.google.com/...",
         "metricType":"RISING"
      }
   ]
}
```
#### Interest Over Time

```php
$result = (new ChrisIdakwo\GoogleTrends\Search\InterestOverTimeSearch())
            ->search($relatedSearchUrlBuilder)
            ->jsonSerialize();
```

JSON response example:

```json
{  
   "searchUrl":"http://www.google.com/trends/...",
   "totalResults":2,
   "results":[  
      {
            "interestAt": "2020-03-21T00:00:00+00:00",
            "values": [
              58
            ],
            "firstValue": 58,
            "hasData": true
      },
      {
        "interestAt": "2020-03-22T00:00:00+00:00",
        "values": [
          57
        ],
        "firstValue": 57,
        "hasData": true
      }
   ]
}
```
#### Interest By Region

```php
$result = (new ChrisIdakwo\GoogleTrends\Search\InterestByRegionSearch())
            ->search($relatedSearchUrlBuilder)
            ->jsonSerialize();
```

JSON response example:

```json
{  
   "searchUrl":"http://www.google.com/trends/...",
   "totalResults":2,
   "results":[  
      {
        "geoCode": "US-RI",
        "geoName": "Rhode Island",
        "value": 100,
        "maxValueIndex": 0,
        "hasData": true
      },
      {
        "geoCode": "US-NY",
        "geoName": "New York",
        "value": 80,
        "maxValueIndex": 0,
        "hasData": true
      }
   ]
}
```

## Installation

The Project is available on [Packagist](https://packagist.org/packages/gabrielfs7/google-trends) and you can install it using [Composer](http://getcomposer.org/):

```shell script
composer require gabrielfs7/google-trends
```

### Testing

Start PHP local server:

```shell script
php -S localhost:8000 web/index.php
```

Access the api endpoints:

- http://localhost:8000/search-interest-by-region?withTopMetrics=1&withRisingMetrics=1&searchTerm=carros&location=BR
- http://localhost:8000/search-interest-over-time?withTopMetrics=1&withRisingMetrics=1&searchTerm=carros&location=BR
- http://localhost:8000/search-related-topics?withTopMetrics=1&withRisingMetrics=1&searchTerm=carros&location=BR
- http://localhost:8000/search-related-queries?withTopMetrics=1&withRisingMetrics=1&searchTerm=carros&location=BR

## Example

After install it you can access an working example [here](/web/index.php).

## Google Categories

You can find the categories available on Google [here](/misc/categories.json).

## Contributing

I am really happy you can help with this project. If you are interest on how to contribute, please check [this page](./CONTRIBUTING.md).
