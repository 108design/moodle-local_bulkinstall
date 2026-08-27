<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

namespace local_bulkinstall\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Multiple plugin ZIP upload form.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 */
class upload_form extends \moodleform {
    /** Define form elements. */
    protected function definition(): void {
        $mform = $this->_form;
        $mform->addElement('filemanager', 'packages', get_string('packages', 'local_bulkinstall'), null,
            $this->_customdata['fileoptions']);
        $mform->addHelpButton('packages', 'packages', 'local_bulkinstall');
        $mform->addRule('packages', get_string('required'), 'required', null, 'client');
        $this->add_action_buttons(false, get_string('checkpackages', 'local_bulkinstall'));
    }

    /**
     * Require at least one actual draft file.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files): array {
        global $USER;

        $errors = parent::validation($data, $files);
        $draftid = (int)($data['packages'] ?? 0);
        if ($draftid > 0) {
            $usercontext = \context_user::instance($USER->id);
            $draftfiles = get_file_storage()->get_area_files(
                $usercontext->id,
                'user',
                'draft',
                $draftid,
                'filename',
                false
            );
            if (empty($draftfiles)) {
                $errors['packages'] = get_string('errornofiles', 'local_bulkinstall');
            } else if (count($draftfiles) > \local_bulkinstall\local\staging_manager::MAX_PACKAGES) {
                $errors['packages'] = get_string(
                    'errortoomanypackages',
                    'local_bulkinstall',
                    \local_bulkinstall\local\staging_manager::MAX_PACKAGES
                );
            }
        }
        return $errors;
    }
}
