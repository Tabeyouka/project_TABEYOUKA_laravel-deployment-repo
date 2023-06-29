<?php

namespace App\Restaurant\Actions;

use Illuminate\Http\Request;
use App\Restaurant\Domain\CreateRestaurantDomain;

class CreateRestaurantAction
{
  protected $domain;

  public function __construct(CreateRestaurantDomain $domain)
  {
    $this->domain = $domain;
  }

  public function __invoke(Request $request)
  {
    $restaurant = $request->only([
      'id',
      'score',
    ]);

    return $this->domain->createRestaurant($restaurant);
  }
}