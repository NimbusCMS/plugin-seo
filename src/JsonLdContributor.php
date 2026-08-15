<?php

declare(strict_types=1);

namespace NimbusCMS\Seo;

use Nimbus\Site\HeadContributor;
use Nimbus\Site\PageContext;

/**
 * Emits schema.org structured data as JSON-LD for public pages:
 *
 * - an entry  → `Article`
 * - the home  → `WebSite`
 * - an index  → `CollectionPage`
 *
 * Everything is built from the PageContext core provides. The JSON is encoded
 * with `<`/`>` hex-escaped, so a value containing `</script>` can never break
 * out of the script element.
 */
final class JsonLdContributor implements HeadContributor
{
    // Slashes stay unescaped so URLs read cleanly; safety against breaking out
    // of <script> comes from HEX_TAG, which escapes every `<` and `>`.
    private const FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP;

    public function head(PageContext $page): string
    {
        $graph = $this->graph($page);
        if ($graph === []) {
            return '';
        }

        $json = json_encode($graph, self::FLAGS);
        if ($json === false) {
            return '';
        }

        return '<script type="application/ld+json">' . $json . '</script>';
    }

    /**
     * The JSON-LD graph for a page, with empty values dropped so the output
     * never carries `null`s.
     *
     * @return array<string,mixed>
     */
    private function graph(PageContext $page): array
    {
        $graph = match ($page->kind) {
            'entry' => [
                '@context'         => 'https://schema.org',
                '@type'            => 'Article',
                'headline'         => $page->title,
                'url'              => $page->canonical,
                'mainEntityOfPage' => $page->canonical,
                'datePublished'    => $this->publishedAt($page->entry),
                'publisher'        => ['@type' => 'Organization', 'name' => $page->siteName],
            ],
            'collection' => [
                '@context' => 'https://schema.org',
                '@type'    => 'CollectionPage',
                'name'     => $page->title,
                'url'      => $page->canonical,
            ],
            'home' => [
                '@context' => 'https://schema.org',
                '@type'    => 'WebSite',
                'name'     => $page->siteName,
                'url'      => $page->canonical,
            ],
            default => [],
        };

        return array_filter($graph, static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * The entry's publish time, already ISO 8601 in the view-model, or null.
     *
     * @param array<string,mixed>|null $entry
     */
    private function publishedAt(?array $entry): ?string
    {
        $value = $entry['published_at'] ?? null;
        return is_string($value) && $value !== '' ? $value : null;
    }
}
