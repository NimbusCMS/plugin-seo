<?php

declare(strict_types=1);

namespace NimbusCMS\Seo\Tests;

use Nimbus\Site\PageContext;
use NimbusCMS\Seo\GeneratorContributor;
use PHPUnit\Framework\TestCase;

final class GeneratorContributorTest extends TestCase
{
    private GeneratorContributor $contributor;

    protected function setUp(): void
    {
        $this->contributor = new GeneratorContributor();
    }

    public function test_it_emits_a_versionless_generator_meta_on_every_page_kind(): void
    {
        foreach (['home', 'entry', 'collection'] as $kind) {
            $html = $this->contributor->head(new PageContext($kind, 'https://example.test/', 'Title', 'My Site', 'AAAAAAAAAAAAAAAAAAAAAA=='));
            self::assertSame('<meta name="generator" content="NimbusCMS">', $html);
        }
    }

    public function test_the_tag_is_constant_and_carries_no_version(): void
    {
        $html = $this->contributor->head(new PageContext('home', 'https://example.test/', 'Home', 'My Site', 'AAAAAAAAAAAAAAAAAAAAAA=='));
        // No version string, and no digit that could be one, ever leaks.
        self::assertDoesNotMatchRegularExpression('/\d/', $html);
    }

    public function test_a_hostile_title_or_site_name_cannot_affect_the_static_tag(): void
    {
        // The tag is constants-only, so no PageContext value reaches it — an
        // injection payload in the title or site name is simply never rendered.
        $html = $this->contributor->head(new PageContext(
            'entry',
            'https://example.test/x',
            '"><script>alert(1)</script>',
            '</head><script>evil()</script>',
            'AAAAAAAAAAAAAAAAAAAAAA==',
        ));
        self::assertSame('<meta name="generator" content="NimbusCMS">', $html);
        self::assertStringNotContainsString('<script>', $html);
    }
}
