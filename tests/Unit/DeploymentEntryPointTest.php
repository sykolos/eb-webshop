<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class DeploymentEntryPointTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = dirname(__DIR__, 2).'/storage/framework/testing/eb-deploy-test-'.bin2hex(random_bytes(8));
        mkdir($this->directory.'/public_html', 0777, true);
        copy(dirname(__DIR__, 2).'/public/index.php', $this->directory.'/public_html/index.php');
    }

    protected function tearDown(): void
    {
        $root = realpath($this->directory);
        $this->assertStringStartsWith(realpath(dirname(__DIR__, 2).'/storage/framework/testing').DIRECTORY_SEPARATOR.'eb-deploy-test-', $root);
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($root);
    }

    public function test_explicit_deployment_path_ignores_old_parent_installation(): void
    {
        $this->createApplication($this->directory, 'OLD');
        $this->createApplication($this->directory.'/myapp', 'DEPLOYED');
        $this->configure($this->directory.'/myapp');

        $result = $this->runEntryPoint();
        $this->assertSame(0, $result->getExitCode(), $result->getErrorOutput());
        $this->assertSame('DEPLOYED', $result->getOutput());
    }

    public function test_local_layout_uses_parent_when_no_deployment_configuration_exists(): void
    {
        $this->createApplication($this->directory, 'LOCAL');
        $this->assertSame('LOCAL', $this->runEntryPoint()->getOutput());
    }

    public function test_invalid_configured_path_does_not_fall_back_to_old_installation(): void
    {
        $this->createApplication($this->directory, 'OLD');
        $this->configure($this->directory.'/missing');
        $result = $this->runEntryPoint();
        $this->assertSame('Application deployment is incomplete.', $result->getOutput());
        $this->assertStringContainsString('configured application root is missing', $result->getErrorOutput());
    }

    public function test_maintenance_file_is_loaded_from_selected_application(): void
    {
        $this->createApplication($this->directory, 'OLD');
        $this->createApplication($this->directory.'/myapp', 'DEPLOYED');
        mkdir($this->directory.'/myapp/storage/framework', 0777, true);
        file_put_contents($this->directory.'/myapp/storage/framework/maintenance.php', "<?php exit('MAINTENANCE');");
        $this->configure($this->directory.'/myapp');
        $this->assertSame('MAINTENANCE', $this->runEntryPoint()->getOutput());
    }

    private function configure(string $path): void
    {
        file_put_contents($this->directory.'/public_html/.laravel-path.php', '<?php return '.var_export($path, true).';');
    }

    /** @dataProvider deploymentPaths */
    public function test_build_configuration_validates_server_layout(string $app, string $public, bool $valid): void
    {
        mkdir($this->directory.'/scripts/deploy', 0777, true);
        mkdir($this->directory.'/public');
        copy(dirname(__DIR__, 2).'/scripts/deploy/configure-path.php', $this->directory.'/scripts/deploy/configure-path.php');
        $process = new Process([PHP_BINARY, $this->directory.'/scripts/deploy/configure-path.php'], null,
            ['DEPLOY_PATH' => $app, 'PUBLIC_PATH' => $public]);
        $process->run();
        $this->assertSame($valid ? 0 : 1, $process->getExitCode(), $process->getErrorOutput());
        $generated = $this->directory.'/public/.laravel-path.php';
        if ($valid) {
            $this->assertSame(rtrim($app, '/'), require $generated);
        } else {
            $this->assertFileDoesNotExist($generated);
        }
    }

    public static function deploymentPaths(): array
    {
        return [
            'sibling directories' => ['/home/dekorhu/ebtest/myapp', '/home/dekorhu/ebtest/public_html', true],
            'nested public' => ['/home/dekorhu/ebtest/', '/home/dekorhu/ebtest/public_html/', true],
            'same directory' => ['/home/dekorhu/ebtest', '/home/dekorhu/ebtest', false],
            'app under web root' => ['/home/dekorhu/ebtest/myapp', '/home/dekorhu/ebtest', false],
            'parent traversal' => ['/home/dekorhu/ebtest/../myapp', '/home/dekorhu/ebtest/public_html', false],
            'missing secret' => ['', '/home/dekorhu/ebtest/public_html', false],
        ];
    }

    public function test_deployment_app_key_helper_preserves_valid_key(): void
    {
        $envFile = $this->directory.'/.env';
        $key = 'base64:'.base64_encode(str_repeat('a', 32));
        file_put_contents($envFile, "APP_KEY=$key\nDB_CONNECTION=mysql\n");

        $process = $this->runAppKeyHelper($envFile);

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $this->assertStringContainsString("APP_KEY=$key", file_get_contents($envFile));
    }

    public function test_deployment_app_key_helper_replaces_placeholder_key(): void
    {
        $envFile = $this->directory.'/.env';
        file_put_contents($envFile, "APP_KEY=base64:CHANGE_ME_GENERATE_ON_SERVER_OR_COPY_EXISTING_KEY\nDB_CONNECTION=mysql\n");

        $process = $this->runAppKeyHelper($envFile);

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $contents = file_get_contents($envFile);
        $this->assertStringNotContainsString('CHANGE_ME', $contents);
        $this->assertMatchesRegularExpression('/^APP_KEY=base64:[A-Za-z0-9+\/=]+\r?$/m', $contents);
    }

    private function runEntryPoint(): Process
    {
        $process = new Process([PHP_BINARY, $this->directory.'/public_html/index.php']);
        $process->run();
        return $process;
    }

    private function runAppKeyHelper(string $envFile): Process
    {
        $process = new Process([PHP_BINARY, dirname(__DIR__, 2).'/scripts/deploy/ensure-app-key.php', $envFile]);
        $process->run();
        return $process;
    }

    private function createApplication(string $path, string $label): void
    {
        mkdir($path.'/vendor', 0777, true);
        mkdir($path.'/bootstrap', 0777, true);
        file_put_contents($path.'/vendor/autoload.php', <<<'PHP'
<?php
namespace Illuminate\Http;
class Request { public static function capture() { return new self; } }
PHP
        );
        $bootstrap = <<<'PHP'
<?php
if (realpath($_ENV['APP_BASE_PATH']) !== realpath(dirname(__DIR__))) {
    throw new \RuntimeException('APP_BASE_PATH mismatch');
}
return new class {
    public function bind($key, $factory) {}
    public function make($class) { return $this; }
    public function handle($request) { return $this; }
    public function send() { echo LABEL; return $this; }
    public function terminate($request, $response) {}
};
PHP;
        file_put_contents($path.'/bootstrap/app.php', str_replace('LABEL', var_export($label, true), $bootstrap));
    }
}
