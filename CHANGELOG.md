# Changelog

## 1.2.20 - 2026-10-04

- Stable release of the free installer, without an account, licence key or activation.
- Verified signed-bundle installation on Moodle 4.5 and 5.2, including dependencies and integrity checks.
- Clarified that renaming a signed bundle produces a filename warning without invalidating its signature.

## 1.2.16-beta - 2026-09-02

- Accepted ordinary Marketplace and repository ZIP roots such as
  `moodle-format_flexsections-5.0.4` while deriving the canonical install directory from the component declared in
  `version.php`, matching Moodle's own plugin installation path.
- Kept declared `$plugin->supported` conflicts distinct from structural and Moodle-Core validation errors so the
  private service edition can explicitly waive only that compatibility declaration without bypassing other checks.

## 1.2.3-beta - 2026-08-27

- Established the Frankenstyle-inspired `bundle_` prefix for multi-plugin bundle ZIPs.
- Signed 108design format-2 downloads now require a lower-case filename matching
  `bundle_<name>-<version>.zip`; unsigned administrator format-1 bundles remain backward-compatible without it.
- Added bilingual, non-blocking feedback for renamed signed bundles, administrator and normative documentation, schema
  guidance and regression coverage for the filename contract.

## 1.1.0 - 2026-08-24

- Enforced the intended installer-page order even though Moodle alphabetically
  sorts the direct children of the Plugins administration category.
- Added upload of one outer bundle ZIP containing `bundle.json` and up to 50
  ordinary Moodle plugin ZIPs directly at the ZIP root.
- Added strict manifest, archive layout, SHA-256, file list, and expected
  component validation before ordinary package preflight begins.
- Added an administrator documentation page, normative Markdown specification,
  bundled JSON Schema, and PHPUnit manifest coverage.
- Preserved the existing multiple individual ZIP upload workflow.

## 1.0.1 - 2026-08-24

- Positioned Bulk installation directly after Install plugins and before
  Plugin overview in the plugin administration menu.

## 1.0.0 - 2026-08-24

- Added multiple Moodle plugin ZIP upload.
- Added safe package metadata inspection and Moodle core ZIP validation.
- Added batch-wide duplicate and dependency checks.
- Added install, update, same-version replacement, and downgrade classification.
- Added SHA-256 staging integrity verification and upload safety limits.
- Added one-step handoff to Moodle's standard multi-plugin deployment helper.
- Added audit events, privacy declaration, and expired staging cleanup task.
- Added English and German interfaces, PHPUnit tests, Behat navigation coverage,
  and administrator documentation.
