<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Search\Actions\SearchRestaurantsAction;
use App\Search\Actions\FindNearbyRestaurantsAction;
use App\Recommendation\Actions\RecommendRestaurantsAction;
use App\Search\Responders\SearchRestaurantsResponder;
use App\Search\Responders\FindNearbyRestaurantsResponder;
use App\Recommendation\Responders\RecommendRestaurantsResponder;
use App\Services\RecruitApiService;
use App\Review\Actions\CreateReviewAction;
use App\Restaurant\Actions\CreateRestaurantAction;

/**
 * 가게 검색 기능(장르, 대형 지역, 중형 지역, 가게명를 선택하여 검색 가능)
 */
Route::get('/search', function (Request $request, SearchRestaurantsAction $action, SearchRestaurantsResponder $responder) {
  $genre = $request->input('genre');
  $large_area = $request->input('large_area');
  $middle_area = $request->input('middle_area');
  $keyword = $request->input('name');

  $result = $action($genre, $large_area, $middle_area, $keyword);
  return $responder($result);
});

/**
 * 사용자 위치 기반 가게 검색 기능
 */
Route::get('/search/nby', function (Request $request, FindNearbyRestaurantsAction $action, FindNearbyRestaurantsResponder $responder) {
  $lat = floatval($request->input('lat'));
  $lng = floatval($request->input('lng'));

  $range = 5; // 기본 검색 범위 (5km)

  $result = $action($lat, $lng, $range);
  return $responder($result);
});

/**
 * 사용자 성향에 따른 가게 추천 기능
 */
Route::get('/recommend/{user_id}', function (string $user_id, RecruitApiService $recruitApiService, RecommendRestaurantsAction $action, RecommendRestaurantsResponder $responder) {
  $restaurants = $action($user_id, $recruitApiService);
  return $responder($restaurants);
});
// TODO: 사용자 성향에 따른 가게 추천

/**
 * 가게 리뷰 작성 기능
 */
Route::post('/review', CreateReviewAction::class);

/**
 * 가게 등록 기능
 */
Route::post('/restaurant', CreateRestaurantAction::class);