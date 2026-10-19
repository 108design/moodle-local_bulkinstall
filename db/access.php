<?php
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    // Site administrators have this implicitly. No delegated role gains destructive access by default.
    'local/bulkinstall:uninstall' => [
        'riskbitmask' => RISK_CONFIG | RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [],
    ],
];
