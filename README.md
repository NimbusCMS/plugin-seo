# NimbusCMS SEO

Structured data for [NimbusCMS](https://github.com/NimbusCMS/nimbus) public
pages — an **official plugin**, and the reference implementation of the
head-contribution capability ([ADR 0004](https://github.com/NimbusCMS/nimbus/blob/main/docs/adr/0004-plugin-head-contributions.md)).

Nimbus core already renders per-page `<title>`, `<meta description>`, canonical
and Open Graph tags, plus `sitemap.xml` and `robots.txt`. This plugin adds the
**opinionated** layer on top: schema.org **JSON-LD**, so search engines can read
your content as structured data.

## What it does

Install it and every public page gains a `<script type="application/ld+json">`
block, built entirely from the page data Nimbus hands the plugin:

| Page | schema.org type |
|------|-----------------|
| an entry | `Article` (headline, url, `datePublished`, publisher) |
| the home page | `WebSite` |
| a collection index | `CollectionPage` |

No configuration. No database access — the plugin only ever sees the prepared
view-model of the page being rendered. Output is encoded so a value containing
`</script>` can never break out of the script element.

## Install

```bash
composer require nimbuscms/seo
```

That is all — Nimbus discovers the plugin through Composer and registers it. To
turn it off without uninstalling, list it in `config/plugins.php`:

```php
return [
    'nimbuscms.seo' => false,
];
```

## Requirements

- PHP 8.2+
- NimbusCMS (this plugin depends on the head-contribution capability, ADR 0004)

## License

MIT.
