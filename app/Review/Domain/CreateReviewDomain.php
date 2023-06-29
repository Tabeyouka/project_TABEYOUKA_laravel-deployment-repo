<?php

namespace App\Review\Domain;

use App\Models\Restaurant;
use App\Restaurant\Domain\CreateRestaurantDomain;
use App\Review\Responders\CreateReviewResponder;
use App\Models\Review;

class CreateReviewDomain
{
  protected $responder;
  protected $createRestaurantDomain;

  public function __construct(CreateReviewResponder $responder, CreateRestaurantDomain $createRestaurantDomain)
  {
    $this->responder = $responder;
    $this->createRestaurantDomain = $createRestaurantDomain;
  }

  public function createReview(array $review)
  {
    if (empty($review)) {
      return $this->responder->respond(['error' => 'Review cannot be empty']);
    }

    // check if review has restaurant_id and user_id
    if (!isset($review['restaurant_id'], $review['user_id'])) {
      return $this->responder->respond(['error' => 'Review `restaurant_id` and `user_id` are required']);
    }

    $restaurant_id = $review['restaurant_id'];
    $restaurant = Restaurant::find($restaurant_id);

    // if restaurant does not exist, create it
    if (!$restaurant) {
      $this->createRestaurantDomain->createRestaurant(['id' => $restaurant_id]);
    }

    $result = new Review($review);

    $result->save();

    return $this->responder->respond($result);
  }
}