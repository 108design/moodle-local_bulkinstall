<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_bulkinstall_bundleformat');
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_url(new moodle_url('/local/bulkinstall/bundleformat.php'));
$PAGE->set_title(get_string('bundleformattitle', 'local_bulkinstall'));
$PAGE->set_heading(get_string('pagetitle', 'local_bulkinstall'));

$example = <<<'JSON'
{
  "format": "moodle-plugin-bundle",
  "formatversion": 3,
  "id": "example-course-tools",
  "name": "Example Course Tools",
  "version": "1.1.0",
  "description": "Optional description for administrators.",
  "publisher": "108design",
  "keyid": "bundle-2026-01",
  "plugins": [
    {
      "file": "mod_example.zip",
      "component": "mod_example",
      "sha256": "0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef"
    }
  ],
  "activation": {
    "storeproduct": "example-course-tools",
    "licencemanager": {"required": true, "minimumversion": 2026083107},
    "entitlements": [
      {"code": "example.pro", "licencemodel": "commercial", "components": ["mod_example"]}
    ]
  },
  "signature": "an-86-character-base64url-ed25519-signature"
}
JSON;

$structure = <<<'TEXT'
bundle.json
mod_example.zip
local_examplehelper.zip
TEXT;

echo $OUTPUT->header();
echo \local_bulkinstall\local\admin_navigation::render('format');
echo $OUTPUT->heading(get_string('bundleformattitle', 'local_bulkinstall'), 2);
echo html_writer::tag('p', get_string('bundleformatintro', 'local_bulkinstall'));
echo html_writer::tag('p', get_string('bundleformatfilename', 'local_bulkinstall'));
echo $OUTPUT->heading(get_string('bundleformatstructureheading', 'local_bulkinstall'), 3);
echo html_writer::tag('p', get_string('bundleformatstructure', 'local_bulkinstall'));
echo html_writer::tag('pre', s($structure));
echo $OUTPUT->heading(get_string('bundleformatmanifestheading', 'local_bulkinstall'), 3);
echo html_writer::tag('pre', s($example));

$table = new html_table();
$table->attributes['class'] = 'generaltable local-bulkinstall-format';
$table->head = [
    get_string('bundleformatfield', 'local_bulkinstall'),
    get_string('bundleformattype', 'local_bulkinstall'),
    get_string('bundleformatrequired', 'local_bulkinstall'),
    get_string('bundleformatrules', 'local_bulkinstall'),
];
$yes = get_string('yes');
$no = get_string('no');
$table->data = [
    ['format', 'string', $yes, get_string('bundleformatruleformat', 'local_bulkinstall')],
    ['formatversion', 'integer', $yes, get_string('bundleformatruleformatversion', 'local_bulkinstall')],
    ['id', 'string', $yes, get_string('bundleformatruleid', 'local_bulkinstall')],
    ['name', 'string', $yes, get_string('bundleformatrulename', 'local_bulkinstall')],
    ['version', 'string', $yes, get_string('bundleformatruleversion', 'local_bulkinstall')],
    ['description', 'string', $no, get_string('bundleformatruledescription', 'local_bulkinstall')],
    ['plugins', 'array', $yes, get_string('bundleformatruleplugins', 'local_bulkinstall')],
    ['plugins[].file', 'string', $yes, get_string('bundleformatrulefile', 'local_bulkinstall')],
    ['plugins[].component', 'string', $yes, get_string('bundleformatrulecomponent', 'local_bulkinstall')],
    ['plugins[].sha256', 'string', $yes, get_string('bundleformatrulesha256', 'local_bulkinstall')],
    ['activation', 'object', $yes, get_string('bundleformatruleactivation', 'local_bulkinstall')],
    ['activation.storeproduct', 'string', $yes, get_string('bundleformatrulestoreproduct', 'local_bulkinstall')],
    ['activation.entitlements[]', 'array', $yes, get_string('bundleformatruleentitlements', 'local_bulkinstall')],
];
echo html_writer::table($table);
echo html_writer::tag('p', get_string('bundleformatstrict', 'local_bulkinstall'));
echo html_writer::tag('p', get_string('bundleformatschema', 'local_bulkinstall'));
echo $OUTPUT->single_button(new moodle_url('/local/bulkinstall/index.php'), get_string('backtoupload', 'local_bulkinstall'));
echo $OUTPUT->footer();
