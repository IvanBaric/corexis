<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

final class FrameworkDefaults
{
    /**
     * @var list<string>
     */
    private const REQUIRED_RULES = [
        'accepted',
        'accepted_if',
        'required',
        'required_array_keys',
        'required_if',
        'required_if_accepted',
        'required_if_declined',
        'required_unless',
        'required_with',
        'required_with_all',
        'required_without',
        'required_without_all',
    ];

    public function configure(Application $app): void
    {
        $this->configureDates();
        $this->configureModels($app);
        $this->configureDatabase($app);
        $this->configurePasswords($app);
        $this->configureValidation();
        $this->configureHttpTests($app);
    }

    private function configureDates(): void
    {
        if ((bool) config('corexis.framework.immutable_dates', true)) {
            Date::use(CarbonImmutable::class);
        }
    }

    private function configureModels(Application $app): void
    {
        $strict = config('corexis.framework.strict_models');

        Model::shouldBeStrict($strict === null
            ? ! $app->environment('production')
            : (bool) $strict);
    }

    private function configureDatabase(Application $app): void
    {
        $prohibitDestructiveCommands = config('corexis.framework.prohibit_destructive_commands');

        DB::prohibitDestructiveCommands($prohibitDestructiveCommands === null
            ? $app->environment('production')
            : (bool) $prohibitDestructiveCommands);

        $threshold = (float) config(
            'corexis.framework.cumulative_query_time_threshold_ms',
            500,
        );

        if ($threshold <= 0) {
            return;
        }

        DB::whenQueryingForLongerThan(
            $threshold,
            static function (Connection $connection, QueryExecuted $event) use ($threshold): void {
                Log::warning('Cumulative database query time exceeded the Corexis threshold.', [
                    'connection' => $connection->getName(),
                    'threshold_ms' => $threshold,
                    'total_duration_ms' => $connection->totalQueryDuration(),
                    'last_query_duration_ms' => $event->time,
                ]);
            },
        );
    }

    private function configurePasswords(Application $app): void
    {
        Password::defaults(static function () use ($app): ?Password {
            if (! (bool) config('corexis.framework.passwords.enabled', true)) {
                return null;
            }

            if (
                (bool) config('corexis.framework.passwords.production_only', true)
                && ! $app->environment('production')
            ) {
                return null;
            }

            $rule = Password::min(max(
                1,
                (int) config('corexis.framework.passwords.minimum', 8),
            ));

            if ((bool) config('corexis.framework.passwords.mixed_case', true)) {
                $rule->mixedCase();
            }

            if ((bool) config('corexis.framework.passwords.letters', true)) {
                $rule->letters();
            }

            if ((bool) config('corexis.framework.passwords.numbers', true)) {
                $rule->numbers();
            }

            if ((bool) config('corexis.framework.passwords.symbols', true)) {
                $rule->symbols();
            }

            if ((bool) config('corexis.framework.passwords.uncompromised', true)) {
                $rule->uncompromised();
            }

            return $rule;
        });
    }

    private function configureValidation(): void
    {
        $message = config(
            'corexis.framework.required_validation_message',
            'corexis::corexis.validation.required',
        );

        if (! is_string($message) || $message === '') {
            return;
        }

        foreach (self::REQUIRED_RULES as $rule) {
            Validator::replacer($rule, static fn (): string => __($message));
        }
    }

    private function configureHttpTests(Application $app): void
    {
        if (
            $app->runningUnitTests()
            && (bool) config('corexis.framework.prevent_stray_http_requests_in_tests', true)
        ) {
            Http::preventStrayRequests();
        }
    }
}
