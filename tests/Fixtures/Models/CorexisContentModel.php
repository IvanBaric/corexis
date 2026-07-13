<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Corexis\Concerns\BelongsToTenant;
use IvanBaric\Corexis\Concerns\HasUniqueSlug;
use IvanBaric\Corexis\Concerns\HasUuid;

class CorexisContentModel extends Model
{
    use BelongsToTenant, HasUniqueSlug, HasUuid;

    protected $table = 'corexis_content_models';

    protected $guarded = [];

    public function slugSource(): string
    {
        return (string) $this->getAttribute('title');
    }
}
