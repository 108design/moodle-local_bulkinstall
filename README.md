# Bulk plugin installation

`local_bulkinstall` adds **Site administration → Plugins → Bulk installation**
directly below Moodle's standard plugin installer. It accepts multiple ordinary
Moodle plugin ZIP packages or one manifest-based bundle ZIP, validates all
contained packages as one batch, and hands successful batches to Moodle's
normal code deployment and database upgrade flow.

## Requirements

Bulk Installer is free of charge and requires no account, licence key or activation.
All its installation features may be used on any number of your own Moodle sites
under the source-available Software License in `LICENSE.md`.
This does not make the software open source or grant redistribution rights.

- Moodle 4.5 or newer, declared through Moodle 5.2.
- Web-based plugin deployment must be enabled. The page is hidden when
  `$CFG->disableupdateautodeploy` is enabled.
- The web server must be able to write the affected plugin-type directories.
- The administrator needs `moodle/site:config`.

## Installation

1. Back up the Moodle code and database.
2. Install the current `local_bulkinstall-<version>.zip` through Moodle's standard
   **Install plugins** page, or place the included `bulkinstall` directory at
   `<moodle-dir>/local/bulkinstall`.
3. Complete Moodle's normal plugin installation screen.
4. Open **Site administration → Plugins → Bulk installation**.

Moodle 5.2 installations that use the split application layout still use the
normal `$CFG->dirroot`; no special path configuration is required.

## Using a batch

1. Select or drag multiple individual plugin ZIPs into the file manager, or
   upload exactly one bundle ZIP.
2. Select **Check packages**.
3. Review component, installed version, package version, intended action,
   warnings, and errors for every package.
4. If the whole batch passes, confirm **Install or update all packages** once.
5. Moodle deploys the code and opens its standard plugin check and database
   upgrade pages.

## Bundle ZIP format

The plugin accepts one outer ZIP containing `bundle.json` and the listed ordinary Moodle plugin ZIPs directly at the
ZIP root. A bundle must be uploaded alone. The manifest declares bundle metadata plus the exact filename, expected
Moodle component, and SHA-256 digest for each nested ZIP. Unsigned format 1 remains available for administrator-created
compatibility bundles. Signed 108design downloads use format 2 for package integrity or format 3 when a resumable
post-install activation journey is declared. Signed formats use the lower-case filename convention
`bundle_<name>-<version>.zip`; format 1 may use the same convention but is not required to do so. Format 3 contains
only non-secret product, minimum-version and entitlement/component metadata; it never contains a licence key, token,
ticket or proof of ownership.

The exact specification is available to administrators from the upload page
and is included in [`docs/bundle-format.md`](docs/bundle-format.md). The
machine-readable schemas are [`schemas/bundle-v1.schema.json`](schemas/bundle-v1.schema.json),
[`schemas/bundle-v2.schema.json`](schemas/bundle-v2.schema.json) and
[`schemas/bundle-v3.schema.json`](schemas/bundle-v3.schema.json).

## Preflight checks

The plugin checks the following before the install button becomes available:

- readable ZIP and safe archive paths;
- exactly one plugin root directory;
- direct `version.php`, valid component, and matching directory name;
- known plugin type and writable/removable target location;
- Moodle `requires` and `supported` declarations;
- installation, upgrade, same-version replacement, or downgrade status;
- duplicate components within the batch;
- statically declared plugin dependencies, including dependencies satisfied by
  another ZIP in the same batch;
- Moodle's standard plugin validation;
- SHA-256 integrity between upload, review, and final confirmation;
- for bundle uploads: strict manifest fields, root-only layout, exact file list,
  nested ZIP hashes, manifest-to-package component matching, signature verification and a warning when the recommended `bundle_`
  filename convention for signed 108design bundles is not followed;
- package count, archive-entry count, and uncompressed size limits.

Uploaded `version.php` files are tokenised and read as text. They are not
included or executed during preflight. Dynamic dependency expressions cannot be
fully resolved safely and produce a warning; Moodle performs its normal final
dependency check after deployment.

## Fixed safety limits

- 50 ZIP packages per batch.
- One bundle manifest of at most 256 KiB.
- 10,000 archive entries per package.
- 256 MiB uncompressed per package.
- 512 MiB uncompressed per batch.
- 24-hour expiry for abandoned staged batches.

PHP, web-server, and Moodle upload limits can impose lower limits.

## Important operational limitations

Moodle validates all packages before changing plugin directories. It then
deploys those directories sequentially and subsequently performs database
installation and upgrades. These operations are not one atomic filesystem and
database transaction. Moodle archives existing plugin code when updating a
removable plugin, but a complete site backup remains necessary.

A separately packaged subplugin whose type is introduced by a parent plugin in
the same batch may be unknown before that parent is installed. Install the
parent first, or distribute the subplugin in the parent package where supported.

## Temporary files and privacy

Staged ZIPs live below `moodledata/temp/local_bulkinstall` under a random
identifier owned by the current administrator session. They are deleted on
cancel, after handing the batch to Moodle, or by an hourly scheduled cleanup
after 24 hours. No personal data or package contents are stored in plugin-owned
database tables, and the plugin performs no external network requests.

Audit events record the administrator, time, component list, package count, and
whether validation passed. They do not contain ZIP data.

## License

This is a 108design source-available commercial software license, not an open-source license. See [LICENSE.md](https://github.com/108design/moodle-local_bulkinstall/blob/main/LICENSE.md) for the full terms.
