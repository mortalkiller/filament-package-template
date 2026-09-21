# Security Policy

## Supported versions

This template does not itself publish a runtime package. Packages created from it must replace this section with their supported package lines before the first stable release.

Use the latest patch release in any supported line.

## Reporting a vulnerability

Report vulnerabilities privately through the repository's GitHub Security Advisory / private vulnerability reporting flow. Do not disclose an unpatched vulnerability in a public issue or pull request.

Include:

- package, PHP, Laravel, and Filament versions;
- a minimal reproduction;
- expected behavior, actual behavior, and likely impact;
- prerequisites needed to reproduce the issue.

Use fictional data and remove credentials, tokens, session identifiers, personal information, customer details, private application names, internal URLs, and infrastructure information.

Ordinary bugs and feature requests belong in GitHub Issues.

## Response and disclosure

Reports are reviewed on a best-effort basis. Confirmed vulnerabilities should be fixed and disclosed through an appropriate patch release and GitHub Security Advisory.

## Package boundary

The consuming application remains responsible for its application-level authentication, authorization, data access, tenant resolution, deployment security, and any application-specific trust boundaries not explicitly owned by the package.
