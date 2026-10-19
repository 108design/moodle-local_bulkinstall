<p align="center">
  <img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/branding/logo.svg" alt="Bulk Installer logo" width="125" height="125">
</p>

# Bulk Installer

Install, update and uninstall multiple Moodle plugins in guided batches.
`local_bulkinstall` adds **Site administration → Plugins → Bulk installation**
directly below Moodle's standard plugin installer.

- **Bulk installation and updates:** upload ordinary plugin ZIPs or a manifest-based
  bundle, review all packages together and hand the confirmed batch to Moodle's
  normal deployment and database upgrade process.
- **Bulk uninstallation:** filter additional plugins, review dependencies,
  subplugins and usage, then explicitly confirm removal through Moodle's native
  uninstall process. Core plugins and Bulk Installer are protected.

Both workflows offer a checked preview for excluding blockers and continuing with
a reduced selection. Review the full batch and keep a complete backup ready;
uninstallation can permanently remove plugin data and has no automatic undo.

## Screenshots

<details>
<summary>View screenshots (15)</summary>

Current English views with fictional demonstration plugins. Click a preview for the full-size screenshot.

<table>
<tr>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-01-navigation.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-01-navigation.jpg" width="300" height="73" alt="Find Bulk Installer in site administration"></a><br>
<sub>Find Bulk Installer in site administration</sub>
</td>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-02-upload-packages.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-02-upload-packages.jpg" width="242" height="160" alt="Upload several plugin ZIP packages"></a><br>
<sub>Upload several plugin ZIP packages</sub>
</td>
</tr>
<tr>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-03-bundle-review.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-03-bundle-review.jpg" width="162" height="160" alt="Review a bundle with new plugins and updates"></a><br>
<sub>Review a bundle with new plugins and updates</sub>
</td>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-04-blocked-package-filter.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-04-blocked-package-filter.jpg" width="163" height="160" alt="Filter blocked packages and inspect original diagnostics"></a><br>
<sub>Filter blocked packages and inspect original diagnostics</sub>
</td>
</tr>
<tr>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-05-reduced-install-selection.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-05-reduced-install-selection.jpg" width="160" height="160" alt="Preview exclusions and dependent packages"></a><br>
<sub>Preview exclusions and dependent packages</sub>
</td>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-06-install-handover.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-06-install-handover.jpg" width="199" height="160" alt="Review the reduced batch before handing it to Moodle"></a><br>
<sub>Review the reduced batch before handing it to Moodle</sub>
</td>
</tr>
<tr>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-07-uninstaller-plugin-list.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-07-uninstaller-plugin-list.jpg" width="161" height="160" alt="Browse additional plugins in the Uninstaller"></a><br>
<sub>Browse additional plugins in the Uninstaller</sub>
</td>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-08-uninstaller-filters-selection.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-08-uninstaller-filters-selection.jpg" width="228" height="160" alt="Filter by name and type while retaining hidden selections"></a><br>
<sub>Filter by name and type while retaining hidden selections</sub>
</td>
</tr>
<tr>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-09-uninstaller-blocked-review.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-09-uninstaller-blocked-review.jpg" width="176" height="160" alt="Review dependencies and included subplugins"></a><br>
<sub>Review dependencies and included subplugins</sub>
</td>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-10-reduced-uninstall-selection.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-10-reduced-uninstall-selection.jpg" width="179" height="160" alt="Preview and confirm excluded uninstall blockers"></a><br>
<sub>Preview and confirm excluded uninstall blockers</sub>
</td>
</tr>
<tr>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-11-uninstall-confirmation.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-11-uninstall-confirmation.jpg" width="188" height="160" alt="Confirm the full selection and possible permanent data loss"></a><br>
<sub>Confirm the full selection and possible permanent data loss</sub>
</td>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-12-uninstall-completion.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-12-uninstall-completion.jpg" width="268" height="160" alt="Finish a successful uninstall with Moodle’s completion check"></a><br>
<sub>Finish a successful uninstall with Moodle’s completion check</sub>
</td>
</tr>
<tr>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-13-last-batch-results.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-13-last-batch-results.jpg" width="300" height="125" alt="View the read-only result of the last batch"></a><br>
<sub>View the read-only result of the last batch</sub>
</td>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-14-remaining-plugin-files.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-14-remaining-plugin-files.jpg" width="242" height="160" alt="Follow up explicitly listed remaining plugin files"></a><br>
<sub>Follow up explicitly listed remaining plugin files</sub>
</td>
</tr>
<tr>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-15-bundle-format.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-local_bulkinstall/main/docs/screenshots/bulk-installer-15-bundle-format.jpg" width="204" height="160" alt="Read the bundle ZIP specification"></a><br>
<sub>Read the bundle ZIP specification</sub>
</td>
</tr>
</table>

</details>

## Requirements

- Moodle 4.5 or newer, declared through Moodle 5.3.
- Web-based plugin deployment must be enabled. The page is hidden when
  `$CFG->disableupdateautodeploy` is enabled.
- The web server must be able to write the affected plugin-type directories.
- The administrator needs `moodle/site:config`; uninstallation additionally requires
  `local/bulkinstall:uninstall` and respects CLI-only uninstall restrictions.

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
4. If packages are blocked, select **Exclude Blocking Packages** to review a
   reduced selection. Packages that would lose a required dependency are also
   excluded. Confirm **Use Reduced Selection**, then review the remaining batch.
5. When the batch passes, confirm **Hand Packages to Moodle** once.
6. Moodle deploys the code and opens its standard plugin check and database
   upgrade pages.

**Cancel** discards the prepared batch. Once packages have been handed to Moodle,
interrupting installation can leave some plugins updated and others unchanged.

## Updates from Plugin Manager

A compatible 108design Plugin Manager can download selected signed updates and
hand them directly to the same package review, without a manual ZIP upload.
Bulk Installer preserves and rechecks the signed catalogue, package hashes,
components, builds and installation state before handing the confirmed selection
to Moodle. Excluding blocked packages rechecks the remaining dependencies and
signed update selection. Finish Moodle's subsequent plugin check and database upgrade pages.
A changed installation or invalid/expired catalogue blocks the prepared batch;
return to Plugin Manager and prepare a new selection. Ordinary uploads and
bundle installations do not require Plugin Manager.

## Bulk uninstallation

The third **Uninstaller** tab lets administrators filter additional installed
plugins, select several components and review their removal together. Core
plugins are excluded and Bulk Installer cannot uninstall itself. Access requires
both site configuration permission and the separate
`local/bulkinstall:uninstall` capability; delegated roles do not receive it by default.

1. Filter by name/component, plugin type or installation status, then select
   plugins. Filters only change the display; hidden checked plugins remain selected.
2. Select **Review Selection**. Review every affected component, including
   associated subplugins, known activity/block usage, Moodle warnings and file removal.
3. If the selection is blocked, **Exclude Blocking Plugins** previews the
   exclusions and reasons. **Use Reduced Selection** keeps the remaining group;
   nothing is uninstalled at this step and dependents are never silently added.
4. Acknowledge the complete displayed selection and possible permanent data loss,
   then confirm the final uninstall. Confirmation expires after one hour and
   changes to the reviewed state require a new review.
5. In the active completion stage, remove any explicitly listed remaining plugin
   directories on the server and select **Check files again**. This check deletes
   nothing. Once the files are gone, **Open Moodle completion check** opens Moodle’s
   final inventory check and any required native steps. The historical
   **View Last Batch Result** view is a read-only receipt without completion actions.

Have a complete site backup ready. Uninstallation can permanently remove plugin
data and content. Usage counts are partial information, not proof that a plugin is
unused. Plugins are processed sequentially in dependency order; errors or manual
file follow-up stop the batch. Completed steps remain in effect, with no automatic
rollback or retry. Read-only or externally managed plugin directories require
manual removal. Server restrictions on web deployment or CLI-only uninstallation
are respected.

Temporary uninstall receipts contain component names, reviewed plugin paths and results, without
user identifiers or plugin content. Access is bound to the administrator and
originating Moodle session. They remain available to the originating session for up
to 24 hours and are removed by the existing scheduled cleanup task. Moodle audit
events record the batch start and final status.

## Bundle ZIP format

The plugin accepts one outer ZIP containing `bundle.json` and the listed ordinary Moodle plugin ZIPs directly at the
ZIP root. A bundle must be uploaded alone. The manifest declares bundle metadata plus the exact filename, expected
Moodle component, and SHA-256 digest for each nested ZIP. Unsigned format 1 remains available for administrator-created
compatibility bundles. Signed 108design downloads use format 2 for package integrity or format 3 when a resumable
post-install activation journey is declared. Signed formats use the lower-case filename convention
`bundle_<name>-<version>.zip`; format 1 may use the same convention but is not required to do so. Format 3 contains
only non-secret product, minimum-version and entitlement/component metadata; it never contains a licence key, token,
ticket or proof of ownership.

For a reduced bundle selection, subsequent setup covers only products whose
required components remain complete. If a suitable Plugin Manager is unavailable,
set up licenses separately in the installed plugins or Plugin Manager.

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

**This release is available free of charge under the 108design Software License.**

See [LICENSE.md](https://github.com/108design/moodle-local_bulkinstall/blob/main/LICENSE.md) for the full terms.
