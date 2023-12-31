<?php

namespace App\Profile\Domains\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
  use HasFactory;
  protected $factory = \Database\Factories\UsersFactory::class;

  protected $table = 'users';
  protected $primaryKey = 'id';
  public $incrementing = false;

  protected $keyType = 'string';

  protected $fillable = ['id', 'nickname', 'profile_image', 'bio'];
}

?>
