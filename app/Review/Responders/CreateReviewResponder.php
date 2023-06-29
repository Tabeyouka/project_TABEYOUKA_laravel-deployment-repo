<?php

namespace App\Review\Responders;

class CreateReviewResponder
{
  public function respond($result)
  {
    return response()->json($result);
  }
}