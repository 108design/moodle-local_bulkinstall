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
 * Final deployment confirmation form.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 */
class confirm_form extends \moodleform {
    /** Define form elements. */
    protected function definition(): void {
        $mform = $this->_form;
        $mform->addElement('hidden', 'storage', $this->_customdata['storage']);
        $mform->setType('storage', PARAM_ALPHANUM);
        $mform->addElement('static', 'confirmation', '', get_string('confirmationtext', 'local_bulkinstall'));
        $this->add_action_buttons(true, get_string('installbatch', 'local_bulkinstall'));
    }
}
