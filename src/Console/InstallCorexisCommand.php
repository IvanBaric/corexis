<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCorexisCommand extends Command
{
    protected $signature = 'corexis:install {--velora : Generate a host-app tenant resolver that bridges Velora helpers to Corexis} {--force : Overwrite existing generated resolver files}';

    protected $description = 'Publish Corexis config and generate optional host-application resolver stubs.';

    public function handle(Filesystem $files): int
    {
        $this->callSilent('vendor:publish', [
            '--tag' => 'corexis-config',
            '--force' => (bool) $this->option('force'),
        ]);

        $this->info('Published Corexis config.');

        $tenantStub = $this->option('velora')
            ? 'tenancy/CurrentTeamResolver.stub'
            : 'tenancy/NullAppTenantResolver.stub';

        $tenantPath = $this->option('velora')
            ? app_path('Support/Tenancy/CurrentTeamResolver.php')
            : app_path('Support/Tenancy/NullAppTenantResolver.php');

        $this->writeStub($files, $tenantStub, $tenantPath);
        $this->writeStub($files, 'locale/AppLocaleResolver.stub', app_path('Support/Locale/AppLocaleResolver.php'));
        $this->writeStub($files, 'actor/AuthActorResolver.stub', app_path('Support/Actor/AuthActorResolver.php'));

        $this->newLine();
        $this->line('Next steps:');
        $this->line('1. Review config/corexis.php.');

        if ($this->option('velora')) {
            $this->line('2. Set corexis.tenancy.enabled to true.');
            $this->line('3. Set corexis.tenancy.resolver to App\\Support\\Tenancy\\CurrentTeamResolver::class.');
            $this->line('4. Ensure the host application provides team() and current_team_id().');
        } else {
            $this->line('2. Keep tenancy disabled or set corexis.tenancy.resolver to your app resolver.');
            $this->line('3. Implement TenantResolver in the host app when tenancy is needed.');
        }

        return self::SUCCESS;
    }

    private function writeStub(Filesystem $files, string $stub, string $target): void
    {
        if ($files->exists($target) && ! $this->option('force')) {
            $this->warn(sprintf('Skipped existing file: %s', $target));

            return;
        }

        $files->ensureDirectoryExists(dirname($target));
        $files->put($target, $files->get(__DIR__.'/../../stubs/'.$stub));

        $this->info(sprintf('Generated: %s', $target));
    }
}
