<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class DeploymentPackageTest extends TestCase
{
    private string $directory;
    private string $bash;

    protected function setUp(): void
    {
        $this->bash = PHP_OS_FAMILY === 'Windows' ? 'C:/Program Files/Git/bin/bash.exe' : 'bash';
        $this->directory = dirname(__DIR__, 2).'/storage/framework/testing/eb-package-test-'.bin2hex(random_bytes(8));
        foreach (['app', 'bootstrap/cache', 'config', 'database', 'lang', 'resources', 'routes', 'scripts/deploy', 'vendor', 'storage/app/public/products', 'public/build', 'public/storage', 'public/.well-known', '.codex-inspect', 'docs'] as $path) {
            mkdir($this->directory.'/'.$path, 0777, true);
        }
        copy(dirname(__DIR__, 2).'/scripts/deploy/package.sh', $this->directory.'/scripts/deploy/package.sh');
        foreach (['artisan', 'composer.json', 'composer.lock', 'app/example.php', 'vendor/autoload.php', 'public/index.php', 'public/.htaccess', 'public/build/manifest.json', '.env', '.env.develop', 'reference.zip', '.codex-inspect/source.txt', 'docs/plan.md', 'bootstrap/cache/config.php', 'public/hot', 'public/storage/private.jpg', 'public/.well-known/token'] as $file) {
            file_put_contents($this->directory.'/'.$file, 'fixture');
        }
        file_put_contents($this->directory.'/REVISION', str_repeat('a', 40));
        file_put_contents($this->directory.'/storage/app/public/products/product.webp', 'product-image');
    }

    protected function tearDown(): void
    {
        $root = realpath($this->directory);
        $this->assertStringStartsWith(realpath(dirname(__DIR__, 2).'/storage/framework/testing').DIRECTORY_SEPARATOR.'eb-package-test-', $root);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($root);
    }

    public function test_release_contains_only_runtime_and_can_be_unpacked(): void
    {
        $this->runBash('bash scripts/deploy/package.sh "$(pwd)/release"');
        $app = $this->runBash('tar -tzf release/app.tgz')->getOutput();
        foreach (['app/example.php', 'vendor/autoload.php', 'REVISION'] as $expected) {
            $this->assertStringContainsString($expected, $app);
        }
        $this->assertStringContainsString('storage/app/public/products/product.webp', $app);
        foreach (['.env', '.zip', '.codex-inspect', 'docs/', 'bootstrap/cache/config.php'] as $excluded) {
            $this->assertStringNotContainsString($excluded, $app);
        }
        $public = $this->runBash('tar -tzf release/public.tgz')->getOutput();
        $this->assertStringContainsString('./.htaccess', $public);
        $this->assertStringContainsString('./build/manifest.json', $public);
        $this->assertStringNotContainsString('./storage', $public);
        $this->assertStringNotContainsString('./hot', $public);
        $this->assertStringNotContainsString('./.well-known', $public);
        $this->runBash('cd release && sha256sum --check SHA256SUMS');
        mkdir($this->directory.'/restored');
        $this->runBash('tar -xzf release/app.tgz -C restored');
        $this->assertSame(str_repeat('a', 40), file_get_contents($this->directory.'/restored/REVISION'));
        $this->assertSame('fixture', file_get_contents($this->directory.'/restored/app/example.php'));
    }

    public function test_release_without_valid_revision_is_rejected(): void
    {
        file_put_contents($this->directory.'/REVISION', 'invalid');
        $result = $this->runBash('bash scripts/deploy/package.sh "$(pwd)/release"', false);
        $this->assertNotSame(0, $result->getExitCode());
        $this->assertFileDoesNotExist($this->directory.'/release/app.tgz');
    }

    private function runBash(string $command, bool $successful = true): Process
    {
        $process = new Process([$this->bash, '-c', $command], $this->directory);
        $process->run();
        if ($successful) {
            $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        }
        return $process;
    }
}
