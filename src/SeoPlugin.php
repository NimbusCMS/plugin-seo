<?php

declare(strict_types=1);

namespace NimbusCMS\Seo;

use Nimbus\Plugin\Plugin;
use Nimbus\Plugin\PluginContext;

/**
 * The official SEO plugin — and the reference implementation of the head
 * contribution capability (Nimbus ADR 0004).
 *
 * Deliberately small: it registers head contributors that emit schema.org
 * JSON-LD and a `<meta name="generator">` for public pages. No routes, no admin
 * UI, no migrations. It exists to prove that a plugin can enrich the rendered
 * <head> using only the data-only PageContext core hands it — never a repository
 * or the database.
 */
final class SeoPlugin implements Plugin
{
    /** Matches extra.nimbus.id in composer.json. */
    public const ID = 'nimbuscms.seo';

    public function register(PluginContext $context): void
    {
        $context->head()->register(new JsonLdContributor());
        $context->head()->register(new GeneratorContributor());
    }
}
