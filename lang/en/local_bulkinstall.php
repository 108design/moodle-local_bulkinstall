<?php
/**
 * File documentation for lang/en/local_bulkinstall.php.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */
$string['errorupdateproof'] = 'The signed update evidence is invalid, expired, incomplete or no longer supported. Return to Plugin Manager and prepare a new selection.';
$string['errorupdateversion'] = 'The package build does not match the selected signed release.';
$string['errorupdatechanged'] = 'The installation changed after these updates were prepared. Prepare a new update selection.';
$string['errorinstallbusy'] = 'Another Bulk Installer deployment is running. Wait for it to finish before trying again.';
$string['signedupdatesnotice'] = 'These packages were downloaded from the signed update catalogue. Their hashes, components, builds and installation state are checked again before installation. Confirm once to hand over the entire batch to Moodle and complete the subsequent Moodle upgrade.';
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

$string['pluginname'] = 'Bulk plugin installation';
$string['menulabel'] = 'Bulk installation';
$string['pagetitle'] = 'Bulk plugin installation';
$string['uploadheading'] = 'Upload plugin packages';
$string['uploadintro'] = 'Select up to 50 ordinary Moodle plugin ZIP packages, or one bundle ZIP containing bundle.json and its plugin ZIPs at the ZIP root. All packages are checked together before any plugin code is changed.';
$string['backupnotice'] = 'Back up the site code and database before installing or updating a large plugin batch.';
$string['packages'] = 'Plugin ZIP packages';
$string['packages_help'] = 'Select multiple ordinary Moodle plugin ZIP packages, or exactly one bundle ZIP. A bundle cannot be combined with other uploaded files.';
$string['checkpackages'] = 'Check packages';
$string['bundleformatlink'] = 'Bundle ZIP format and bundle.json specification';
$string['bundleformattitle'] = 'Bundle ZIP format';
$string['bundleformatintro'] = 'A bundle is one outer ZIP containing bundle.json and all listed ordinary Moodle plugin ZIPs directly at the ZIP root. All supported format versions are strict and do not permit other files or directories.';
$string['bundleformatfilename'] = 'Signed 108design bundles use a Frankenstyle-inspired lower-case name such as bundle_course_tools-1.0.0.zip. The bundle_ prefix is mandatory for format 2; administrator-created format 1 bundles remain compatible without it.';
$string['bundleformatstructureheading'] = 'ZIP structure';
$string['bundleformatstructure'] = 'All entries are files at the ZIP root. File names are case-sensitive and must match bundle.json exactly.';
$string['bundleformatmanifestheading'] = 'bundle.json example';
$string['bundleformatfield'] = 'Field';
$string['bundleformattype'] = 'JSON type';
$string['bundleformatrequired'] = 'Required';
$string['bundleformatrules'] = 'Rules';
$string['bundleformatruleformat'] = 'Must equal moodle-plugin-bundle.';
$string['bundleformatruleformatversion'] = '1 is an unsigned compatibility bundle; 2 and 3 are signed 108design bundles. Version 3 adds activation metadata.';
$string['bundleformatruleactivation'] = 'Required only in signed version 3. Coordinates the Plugin Manager wizard and never grants a license.';
$string['bundleformatrulestoreproduct'] = 'Stable Storefront product identifier used to find matching purchases in the connected account.';
$string['bundleformatruleentitlements'] = 'Expected commercial or free entitlement codes and the bundled components they cover.';
$string['bundleformatruleid'] = '2–100 lower-case ASCII characters; starts with a letter, followed by letters, digits, underscore, or hyphen.';
$string['bundleformatrulename'] = 'Non-empty display name, at most 200 characters.';
$string['bundleformatruleversion'] = 'Non-empty bundle release identifier, at most 100 characters. This is bundle metadata, not a Moodle build number.';
$string['bundleformatruledescription'] = 'Optional administrator-facing text, at most 2,000 characters.';
$string['bundleformatruleplugins'] = 'Array of 1–50 plugin objects. File names and components must each be unique; file names are compared case-insensitively for uniqueness.';
$string['bundleformatrulefile'] = 'Exact root-level ZIP file name, at most 255 characters; no path or control characters.';
$string['bundleformatrulecomponent'] = 'Expected Moodle frankenstyle component, for example mod_example. It must match the component found inside the package.';
$string['bundleformatrulesha256'] = 'Exactly 64 hexadecimal characters: the SHA-256 digest of the nested ZIP file bytes.';
$string['bundleformatstrict'] = 'Only the documented properties are accepted. Every plugin ZIP in the outer archive must be listed exactly once, and every listed ZIP must exist. Uploading a bundle together with another file is rejected.';
$string['bundleformatschema'] = 'The same normative specification is included in schemas/bundle-v1.schema.json, schemas/bundle-v2.schema.json, schemas/bundle-v3.schema.json and docs/bundle-format.md inside this plugin.';
$string['backtoupload'] = 'Back to package upload';
$string['bundledetected'] = 'Detected bundle';
$string['bundlearchive'] = 'Bundle ZIP';
$string['bundleid'] = 'Bundle ID';
$string['bundlename'] = 'Bundle name';
$string['bundleversion'] = 'Bundle version';
$string['bundledescription'] = 'Description';
$string['reviewtitle'] = 'Review plugin batch';
$string['reviewintro'] = 'Review the planned changes and results before handing the batch to Moodle.';
$string['filename'] = 'File';
$string['component'] = 'Component';
$string['installedversion'] = 'Installed version';
$string['packageversion'] = 'Package version';
$string['release'] = 'Release';
$string['action'] = 'Action';
$string['validation'] = 'Validation';
$string['validationok'] = 'OK';
$string['errorprefix'] = 'Error: {$a}';
$string['warningprefix'] = 'Warning: {$a}';
$string['actioninstall'] = 'Install';
$string['actionupdate'] = 'Update';
$string['actionreinstall'] = 'Replace same version';
$string['actiondowngrade'] = 'Downgrade';
$string['actionunknown'] = 'Unknown';
$string['reviewsummary'] = '{$a->count} package(s), {$a->size} uncompressed in total.';
$string['sequentialwarning'] = 'Moodle validates all packages first, but deploys their code directories sequentially. Filesystem deployment and subsequent database upgrades are not one atomic transaction.';
$string['confirmationtext'] = 'I have reviewed the listed components and want Moodle to deploy all packages in this batch.';
$string['installbatch'] = 'Hand Packages to Moodle';
$string['discardbatch'] = 'Discard batch';
$string['batchblocked'] = 'This batch cannot be installed. Correct the errors and upload the packages again.';
$string['batchchanged'] = 'The staged batch no longer passes validation. Review it again.';
$string['installingbatch'] = 'Installing plugin batch';
$string['dependencyinbatch'] = 'package batch';
$string['dependencyinstalled'] = 'installed site';

$string['errornofiles'] = 'Select at least one ZIP package.';
$string['errortoomanypackages'] = 'A batch may contain no more than {$a} packages.';
$string['errorcreatestaging'] = 'The temporary staging directory could not be created.';
$string['errorcopystagedfile'] = 'The package “{$a}” could not be copied to temporary staging.';
$string['errorstorageexpired'] = 'This staged batch has expired or does not belong to the current session.';
$string['errorinvalidstorage'] = 'Invalid temporary storage identifier.';
$string['errorstagedfilemissing'] = 'The staged ZIP file is missing or unreadable.';
$string['errorintegritychanged'] = 'The staged ZIP has changed since it was uploaded.';
$string['errorbundlemixedupload'] = 'Upload either one bundle ZIP or ordinary plugin ZIPs. A bundle cannot be combined with other files.';
$string['errorbundleentries'] = 'A bundle may contain bundle.json and no more than {$a} plugin ZIPs.';
$string['errorbundlerootonly'] = 'Every bundle entry must be a file directly at the ZIP root. Invalid entry: {$a}';
$string['errorbundleduplicateentry'] = 'The bundle contains a duplicate or case-colliding entry: {$a}';
$string['errorbundleunexpectedfile'] = 'The bundle contains a file that is neither bundle.json nor a ZIP: {$a}';
$string['errorbundlemanifestmissing'] = 'The bundle does not contain exactly one bundle.json at its ZIP root.';
$string['errorbundlemanifestread'] = 'bundle.json could not be extracted safely or exceeds the size limit.';
$string['errorbundlenestedziplimit'] = 'The nested ZIP files exceed the combined staged-size limit of {$a}.';
$string['errorbundlefilelist'] = 'The plugin ZIP files at the bundle root do not match the files listed in bundle.json exactly.';
$string['errorbundleextract'] = 'The plugin ZIP files could not be extracted safely from the bundle.';
$string['errorbundlehashmismatch'] = 'The SHA-256 digest does not match the nested ZIP file: {$a}';
$string['errorbundlecomponentmismatch'] = 'The manifest expects {$a->expected} for {$a->file}, but the package declares {$a->actual}.';
$string['errorbundlemanifest'] = 'Invalid bundle.json: {$a}';
$string['errorbundlemanifestsize'] = 'The manifest must be non-empty and at most {$a}.';
$string['errorbundleinvalidjson'] = 'The manifest is not valid JSON.';
$string['errorbundleobjectrequired'] = 'The top-level JSON value must be an object.';
$string['errorbundleunknownproperty'] = 'Unknown top-level property or properties: {$a}';
$string['errorbundlemissingproperty'] = 'Missing required top-level property: {$a}';
$string['errorbundleformat'] = 'format must equal {$a}.';
$string['errorbundleformatversion'] = 'Supported versions are formatversion 1 and signed formatversions 2 and 3.';
$string['errorbundleactivation'] = 'The signed activation section is invalid.';
$string['errorbundleactivationcomponent'] = 'Activation metadata refers to a component that is not uniquely present in the bundle: {$a}';
$string['errorbundlelmhmissing'] = 'This bundle requires the 108design Plugin Manager, but it is neither installed nor included.';
$string['errorbundlelmhversion'] = 'This bundle requires Plugin Manager version {$a->required}; available is {$a->available}.';
$string['continuebundleactivation'] = 'Continue bundle activation';
$string['errorbundleid'] = 'id must match ^[a-z][a-z0-9_-]{1,99}$.';
$string['errorbundletextproperty'] = 'The {$a} text field is invalid or too long.';
$string['errorbundleplugincount'] = 'plugins must contain between 1 and {$a} entries.';
$string['errorbundlepluginobject'] = 'Plugin entry {$a} must be a JSON object.';
$string['errorbundlepluginproperties'] = 'Plugin entry {$a} must contain exactly file, component, and sha256.';
$string['errorbundlepluginfile'] = 'Plugin entry {$a} has an invalid root-level ZIP file name.';
$string['errorbundleplugincomponent'] = 'Plugin entry {$a} has an invalid Moodle component.';
$string['errorbundlepluginhash'] = 'Plugin entry {$a} has an invalid SHA-256 digest.';
$string['errorbundleduplicatefile'] = 'The file {$a} occurs more than once in the manifest.';
$string['errorbundleduplicatecomponent'] = 'The component {$a} occurs more than once in the manifest.';
$string['errorinvalidzip'] = 'The file is not a readable ZIP package.';
$string['errortoomanyentries'] = 'The ZIP contains more than {$a} entries.';
$string['errorunsafepath'] = 'The ZIP contains an unsafe path: {$a}';
$string['errorinvalidlayout'] = 'The ZIP has an invalid package layout.';
$string['errormultipleroots'] = 'The ZIP must contain exactly one root directory.';
$string['errorpackageuncompressedlimit'] = 'The package exceeds the uncompressed size limit of {$a}.';
$string['errorbatchuncompressedlimit'] = 'The batch exceeds the uncompressed size limit of {$a}.';
$string['errorinvalidroot'] = 'The ZIP root directory name is invalid: {$a}';
$string['errormissingversionphp'] = 'No version.php was found directly in the plugin root.';
$string['errorextractmetadata'] = 'The package metadata could not be extracted safely.';
$string['errormissingcomponent'] = 'version.php does not declare $plugin->component.';
$string['errorinvalidcomponent'] = 'Invalid plugin component: {$a}';
$string['errorunknownplugintype'] = 'The plugin type “{$a}” is unknown on this site. A new parent plugin may need to be installed first.';
$string['errorcomponentrootmismatch'] = 'Component {$a->component} does not match ZIP root directory {$a->root}.';
$string['errormissingversion'] = 'version.php does not declare a numeric $plugin->version.';
$string['errorrequiresmoodle'] = 'The package requires Moodle build {$a} or newer.';
$string['errorunsupportedbranch'] = 'Moodle branch {$a->branch} is outside the package support range {$a->minimum}–{$a->maximum}.';
$string['warningdynamicdependencies'] = 'The dependencies declaration is dynamic or could not be parsed completely. Moodle will perform its normal dependency check after code deployment.';
$string['errordowngrade'] = 'Downgrade blocked. The installed version is {$a}.';
$string['warningsameversion'] = 'The package has the same version number as the installed plugin and will replace its code.';
$string['errorcorevalidation'] = 'Moodle core package validation failed.';
$string['errorduplicatecomponent'] = 'The component {$a} occurs more than once in this batch.';
$string['errormissingdependency'] = 'Required dependency {$a} is neither installed nor included in this batch.';
$string['errordependencyversion'] = 'Dependency {$a->component} requires version {$a->required}; version {$a->available} is available from the {$a->source}.';

$string['eventbundlevalidated'] = 'Plugin batch validated';
$string['eventbundleinstallationstarted'] = 'Plugin batch installation started';
$string['taskcleanupstaging'] = 'Clean up bulk plugin installation staging';
$string['taskcleanupresult'] = 'Removed {$a} expired bulk installation staging directories.';
$string['privacy:metadata'] = 'Bulk Installer stores no participant data. Upload metadata, confirmations and access references for temporary receipts are held only in the native administrator session. Temporary receipt files contain components and statuses without user identifiers or plugin content. Moodle manages sessions and administrative audit events.';
$string['errorbundlepublisher'] = 'The signed bundle publisher information is invalid.';
$string['errorbundlesignature'] = 'The prepared bundle signature is invalid. The bundle may have been modified or may not originate from 108design.';
$string['warningbundlepublisherfilename'] = 'The signed 108design bundle “{$a}” is valid, but its filename does not follow the bundle_ convention. This does not block installation; keep an original name such as bundle_course_tools-1.0.0.zip where possible.';

// Contextual administration help.
$string['adminhelp'] = 'Administration help';
$string['adminhelpmore'] = 'More in the help panel';
$string['adminhelptopics'] = 'Help Topics';
$string['adminhelp_overview'] = 'Preparing plugin packages';
$string['adminhelp_overview_article'] = '<p>Upload the plugin package or supported bundle, then review its components, versions and prerequisites.</p><p>Reviewing does not install anything. Before confirming installation, create a suitable backup of Moodle and its database. Check existing plugin versions and local customisations.</p>';
$string['adminhelp_bundles'] = 'Bundles and licenses';
$string['adminhelp_bundles_article'] = '<p>A bundle can contain several related plugins. Review the complete selection and required dependencies before continuing.</p><p>Successful installation does not automatically activate paid features. Manage applicable activations afterwards in <strong>108design Plugin Manager</strong> or on the product\'s license page.</p>';

$string['reviewnotinstalled'] = 'Not installed';

$string['reviewbuild'] = 'Moodle build';

$string['reviewreasonrequires'] = 'This package requires Moodle build {$a->build} or newer.';

$string['reviewreasondowngrade'] = 'The package is older than the installed build. Select a newer package.';

$string['reviewreasonsameversion'] = 'The same build is installed. Its plugin code will be replaced.';

$string['reviewreasonsupported'] = 'This package declares support for Moodle branches {$a->minimum}–{$a->maximum}. Select a package for this Moodle version.';

$string['reviewreasontargetexists'] = 'The destination directory already exists.';

$string['reviewreasonmaturity'] = 'The package is marked as a development release. Review its maturity in the details.';

$string['reviewnomessages'] = 'No errors or warnings in this package check.';

$string['reviewanyversion'] = 'Any version';

$string['reviewerror'] = 'Error';

$string['reviewwarning'] = 'Warning';

$string['reviewinfo'] = 'Information';

$string['reviewstateerror'] = 'Blocked';

$string['reviewstatewarning'] = 'Review warnings';

$string['reviewstateready'] = 'Checked · ready';

$string['reviewmessagecounts'] = '{$a->errors} errors · {$a->warnings} warnings';

$string['reviewfilterall'] = 'All';

$string['reviewfiltererror'] = 'Blocked';

$string['reviewfilterwarning'] = 'Warnings Only';

$string['reviewfilterready'] = 'Ready';

$string['reviewgroupready'] = 'The batch is ready for handover';

$string['reviewgroupblocked'] = 'This batch is blocked';

$string['reviewgroupreadybody'] = 'There are no blocking errors. Confirm once to hand over the entire batch.';

$string['reviewgroupblockedbody'] = '{$a} packages have errors. Correct the errors or select suitable packages before handing over the batch.';

$string['reviewwarningpackages'] = 'Packages with warnings and no errors: {$a}.';

$string['reviewsource'] = 'Source';

$string['reviewsourceupdates'] = '108design Plugin Manager';

$string['reviewsourcesbundle'] = 'Bundle ZIP';

$string['reviewsourceupload'] = 'ZIP upload';

$string['reviewstepmanager'] = 'Selected in Plugin Manager';

$string['reviewstepselected'] = 'Packages selected';

$string['reviewstepcheck'] = 'Review batch';

$string['reviewstepmoodle'] = 'Moodle installation';

$string['reviewsteps'] = 'Installation progress';

$string['reviewbatchmeta'] = '{$a->count} packages · {$a->size} uncompressed · Moodle {$a->moodle}';

$string['reviewfilters'] = 'Filter validation results';

$string['reviewidentity'] = 'Plugin · planned action';

$string['reviewinstalled'] = 'Installed';

$string['reviewtarget'] = 'In package';

$string['reviewresult'] = 'Validation result';

$string['reviewpackages'] = 'Detected plugins';

$string['reviewdetails'] = 'Details';

$string['reviewto'] = 'to';

$string['reviewfilecomponent'] = 'File and component';

$string['reviewcompatibility'] = 'Compatibility and destination';

$string['reviewrequires'] = 'Minimum Moodle build';

$string['reviewsupported'] = 'Supported Moodle branches';

$string['reviewtargetpath'] = 'Destination directory';

$string['reviewdependency'] = 'Required dependency';

$string['revieworiginals'] = 'Validation details and original messages';

$string['reviewcorechecked'] = 'Handover to Moodle.';

$string['reviewhandoff'] = 'After confirmation, Moodle validates the packages, deploys the plugin files sequentially and leads you through the normal upgrade process.';

$string['reviewbackup'] = 'Back up the site files and database before installation. If installation is interrupted after “Hand Packages to Moodle”, some plugins may already have been updated. “Cancel” only discards the prepared batch here.';

$string['review'] = 'Reviewing the batch';

$string['review_help'] = 'Compare the installed release with the package. Red results block the entire batch; open Details for complete messages and prerequisites.';

$string['adminhelp_review'] = 'Reviewing versions and results';

$string['adminhelp_review_article'] = '<p><strong>Installed</strong> and <strong>In package</strong> show the readable release above its numeric Moodle build. If a reliable release is unavailable, the build is shown by itself. Moodle compares builds to determine an update, replacement or downgrade.</p><p>Filters change only the visible rows. Red errors block the entire batch. Warnings remain visible and need review; replacing the same build overwrites its code. <strong>Details</strong> contains the original messages, dependencies, destination and SHA-256.</p><p>Packages received from <strong>108design Plugin Manager</strong> retain the selected signed release. Catalogue validity, package hashes, components, builds and the installed state are checked again before deployment.</p><p><strong>Hand Packages to Moodle</strong> confirms the entire group. Moodle performs package validation, deploys directories sequentially and continues to its normal dependency and database upgrade checks. Back up site files and the database first: this is not one atomic transaction.</p>';

$string['reviewgroupbatchblockedbody'] = 'The batch failed its combined check. Resolve the following issues before continuing.';

$string['reviewerrorcount'] = 'Errors: {$a}';

$string['reviewwarningcount'] = 'Warnings: {$a}';

$string['selectionempty'] = 'No packages remain in this selection.';

$string['selectionchanged'] = 'The selection or its check results have changed. Review the batch again.';

$string['selectionpreview'] = 'Review Reduced Selection';

$string['selectionpreviewintro'] = 'These packages will be excluded. This also includes packages whose required dependencies would no longer be available. Review the remaining selection below.';

$string['selectionpartial'] = 'Reduced selection: {$a} package(s) excluded.';

$string['selectionexcluded'] = 'Excluded Packages';

$string['selectionbundlemanual'] = 'This installs only part of the bundle. Set up licences separately in the installed plugins or the Plugin Manager.';

$string['selectionbundleactivation'] = 'This installs only part of the bundle. The subsequent setup includes only products whose components are all in this selection.';

$string['selectionconfirm'] = 'Use Reduced Selection';

$string['selectionunavailable'] = 'No installable selection remains. Return to the batch and review its errors.';

$string['selectionexclude'] = 'Exclude Blocking Packages';

$string['selectionpreviewready'] = 'The remaining selection has been checked';
$string['selectionpreviewreadybody'] = 'Use the reduced selection. You can then hand the packages to Moodle.';

$string['adminhelp_review_article'] .= '<p><strong>Exclude Blocking Packages</strong> previews a reduced selection, including any additional packages that would lose a required dependency. <strong>Use Reduced Selection</strong> confirms this change; review the remaining packages before handing them to Moodle. Excluded files remain listed until the batch is discarded or installed. For a partial bundle, setup includes only complete products and requires a suitable Plugin Manager. Otherwise, set up licences separately.</p>';

// Bulk Uninstaller.
$string['uninstalltab'] = 'Uninstaller';
$string['uninstallheading'] = 'Uninstall plugins together';
$string['uninstallintro'] = 'Select additional plugins and review the complete selection and its consequences before uninstalling.';
$string['uninstallsearch'] = 'Name or component';
$string['uninstalltype'] = 'Plugin type';
$string['uninstallstate'] = 'Status';
$string['uninstallalltypes'] = 'All plugin types';
$string['uninstallallstates'] = 'All statuses';
$string['uninstallinstalled'] = 'Installed';
$string['uninstallmissing'] = 'Missing from disk';
$string['uninstallcandidate'] = 'Available for selection; dependencies are checked in the next step.';
$string['uninstallselectvisible'] = 'Select Filtered Plugins';
$string['uninstallclear'] = 'Clear Selection';
$string['uninstallcount'] = '{selected} selected · {hidden} hidden by filters';
$string['uninstallfilterhelp'] = 'Core plugins are excluded. Filters only change the display; hidden checked plugins remain selected and appear in the complete review.';
$string['uninstallremovefiles'] = 'Also remove plugin files where this can be done safely';
$string['uninstallempty'] = 'No additional installed plugins found.';
$string['uninstallnomatches'] = 'No plugins match these filters.';
$string['uninstallreview'] = 'Review Selection';
$string['uninstallready'] = 'The selection can be uninstalled';
$string['uninstallblocked'] = 'The selection contains blockers';
$string['uninstallaffectedcount'] = 'Affected plugins, including associated subplugins: {$a}.';
$string['uninstallchild'] = 'Removed together with {$a}.';
$string['uninstallfilesabsent'] = 'Plugin files are already absent.';
$string['uninstallfilesauto'] = 'Plugin files will also be removed.';
$string['uninstallfilesmanual'] = 'Files require manual removal. The batch stops after this step.';
$string['uninstallusageunknown'] = 'Usage cannot be fully determined';
$string['uninstallactivities'] = '{$a} activity instances';
$string['uninstallblocks'] = '{$a} block instances';
$string['uninstallusagelimit'] = 'Usage counts show existing activity and block instances. Other data may be affected even when the count is 0. This review cannot determine every consequence of arbitrary third-party plugins.';
$string['uninstallorder'] = 'Planned order';
$string['uninstallconsequences'] = 'Uninstalling may permanently delete settings, content, files and other data belonging to the listed plugins. Subplugins are included. Have a complete backup ready. The batch processes plugins sequentially, stops on errors or manual follow-up, and provides no automatic undo.';
$string['uninstallacknowledge'] = 'I confirm the complete displayed selection, including subplugins, and understand that data may be permanently lost.';
$string['uninstallconfirm'] = 'Permanently Uninstall Selection ({$a})';
$string['uninstallexclude'] = 'Exclude Blocking Plugins';
$string['uninstallexclusionheading'] = 'Reduced selection preview';
$string['uninstallexclusionintro'] = 'These plugins would be excluded. The reduced selection is applied only after confirmation. Nothing is uninstalled at this step.';
$string['uninstallapplyexclusion'] = 'Use Reduced Selection';
$string['uninstallexcluded'] = 'Excluded plugins and reasons';
$string['uninstallerrorselection'] = 'Select between 1 and 50 plugins.';
$string['uninstallerrorunknown'] = '{$a->component}: plugin could not be reliably identified.';
$string['uninstallerrorself'] = '{$a->component}: Bulk Installer cannot uninstall itself here.';
$string['uninstallerrorstandard'] = '{$a->component}: Core plugins and components of unknown origin are excluded.';
$string['uninstallerrorpending'] = '{$a->component}: installation or upgrade is pending. Resolve this state in the plugin overview first.';
$string['uninstallerrornative'] = '{$a->component}: This plugin type cannot be safely uninstalled in this batch. Review its individual uninstall in Moodle.';
$string['uninstallerrorrequired'] = '{$a->component} is required by {$a->detail}. That dependent plugin is outside the selection.';
$string['uninstallerrorcycle'] = '{$a->component}: dependencies prevent a safe uninstall order.';
$string['uninstallerrorpath'] = '{$a->component}: the plugin path contains a link, is outside this installation or cannot be safely checked.';
$string['uninstallpolicy'] = 'Server configuration blocks this web uninstaller or an upgrade is in progress. Resolve this state first.';
$string['uninstallstale'] = 'Confirmation has expired, has already been used or the reviewed state has changed. Review the selection again.';
$string['uninstallincomplete'] = 'Moodle did not fully complete the uninstall.';
$string['uninstallackrequired'] = 'First acknowledge the displayed selection and possible data loss.';
$string['uninstallreceiptfailed'] = 'The result receipt could not be safely saved. The batch will not continue.';
$string['uninstallfailed'] = 'The step failed or was only partially completed. Check the actual state and server log before continuing.';
$string['uninstallresultcomplete'] = 'The confirmed batch is complete.';
$string['uninstallresultstopped'] = 'The batch stopped. Completed changes remain in effect; subsequent plugins were not processed.';
$string['uninstallresultinterrupted'] = 'The batch was interrupted. Check the actual state of the last step. No automatic retry will occur.';
$string['uninstallresultrunning'] = 'The batch started, but completion is not confirmed. Check the actual state before taking further action.';
$string['uninstallstepdone'] = 'Uninstalled; existing plugin files removed.';
$string['uninstallstepmanual'] = 'Uninstalled; plugin files remained for manual removal during this batch. The batch stopped.';
$string['uninstallstepfailed'] = 'Failed or partially completed. Check the listed components before starting again.';
$string['uninstallmanualcomponents'] = 'Plugin files not removed during this batch: {$a}';
$string['uninstallnotrun'] = 'Not processed';
$string['uninstalluncertain'] = 'Last started step with an uncertain outcome: {$a}';
$string['uninstallfinishnotice'] = 'The uninstalled plugins and their files have been removed. Select “Open Moodle completion check” to open the final check. Moodle checks the changed plugin inventory and shows any further steps required. Finish this check to complete uninstallation.';
$string['uninstallfinish'] = 'Open Moodle completion check';
$string['uninstallback'] = 'Back to Plugin Selection';
$string['uninstalllastresult'] = 'View Last Batch Result';
$string['uninstallprogress'] = 'Uninstall progress';
$string['bulkinstall:uninstall'] = 'Uninstall multiple additional plugins after explicit confirmation';
$string['uninstall_help'] = 'Core and Bulk Installer itself are excluded. Review the complete selection, including subplugins and data loss, before confirming.';
$string['uninstall'] = 'Uninstalling';
$string['uninstallfiles'] = 'Removing plugin files';
$string['uninstallfiles_help'] = 'Database uninstall and file removal are separate steps. Files that cannot be safely removed or are managed externally require manual follow-up.';
$string['adminhelp_uninstall'] = 'Uninstalling plugins together';
$string['adminhelp_uninstall_article'] = '<p>Select additional plugins and open <strong>Review Selection</strong>. Core plugins are excluded and Bulk Installer is protected. A separate uninstall permission is required in addition to site configuration.</p><p>Filters only change the display. Hidden checked plugins remain part of the complete review. Associated subplugins are explicitly listed. Dependents outside the selection block removal and are never added automatically.</p><p><strong>Exclude Blocking Plugins</strong> previews a reduced selection with every excluded plugin and reason. <strong>Use Reduced Selection</strong> applies it. A separate final uninstall confirmation is still required.</p><p>Review usage counts and additional Moodle warnings. Counts cover activity and block instances, not every kind of data or consequence. A disabled plugin or a count of 0 does not mean no data will be lost.</p><p>Confirmation expires after one hour and can only be used once. Changed files, versions, dependencies or counted usage require a fresh review. Errors stop the batch and completed steps are not rolled back. Cancel only discards preparation.</p>';
$string['adminhelp_uninstallfiles'] = 'Files and completion';
$string['adminhelp_uninstallfiles_article'] = '<p>Moodle first removes installed plugin data. With file removal enabled, the reviewed plugin directories are then removed where this is safe. Links and paths outside this Moodle installation are blocked.</p><p>Read-only or externally managed plugin files are not removed automatically. The batch stops and identifies affected components. Disabling file removal has the same effect.</p><p>The active completion stage lists directories that are actually still present. Remove these on the server and select “Check files again”. This button does not delete files or retry uninstallation. Only a successful check makes “Open Moodle completion check” available. Moodle then checks the changed plugin inventory and shows further steps required. Remaining code may otherwise trigger reinstallation. There is no cancel button after execution.</p><p>“View Last Batch Result” opens the historical receipt without completion actions. It records the state during the batch; later file checks do not change it.</p><p>Have a complete backup ready before execution. The uninstaller creates no automatic backup and provides no automatic undo. Temporary receipts contain components, reviewed plugin paths and statuses, remain available for up to 24 hours and are removed by the existing cleanup task.</p><p>If a request fails, Bulk Installer adds technical diagnostic entries to the PHP or web server error log. They identify the request, processing stage, affected component and technical failure locations. These BI entries omit exception message text, form contents, confirmation tokens and user identifiers. Server log retention follows your server configuration; the 24-hour limit applies only to batch receipts. Logging does not retry uninstallation.</p>';

$string['eventuninstallbatchstarted'] = 'Plugin uninstall batch started';
$string['eventuninstallbatchfinished'] = 'Plugin uninstall batch finished';

$string['uninstallproposalready'] = 'The reduced selection is ready to apply';

$string['uninstallhistoryheading'] = 'Last uninstall batch results';

$string['uninstallcompletionheading'] = 'Complete uninstallation';

$string['uninstallreceiptunavailable'] = 'No receipt is available for your session. Receipts are retained for up to 24 hours.';

$string['uninstallremainingfiles'] = 'The following plugin directories are still present:';

$string['uninstallmanualnotice'] = 'Remove these plugin directories on the server. Remaining code may otherwise trigger reinstallation. Then select “Check files again” to verify that all listed directories have been removed. This button checks files; it does not delete them.';

$string['uninstallrecheck'] = 'Check files again';

$string['uninstallcompletionuncertain'] = 'Completion cannot yet be confirmed safely. Check the errors reported above and the plugin status before continuing. The file check does not retry uninstallation or restore data.';

$string['uninstallnextstep'] = 'Next step';
