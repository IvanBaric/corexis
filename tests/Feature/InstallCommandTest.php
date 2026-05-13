<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use Illuminate\Support\Facades\File;
use IvanBaric\Corexis\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    public function test_install_command_publishes_config(): void
    {
        File::delete(config_path('corexis.php'));

        $this->artisan('corexis:install')->assertSuccessful();

        $this->assertFileExists(config_path('corexis.php'));
    }

    public function test_install_command_with_velora_generates_current_team_resolver(): void
    {
        $path = app_path('Support/Tenancy/CurrentTeamResolver.php');
        File::delete($path);

        $this->artisan('corexis:install', [
            '--velora' => true,
            '--force' => true,
        ])->assertSuccessful();

        $this->assertFileExists($path);
        $this->assertStringContainsString('current_team_id()', File::get($path));
        $this->assertStringContainsString('team()', File::get($path));
    }

    public function test_install_command_without_force_does_not_overwrite_existing_file(): void
    {
        $path = app_path('Support/Tenancy/CurrentTeamResolver.php');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, 'original');

        $this->artisan('corexis:install', [
            '--velora' => true,
        ])->assertSuccessful();

        $this->assertSame('original', File::get($path));
    }

    public function test_install_command_with_force_overwrites_existing_file(): void
    {
        $path = app_path('Support/Tenancy/NullAppTenantResolver.php');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, 'original');

        $this->artisan('corexis:install', [
            '--force' => true,
        ])->assertSuccessful();

        $this->assertStringContainsString('final class NullAppTenantResolver', File::get($path));
    }
}
