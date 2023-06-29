<?php

namespace App\Review\Actions;

use Illuminate\Http\Request;
use App\Review\Domain\CreateReviewDomain;

class CreateReviewAction
{
  protected $domain;

  public function __construct(CreateReviewDomain $domain)
  {
    $this->domain = $domain;
  }

  public function __invoke(Request $request)
  {
    $review = $request->only([
      'content',
      'score',
      'restaurant_id',
      'user_id',
    ]);

    return $this->domain->createReview($review);
  }

}