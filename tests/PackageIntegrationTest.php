<?php

declare(strict_types=1);

namespace NimbusCMS\Seo\Tests;

use Nimbus\Plugin\PluginCapabilities;
use Nimbus\Plugin\PluginDiagnostic;
use Nimbus\Plugin\PluginLoader;
use Nimbus\Site\HeadContributorRegistry;
use Nimbus\Site\PageContext;
use NimbusCMS\Seo\SeoPlugin;
use PHPUnit\Framework\TestCase;

/**
 * Proves the *package boundary*, not the JSON-LD.
 *
 * JsonLdContributorTest checks the markup for a given page. This checks
 * something different and easy to get wrong: that a real Composer installation
 * of this package is discovered by Nimbus's own loader, using this package's
 * real manifest, and registers its head contributor without anyone editing core.
 *
 * Everything here is the genuine article — the installed `composer.json`, the
 * real `PluginLoader`, the real `HeadContributorRegistry`. Only the path to
 * `installed.json` is synthesised, because Composer writes that file about the
 * *root* project and this package is the root when its own tests run.
 */
final class PackageIntegrationTest extends TestCase
{
    private string $installedJson;

    protected function setUp(): void
    {
        $this->installedJson = tempnam(sys_get_temp_dir(), 'nb-installed-') ?: '';
    }

    protected function tearDown(): void
    {
        @unlink($this->installedJson);
    }

    /** @return array<string,mixed> this package's actual composer manifest */
    private function manifest(): array
    {
        $manifest = json_decode((string) file_get_contents(__DIR__ . '/../composer.json'), true);
        self::assertIsArray($manifest);

        return $manifest;
    }

    /** An installed.json describing this package exactly as Composer would. */
    private function installedAs(): string
    {
        $manifest = $this->manifest();
        file_put_contents($this->installedJson, json_encode([
            'packages' => [[
                'name'  => $manifest['name'],
                'type'  => $manifest['type'],
                'extra' => $manifest['extra'],
            ]],
        ], JSON_THROW_ON_ERROR));

        return $this->installedJson;
    }

    private function home(): PageContext
    {
        return new PageContext('home', 'https://example.test/', 'Home', 'My Site', 'AAAAAAAAAAAAAAAAAAAAAA==');
    }

    // ------------------------------------------------------- the manifest

    public function test_the_package_declares_nimbus_as_a_runtime_dependency(): void
    {
        $manifest = $this->manifest();

        self::assertArrayHasKey('nimbuscms/nimbus', $manifest['require']);
        self::assertArrayNotHasKey('nimbuscms/nimbus', $manifest['require-dev'] ?? []);
    }

    public function test_the_package_is_typed_as_a_nimbus_plugin(): void
    {
        self::assertSame('nimbuscms-plugin', $this->manifest()['type']);
    }

    // -------------------------------------------------- discovery to registry

    public function test_composer_discovery_registers_the_head_contributor(): void
    {
        $head        = new HeadContributorRegistry();
        $loader      = new PluginLoader($this->installedAs());
        $diagnostics = $loader->load(new PluginCapabilities(head: $head));

        self::assertSame([], $diagnostics, 'a correctly installed package must load cleanly');
        self::assertSame([SeoPlugin::ID => $this->manifest()['name']], $loader->registered());

        // The contributor is live: rendering a page context yields JSON-LD.
        $out = $head->render($this->home());
        self::assertStringContainsString('application/ld+json', $out);
        self::assertStringContainsString('"@type":"WebSite"', $out);
    }

    // ---------------------------------------------------------- disabling

    public function test_disabling_the_package_registers_no_contributor(): void
    {
        $head        = new HeadContributorRegistry();
        $loader      = new PluginLoader($this->installedAs(), [SeoPlugin::ID => false]);
        $diagnostics = $loader->load(new PluginCapabilities(head: $head));

        self::assertSame([], $loader->registered());
        self::assertSame('', $head->render($this->home()), 'a disabled plugin contributes nothing');
        self::assertCount(1, $diagnostics);
        self::assertSame(PluginDiagnostic::DISABLED, $diagnostics[0]->reason);
        self::assertFalse($diagnostics[0]->isFailure(), 'disabled is a choice, not a fault');
    }
}
