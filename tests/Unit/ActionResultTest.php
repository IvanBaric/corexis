<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Unit;

use IvanBaric\Corexis\Data\ActionResult;
use PHPUnit\Framework\TestCase;

class ActionResultTest extends TestCase
{
    public function test_success_result_can_be_created(): void
    {
        $result = ActionResult::success('Saved.', ['id' => 1]);

        $this->assertTrue($result->success);
        $this->assertFalse($result->failed());
        $this->assertSame('Saved.', $result->message);
        $this->assertSame(['id' => 1], $result->data);
        $this->assertNull($result->code);
    }

    public function test_error_result_can_be_created(): void
    {
        $result = ActionResult::error('Failed.', 'failed', ['field' => 'name']);

        $this->assertFalse($result->success);
        $this->assertTrue($result->failed());
        $this->assertSame('Failed.', $result->message);
        $this->assertSame('failed', $result->code);
        $this->assertSame(['field' => 'name'], $result->data);
    }
}
