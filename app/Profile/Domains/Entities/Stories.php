<?php

namespace App\Profile\Domains\Entities;

use Illuminate\Database\Eloquent\Model;

class Stories extends Model
{
  protected $table = 'stories';

  protected $fillable = ['story_list_id', 'review_id'];
}

?>