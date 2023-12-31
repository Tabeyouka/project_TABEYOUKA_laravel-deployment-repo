<?php

namespace App\Services;

use GuzzleHttp\Client;

class RecruitApiService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            "base_uri" => "https://webservice.recruit.co.jp/hotpepper/",
        ]);
    }

    private function getResponseFromApi(array $params)
    {
        $response = $this->client->get("gourmet/v1", [
            "query" => $params,
        ]);

        if ($response->getStatusCode() === 200) {
            return json_decode($response->getBody(), true)["results"];
        }

        return null;
    }

    /**
     * 지역 코드를 기반으로 한 식당 검색 메서드
     * - 장르, 대형 지역, 중형 지역, 위도, 경도, 검색 키워드 선택 가능
     */
    public function searchRestaurantsByLocationCode(
        ?string $genre = null,
        ?string $area = null,
        ?float $lat = null,
        ?float $lng = null,
        ?string $keyword = null,
        ?int $start = 1,
        ?int $count = 10,
    ) {
        $params = [
            "key" => env("HOTPEPPER_KEY"),
            "format" => "json",
        ];

        if ($genre !== null) {
            $params["genre"] = $genre;
        }
        if ($area !== null) {
            if (strpos($area, "Z0") !== false) {
                $params["large_area"] = $area;
            } else {
                $params["middle_area"] = $area;
            }            
        }
        if ($lat !== null) {
            ($lat < -90 || $lat > 90) ? throw new \InvalidArgumentException("Latitude value must be between -90 and 90") : $params["lat"] = $lat;
        }
        if ($lng !== null) {
            ($lng < -180 || $lng > 180) ? throw new \InvalidArgumentException("Longitude value must be between -180 and 180") : $params["lng"] = $lng;
        }
        if ($keyword !== null) {
            $params["keyword"] = $keyword;
        }
        if ($start !== 1) {
            $params["start"] = $start;
        }
        if ($count !== 10) {
            $params["count"] = $count;
        }

        $input_keys = array_keys($params);
        foreach ($input_keys as $key) {
            if (empty($params[$key])) {
                throw new \InvalidArgumentException($key . " parameter cannot be empty");
            }
        }

        $allResults = [];
        $totalCount = 0;

        do {
            $response = $this->client->get("gourmet/v1/", [
                "query" => $params,
            ]);

            if ($response->getStatusCode() === 200) {
                $results = json_decode($response->getBody(), true)["results"];

                if ($count === 100) {
                    $results["results_available"] = $results["results_available"];
                } else {
                    $results["results_available"] = min($count, $results["results_available"]);
                }

                $totalCount = $results["results_available"];
                $allResults = array_merge($allResults, $results["shop"]);
                $count -= count($results["shop"]);
                $start += count($results["shop"]);

                $params["start"] = $start;
            } else {
                throw new \GuzzleHttp\Exception\GuzzleException("Request failed");
            }
        } while ($count > 0 && $start <= $totalCount);

        return [
            "results_available" => $totalCount,
            "results" => $allResults,
        ];
    }

    /**
     * 가게명으로 검색 메서드
     * - 입력된 가게명을 사용하여 검색 결과 반환
     */
    public function searchRestaurantsByName(string $name)
    {
        $params = [
            "key" => env("HOTPEPPER_KEY"),
            "keyword" => $name,
            "format" => "json",
        ];

        if (empty($name)) {
            throw new \InvalidArgumentException("name cannot be empty");
        }

        try {
            $results = $this->getResponseFromApi($params);

            if ($results === null) {
                throw new \UnexpectedValueException("Failed to retrieve results from API");
            }

            return $results;
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            return response()->json(
                [
                    "message" => "Request failed",
                    "error" => $e->getMessage(),
                ],
                400
            );
        }
    }

    /**
     * 사용자의 위치를 기반으로 한 주변 식당 검색 메서드
     * - 위도, 경도에 기반하여 사용자 주변의 식당 검색이 가능
     * - 검색 키워드 선택 가능
     */
    public function searchRestaurantsByUserLocation(
        float $latitude,
        float $longitude,
        float $range,
        ?string $keyword = null
    ) {
        $params = [
            "key" => env("HOTPEPPER_KEY"),
            "lat" => $latitude,
            "lng" => $longitude,
            "range" => $range,
            "order" => 4,
            "format" => "json",
        ];

        if ($keyword !== null) {
            $params["keyword"] = $keyword;
        }

        if ($latitude < -90 || $latitude > 90) {
            throw new \InvalidArgumentException("Latitude value must be between -90 and 90");
        }
        if ($longitude < -180 || $longitude > 180) {
            throw new \InvalidArgumentException("Longitude value must be between -180 and 180");
        }
        if ($range <= 0) {
            throw new \InvalidArgumentException("Range value must be greater than 0");
        }

        $results = $this->getResponseFromApi($params);

        if ($results === null) {
            throw new \UnexpectedValueException("Failed to retrieve results from API");
        }

        return $results;
    }

    /**
     * 일치하는 장르의 인기 있는 가게를 가져오는 메서드
     */
    public function getPopularRestaurantsByGenre(?string $genre = null)
    {
        if ($genre === null) {
            $genreArray = range(1, 17);
            $randomGenre = $genreArray[array_rand($genreArray)]; // 랜덤한 장르 코드 선택
            $genre = "G" . str_pad($randomGenre, 3, "0", STR_PAD_LEFT);
        }

        $params = [
            "key" => env("HOTPEPPER_KEY"),
            "format" => "json",
            "genre" => $genre,
            "order" => 4, // 인기순 정렬
        ];

        $results = $this->getResponseFromApi($params);

        if ($results === null) {
            throw new \UnexpectedValueException("Failed to retrieve results from API");
        }

        return $results;
    }

    /**
     * 가게 아이디로 가게 정보를 가져오는 메서드
     */
    public function getRestaurantById(string $id)
    {
        $params = [
            "key" => env("HOTPEPPER_KEY"),
            "id" => $id,
            "format" => "json",
        ];

        $results = $this->getResponseFromApi($params);

        if ($results === null) {
            throw new \UnexpectedValueException("Failed to retrieve results from API");
        }

        return $results;
    }
}
