<?php

namespace App\Restaurant\Domain;

use App\Models\Restaurant;
use App\Restaurant\Responders\CreateRestaurantResponder;

class CreateRestaurantDomain
{
  protected $responder;

  public function __construct(CreateRestaurantResponder $responder)
  {
    $this->responder = $responder;
  }

  public function createRestaurant(array $restaurant)
  {
    $result = new Restaurant($restaurant);

    try {
      $result->save();
    } catch (\Exception $e) {
      return $this->responder->respond(['error' => $e->getMessage()]);
    }

    return $this->responder->respond($result);
  }

}