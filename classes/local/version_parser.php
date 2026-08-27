<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

namespace local_bulkinstall\local;

/**
 * Safely reads literal metadata declarations from version.php source code.
 *
 * The uploaded PHP is tokenised and parsed as text. It is never included.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 */
class version_parser {
    /**
     * Parse supported plugin metadata.
     *
     * @param string $source PHP source
     * @return array
     */
    public function parse(string $source): array {
        $code = $this->strip_php($source);
        $result = [
            'component' => $this->string_value($code, 'component'),
            'version' => $this->integer_value($code, 'version'),
            'requires' => $this->integer_value($code, 'requires'),
            'release' => $this->string_value($code, 'release'),
            'maturity' => $this->constant_value($code, 'maturity'),
            'supported' => null,
            'dependencies' => [],
            'dependenciescomplete' => true,
        ];

        if (preg_match('/\\$plugin->supported=\\[([0-9]+),([0-9]+)\\];/', $code, $matches) ||
                preg_match('/\\$plugin->supported=array\\(([0-9]+),([0-9]+)\\);/', $code, $matches)) {
            $result['supported'] = [(int)$matches[1], (int)$matches[2]];
        }

        $dependencybody = null;
        if (preg_match('/\\$plugin->dependencies=\\[(.*?)\\];/s', $code, $matches) ||
                preg_match('/\\$plugin->dependencies=array\\((.*?)\\);/s', $code, $matches)) {
            $dependencybody = $matches[1];
        }
        if ($dependencybody !== null) {
            preg_match_all(
                '/([\'\"])([a-z][a-z0-9_]*_[a-z0-9_]+)\\1=>([0-9]+|ANY_VERSION)/',
                $dependencybody,
                $matches,
                PREG_SET_ORDER
            );
            foreach ($matches as $match) {
                $result['dependencies'][$match[2]] = $match[3] === 'ANY_VERSION' ? 0 : (int)$match[3];
            }
            $result['dependenciescomplete'] = substr_count($dependencybody, '=>') === count($matches);
        }

        return $result;
    }

    /** @return int|null */
    private function integer_value(string $code, string $name): ?int {
        if (preg_match('/\\$plugin->' . preg_quote($name, '/') . '=([0-9]+);/', $code, $matches)) {
            return (int)$matches[1];
        }
        return null;
    }

    /** @return string|null */
    private function string_value(string $code, string $name): ?string {
        if (preg_match('/\\$plugin->' . preg_quote($name, '/') . '=([\'\"])(.*?)\\1;/', $code, $matches)) {
            return stripcslashes($matches[2]);
        }
        return null;
    }

    /** @return string|null */
    private function constant_value(string $code, string $name): ?string {
        if (preg_match('/\\$plugin->' . preg_quote($name, '/') . '=([A-Z][A-Z0-9_]+);/', $code, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Remove comments, whitespace, and non-PHP content without evaluating code.
     *
     * @param string $source
     * @return string
     */
    private function strip_php(string $source): string {
        $output = '';
        $insidephp = false;
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                if ($insidephp) {
                    $output .= $token;
                }
                continue;
            }
            [$id, $text] = $token;
            if ($id === T_OPEN_TAG) {
                $insidephp = true;
            } else if ($id === T_CLOSE_TAG) {
                $insidephp = false;
            } else if ($insidephp && !in_array($id, [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                $output .= $text;
            }
        }
        return $output;
    }
}
