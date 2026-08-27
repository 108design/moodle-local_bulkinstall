<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.
namespace local_bulkinstall\local;

/** Safe structured LAS transport or protocol failure. */
final class license_client_exception extends \moodle_exception {
    public function __construct(
        public readonly string $lascode,
        public readonly string $detail,
        public readonly int $httpstatus = 0
    ) {
        parent::__construct('licenseclienterror', 'local_bulkinstall', '', (object) [
            'code' => $lascode,
            'detail' => $detail,
        ]);
    }
}


