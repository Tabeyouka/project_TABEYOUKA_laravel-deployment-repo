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

    /**
     * APIから応答を取得するメソッド
     * @param array $params API呼び出しに必要なパラメータ
     * @return array|null API応答結果
     * @throws \UnexpectedValueException APIから結果を取得できない場合発生
     */
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
     * 緯度と経度を検証する方法
     * @param float|null $lat 緯度
     * @param float|null $lng 経度
     * @throws \InvalidArgumentException 緯度または経度が有効範囲を外れた場合に発生
     */
    private function validateLatitudeAndLongitude(?float $lat = null, ?float $lng = null)
    {
        if ($lat !== null && ($lat < -90 || $lat > 90)) {
            throw new \InvalidArgumentException("Latitude value must be between -90 and 90");
        }
        if ($lng !== null && ($lng < -180 || $lng > 180)) {
            throw new \InvalidArgumentException("Longitude value must be between -180 and 180");
        }
    }

    /**
     * APIの呼び出しに必要なパラメータを準備するメソッド
     * @param array $options API呼び出しに必要なパラメータ
     * @return array API呼び出しに必要なパラメータ
     * @throws \InvalidArgumentException パラメータが無効な場合発生
     */
    private function prepareParams(array $options)
    {
        $params = [
            "key" => env("HOTPEPPER_KEY"),
            "format" => "json",
        ];

        return array_merge($params, $options);
    }

    /**
     * 地域コードに基づくレストラン検索メソッド
     * ジャンル、大地域、中地域、緯度、経度、検索キーワード選択可能
     * @param string|null $genre
     * @param string|null $area
     * @param float|null $lat
     * @param float|null $lng
     * @param string|null $keyword
     * @param int|null $start
     * @param int|null $count
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
        $this->validateLatitudeAndLongitude($lat, $lng);

        $options = $this->createOptionsArray($genre, $area, $lat, $lng, $keyword, $start, $count);

        $params = $this->prepareParams($options);

        $params = $this->handleAreaCode($params, $area);

        $this->validateParams($params);

        $results = $this->handleApiCall($params, $start, $count);

        return $results;
    }

    private function createOptionsArray($genre, $area, $lat, $lng, $keyword, $start, $count)
    {
        return [
            "genre" => $genre,
            "area" => $area,
            "lat" => $lat,
            "lng" => $lng,
            "keyword" => $keyword,
            "start" => $start,
            "count" => $count,
        ];
    }

    private function handleAreaCode(array $params, ?string $area)
    {
        if ($area !== null) {
            if (strpos($area, "Z0") !== false) {
                $params["large_area"] = $area;
            } else {
                $params["middle_area"] = $area;
            }
        }

        return $params;
    }

    private function validateParams(array $params)
    {
        $input_keys = array_keys($params);
        foreach ($input_keys as $key) {
            if (empty($params[$key])) {
                throw new \InvalidArgumentException($key . " parameter cannot be empty");
            }
        }
    }

    private function handleApiCall(array $params, int $start, int $count)
    {
        $allResults = [];
        $totalCount = 0;

        do {
            $response = $this->getResponseFromApi($params);

            if ($response === null) {
                throw new \UnexpectedValueException("Failed to retrieve results from API");
            }

            $totalCount = $response["results_available"];
            $allResults = array_merge($allResults, $response["shop"]);
            $count -= count($response["shop"]);
            $start += count($response["shop"]);

            $params["start"] = $start;
        } while ($count > 0 && $start <= $totalCount);

        return [
            "results_available" => $totalCount,
            "results" => $allResults,
        ];
    }

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
     * ユーザーの位置情報を基にした周辺飲食店検索メソッド
     * 緯度、経度に基づき、ユーザー周辺の飲食店検索が可能
     * 検索キーワード選択可能
     * @param float $latitude
     * @param float $longitude
     * @param float $range
     * @param string|null $keyword
     * @return array
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
            "order" => 4,
        ];

        $results = $this->getResponseFromApi($params);

        if ($results === null) {
            throw new \UnexpectedValueException("Failed to retrieve results from API");
        }

        return $results;
    }

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