<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Corexis\Concerns\BelongsToTenant;

class TenantModel extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
}
