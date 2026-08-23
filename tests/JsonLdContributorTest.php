<?php

declare(strict_types=1);

namespace NimbusCMS\Seo\Tests;

use Nimbus\Site\PageContext;
use NimbusCMS\Seo\JsonLdContributor;
use PHPUnit\Framework\TestCase;

final class JsonLdContributorTest extends TestCase
{
    private JsonLdContributor $contributor;

    protected function setUp(): void
    {
        $this->contributor = new JsonLdContributor();
    }

    public function test_an_entry_is_an_article(): void
    {
        $page = new PageContext(
            'entry',
            'https://example.test/posts/hello',
            'Hello World',
            'My Site',
            'AAAAAAAAAAAAAAAAAAAAAA==',
            ['title' => 'Hello World', 'published_at' => '2026-08-01T09:00:00+00:00'],
        );

        $html = $this->contributor->head($page);

        self::assertStringStartsWith('<script type="application/ld+json">', $html);
        self::assertStringContainsString('"@type":"Article"', $html);
        self::assertStringContainsString('"headline":"Hello World"', $html);
        self::assertStringContainsString('"url":"https://example.test/posts/hello"', $html);
        self::assertStringContainsString('"datePublished":"2026-08-01T09:00:00+00:00"', $html);
        self::assertStringContainsString('"name":"My Site"', $html, 'the publisher name');
    }

    public function test_the_home_page_is_a_website(): void
    {
        $html = $this->contributor->head(new PageContext('home', 'https://example.test/', 'Home', 'My Site', 'AAAAAAAAAAAAAAAAAAAAAA=='));

        self::assertStringContainsString('"@type":"WebSite"', $html);
        self::assertStringContainsString('"name":"My Site"', $html);
        self::assertStringContainsString('"url":"https://example.test/"', $html);
    }

    public function test_a_collection_page_is_a_collection_page(): void
    {
        $html = $this->contributor->head(new PageContext('collection', 'https://example.test/posts', 'Posts', 'My Site', 'AAAAAAAAAAAAAAAAAAAAAA=='));

        self::assertStringContainsString('"@type":"CollectionPage"', $html);
        self::assertStringContainsString('"name":"Posts"', $html);
    }

    public function test_a_missing_publish_time_is_omitted(): void
    {
        $page = new PageContext('entry', 'https://example.test/posts/x', 'X', 'My Site', 'AAAAAAAAAAAAAAAAAAAAAA==', ['title' => 'X']);

        self::assertStringNotContainsString('datePublished', $this->contributor->head($page));
    }

    public function test_it_cannot_break_out_of_the_script_element(): void
    {
        $page = new PageContext(
            'entry',
            'https://example.test/x',
            'Pwned</script><script>alert(1)</script>',
            'My Site',
            'AAAAAAAAAAAAAAAAAAAAAA==',
            ['title' => 'x'],
        );

        $html = $this->contributor->head($page);

        // The only literal </script> is the real closing tag; the title's angle
        // brackets are hex-escaped (< / >), so it cannot terminate the
        // element early.
        self::assertSame(1, substr_count($html, '</script>'));
        self::assertStringContainsString('<', $html, 'the title\'s < is hex-escaped in the JSON');
        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_an_unknown_page_kind_contributes_nothing(): void
    {
        self::assertSame('', $this->contributor->head(new PageContext('mystery', 'https://example.test/', 'X', 'S', 'AAAAAAAAAAAAAAAAAAAAAA==')));
    }
}
