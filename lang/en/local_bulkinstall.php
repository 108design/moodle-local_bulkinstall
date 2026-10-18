<?php
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
$string['privacy:metadata'] = 'The Bulk plugin installation plugin does not store personal data. Temporary packages and session metadata are removed after use or expiry.';
$string['licenseactivation'] = 'Licenses';
$string['licenseproductname'] = '108design Bulk installation';
$string['licensestatusactive'] = 'The free license is activated for this Moodle installation.';
$string['licensestatushub'] = 'The free license is activated and managed by the 108design Plugin Manager.';
$string['licensestatusactivatedshort'] = 'Activated';
$string['licensestatusnotactivatedshort'] = 'Not activated';
$string['licenseproduct'] = 'Product';
$string['licensedetails'] = 'Activation details';
$string['licensesite'] = 'Licensed Moodle URL';
$string['licenseenvironment'] = 'Environment';
$string['licenseauthority'] = 'Managed by';
$string['licenseauthorityhub'] = '108design Plugin Manager';
$string['licenseauthorityplugin'] = 'This plugin';
$string['licensehandoverpending'] = 'The license has already been moved into the 108design Plugin Manager. Activate its free license now to make central management effective.';
$string['openlicensemanager'] = 'Open Plugin Manager';
$string['managewithlicensemanager'] = 'Manage in Plugin Manager';
$string['completehandover'] = 'Complete central license management';
$string['activateinlicensemanager'] = 'Activate free license in Plugin Manager';
$string['managelicense'] = 'Manage license';
$string['licensestatusinactive'] = 'The free license must be activated before use.';
$string['licenserequired'] = 'Please activate the free license first.';
$string['sitelinkintro'] = 'Activation has two short steps: first connect this Moodle site to your 108design account once. Then activate the free license for this installation here.';
$string['connectaccount'] = 'Connect 108design account';
$string['sitelinkstarted'] = 'The connection request was created. Complete step 1 on the account page, then return here.';
$string['sitelinkpending'] = 'Step 1 is not complete yet. Connect the site on the account page first.';
$string['sitelinkcode'] = 'Your connection code: {$a}';
$string['sitelinkstep2'] = 'After connecting the account, return to this page and complete step 2. This claims the free license and activates it for this Moodle installation.';
$string['sitelinkaccountconnected'] = 'Step 1 is complete. Use step 2 now to activate the free license for this Moodle installation.';
$string['opensitelink'] = 'Step 1: Connect account';
$string['checksitelink'] = 'Step 2: Activate free license';
$string['manualactivation'] = 'Use an existing license key';
$string['licensekey'] = 'License key';
$string['activateplugin'] = 'Activate plugin';
$string['licenseactivated'] = 'The free license is activated.';
$string['licenserefreshed'] = 'The license was refreshed.';
$string['licensedeactivated'] = 'The activation was deactivated.';
$string['deactivatelicense'] = 'Deactivate license';
$string['refreshlicense'] = 'Refresh license';
$string['openbulkinstall'] = 'Open Bulk installation';
$string['environmentproduction'] = 'Production site';
$string['environmentdevelopment'] = 'Development';
$string['taskrefreshlicense'] = 'Refresh free Bulk installation license';
$string['licenseclienterror'] = 'License service: {$a->detail} ({$a->code})';
$string['errorbundlepublisher'] = 'The signed bundle publisher information is invalid.';
$string['errorbundlesignature'] = 'The prepared bundle signature is invalid. The bundle may have been modified or may not originate from 108design.';
$string['warningbundlepublisherfilename'] = 'The signed 108design bundle “{$a}” is valid, but its filename does not follow the bundle_ convention. This does not block installation; keep an original name such as bundle_course_tools-1.0.0.zip where possible.';
$string['licensepolicydegradation'] = 'New package checks and installations are blocked. Previously staged data is preserved until normal expiry; the activation interface remains accessible.';
$string['licensepolicydeactivation'] = 'Bulk installation stops completely. Saved settings and staged data remain stored; activation and recovery remain accessible.';

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
