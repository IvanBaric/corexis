<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Fixtures\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $guarded = [];
}
