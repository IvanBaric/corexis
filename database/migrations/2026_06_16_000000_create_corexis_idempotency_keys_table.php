<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = (string) config('corexis.idempotency.table', 'corexis_idempotency_keys');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('scope')->index();
            $table->string('operation')->index();
            $table->string('idempotency_key');
            $table->string('status')->index();
            $table->string('response_message')->nullable();
            $table->string('response_code')->nullable();
            $table->json('response_data')->nullable();
            $table->json('response_errors')->nullable();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['scope', 'operation', 'idempotency_key'], 'corexis_idempotency_scope_operation_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((string) config('corexis.idempotency.table', 'corexis_idempotency_keys'));
    }
};
