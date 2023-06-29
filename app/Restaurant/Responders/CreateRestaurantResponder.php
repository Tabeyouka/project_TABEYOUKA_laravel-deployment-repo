<?php

namespace App\Restaurant\Responders;

class CreateRestaurantResponder
{
  public function respond($result)
  {
    return response()->json($result);
  }
}