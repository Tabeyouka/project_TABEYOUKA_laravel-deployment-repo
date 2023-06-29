<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Restaurant;

class Review extends Model
{
  use HasFactory;

  protected $table = 'reviews';

  protected $fillable = [
    'id',
    'content',
    'score',
    'restaurant_id',
    'user_id',
    'created_at',
    'updated_at',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function restaurant()
  {
    return $this->belongsTo(Restaurant::class);
  }
}