<?php

declare(strict_types=1);

namespace NimbusCMS\Seo;

use Nimbus\Site\HeadContributor;
use Nimbus\Site\PageContext;

/**
 * Emits a `<meta name="generator">` identifying the CMS on every public page — a
 * real, widely-consumed convention (CMS detectors, ecosystem stats).
 *
 * **Constant, and deliberately version-less.** The tag carries the fixed string
 * "NimbusCMS" and nothing else: no version (which would be fingerprinting surface
 * for no benefit) and no value from the PageContext (which would be an escaping
 * and, for agent-facing metadata, a prompt-injection sink). There is nothing to
 * escape because there is nothing dynamic to emit.
 *
 * Advertising the CMS's agent/MCP control surface is deliberately NOT done here:
 * per-page head markup has no consuming agent convention today, and the endpoint
 * is a core fact. That belongs in a core-served `/llms.txt`, beside robots.txt.
 */
final class GeneratorContributor implements HeadContributor
{
    public function head(PageContext $page): string
    {
        return '<meta name="generator" content="NimbusCMS">';
    }
}
