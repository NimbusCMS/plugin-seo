# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project aims
to follow [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- A `<meta name="generator" content="NimbusCMS">` on every public page — a versionless CMS-identification tag (real, widely-consumed convention). Deliberately carries no version string and no PageContext value, so it is neither a fingerprinting nor an escaping/injection surface. (Advertising the MCP/agent surface is a core `/llms.txt` concern, not per-page head markup — no agent convention consumes head-level MCP hints today.)


### Added

- Initial release: a head contributor emitting schema.org JSON-LD for public
  pages — `Article` for entries, `WebSite` for the home page, `CollectionPage`
  for collection indexes. Built on the NimbusCMS head-contribution capability
  (ADR 0004). No configuration, no database access.
