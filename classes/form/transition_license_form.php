<?php
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md for the full terms.
namespace local_bulkinstall\form;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/** Explicit licence activation, separate from installation consent and from any purchase. */
final class transition_license_form extends \moodleform {
    /** Never repopulate a bearer key after a rejected activation. */
    public function clear_submitted_key(): void {
        $this->_form->setConstants(['licencekey' => '']);
    }

    protected function definition(): void {
        $form = $this->_form;
        foreach (['storage' => PARAM_ALPHANUM, 'component' => PARAM_COMPONENT, 'reviewtoken' => PARAM_ALPHANUM] as $name => $type) {
            $form->addElement('hidden', $name, $this->_customdata[$name]);
            $form->setType($name, $type);
        }
        if (!empty($this->_customdata['freecore'])) {
            $this->add_action_buttons(true, get_string('frontpagefreeclaim', 'local_bulkinstall'));
            return;
        }
        $form->addElement('password', 'licencekey', get_string('transitionlicencekey', 'local_bulkinstall'),
            ['autocomplete' => 'off']);
        $form->setType('licencekey', PARAM_RAW_TRIMMED);
        $form->addRule('licencekey', get_string('required'), 'required');
        $form->addHelpButton('licencekey', 'transitionlicencekey', 'local_bulkinstall');
        $this->add_action_buttons(true, get_string('transitionactivate', 'local_bulkinstall'));
    }
}
