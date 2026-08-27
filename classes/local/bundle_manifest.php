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
 * Decodes and validates version 1 compatibility bundles and signed version 2 publisher bundles.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 */
class bundle_manifest {
    /** Bundle format identifier. */
    public const FORMAT = 'moodle-plugin-bundle';

    /** Currently supported manifest version. */
    public const FORMAT_VERSION = 2;

    /** Maximum manifest size (256 KiB). */
    public const MAX_BYTES = 262144;

    /** Allowed top-level properties. */
    private const TOP_LEVEL_PROPERTIES = [
        'format', 'formatversion', 'id', 'name', 'version', 'description',
        'publisher', 'keyid', 'signature', 'plugins',
    ];

    /** Required top-level properties. */
    private const REQUIRED_PROPERTIES = [
        'format', 'formatversion', 'id', 'name', 'version', 'plugins',
    ];

    /** Required and allowed properties for one plugin. */
    private const PLUGIN_PROPERTIES = ['file', 'component', 'sha256'];

    /**
     * Decode and validate a bundle manifest.
     *
     * The returned structure contains only documented fields and normalises
     * hashes to lower case.
     *
     * @param string $json Raw bundle.json contents
     * @return array Validated manifest
     */
    public function parse(string $json): array {
        if ($json === '' || strlen($json) > self::MAX_BYTES) {
            $this->fail(get_string('errorbundlemanifestsize', 'local_bulkinstall', display_size(self::MAX_BYTES)));
        }

        try {
            $document = json_decode($json, false, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $this->fail(get_string('errorbundleinvalidjson', 'local_bulkinstall'));
        }
        if (!$document instanceof \stdClass) {
            $this->fail(get_string('errorbundleobjectrequired', 'local_bulkinstall'));
        }

        $properties = array_keys(get_object_vars($document));
        $unknown = array_values(array_diff($properties, self::TOP_LEVEL_PROPERTIES));
        if ($unknown) {
            $this->fail(get_string('errorbundleunknownproperty', 'local_bulkinstall', implode(', ', $unknown)));
        }
        foreach (self::REQUIRED_PROPERTIES as $property) {
            if (!property_exists($document, $property)) {
                $this->fail(get_string('errorbundlemissingproperty', 'local_bulkinstall', $property));
            }
        }

        if ($document->format !== self::FORMAT) {
            $this->fail(get_string('errorbundleformat', 'local_bulkinstall', self::FORMAT));
        }
        if (!is_int($document->formatversion) || !in_array($document->formatversion, [1, 2], true)) {
            $this->fail(get_string('errorbundleformatversion', 'local_bulkinstall', self::FORMAT_VERSION));
        }
        if ($document->formatversion === 2) {
            foreach (['description', 'publisher', 'keyid', 'signature'] as $property) {
                if (!property_exists($document, $property) || !is_string($document->{$property})) {
                    $this->fail(get_string('errorbundlemissingproperty', 'local_bulkinstall', $property));
                }
            }
            if ($document->publisher !== bundle_signature::PUBLISHER
                    || $document->keyid !== bundle_signature::KEY_ID) {
                $this->fail(get_string('errorbundlepublisher', 'local_bulkinstall'));
            }
        } else if (property_exists($document, 'publisher') || property_exists($document, 'keyid')
                || property_exists($document, 'signature')) {
            $this->fail(get_string('errorbundlepublisher', 'local_bulkinstall'));
        }
        if (!is_string($document->id) || !preg_match('/^[a-z][a-z0-9_-]{1,99}$/', $document->id)) {
            $this->fail(get_string('errorbundleid', 'local_bulkinstall'));
        }
        $this->require_text($document->name, 'name', 200);
        $this->require_text($document->version, 'version', 100);
        if (property_exists($document, 'description')) {
            if (!is_string($document->description) || \core_text::strlen($document->description) > 2000) {
                $this->fail(get_string('errorbundletextproperty', 'local_bulkinstall', 'description'));
            }
        }

        if (!is_array($document->plugins) || !$document->plugins ||
                count($document->plugins) > staging_manager::MAX_PACKAGES) {
            $this->fail(get_string(
                'errorbundleplugincount',
                'local_bulkinstall',
                staging_manager::MAX_PACKAGES
            ));
        }

        $plugins = [];
        $files = [];
        $components = [];
        foreach ($document->plugins as $index => $plugin) {
            $position = $index + 1;
            if (!$plugin instanceof \stdClass) {
                $this->fail(get_string('errorbundlepluginobject', 'local_bulkinstall', $position));
            }
            $pluginproperties = array_keys(get_object_vars($plugin));
            $missing = array_values(array_diff(self::PLUGIN_PROPERTIES, $pluginproperties));
            $extra = array_values(array_diff($pluginproperties, self::PLUGIN_PROPERTIES));
            if ($missing || $extra) {
                $this->fail(get_string('errorbundlepluginproperties', 'local_bulkinstall', $position));
            }
            if (!is_string($plugin->file) || \core_text::strlen($plugin->file) > 255 ||
                    basename($plugin->file) !== $plugin->file ||
                    str_contains($plugin->file, '\\') ||
                    !preg_match('/^[^\x00-\x1F\/]+\.zip$/i', $plugin->file)) {
                $this->fail(get_string('errorbundlepluginfile', 'local_bulkinstall', $position));
            }
            if (!is_string($plugin->component) ||
                    !preg_match('/^[a-z][a-z0-9_]*_[a-z][a-z0-9_]*$/', $plugin->component)) {
                $this->fail(get_string('errorbundleplugincomponent', 'local_bulkinstall', $position));
            }
            if (!is_string($plugin->sha256) || !preg_match('/^[a-f0-9]{64}$/i', $plugin->sha256)) {
                $this->fail(get_string('errorbundlepluginhash', 'local_bulkinstall', $position));
            }
            $filekey = strtolower($plugin->file);
            if (isset($files[$filekey])) {
                $this->fail(get_string('errorbundleduplicatefile', 'local_bulkinstall', $plugin->file));
            }
            if (isset($components[$plugin->component])) {
                $this->fail(get_string('errorbundleduplicatecomponent', 'local_bulkinstall', $plugin->component));
            }
            $files[$filekey] = true;
            $components[$plugin->component] = true;
            $plugins[] = [
                'file' => $plugin->file,
                'component' => $plugin->component,
                'sha256' => strtolower($plugin->sha256),
            ];
        }

        $manifest = [
            'format' => self::FORMAT,
            'formatversion' => (int) $document->formatversion,
            'id' => $document->id,
            'name' => $document->name,
            'version' => $document->version,
            'description' => property_exists($document, 'description') ? $document->description : null,
            'plugins' => $plugins,
        ];
        if ($document->formatversion === 2
                && !bundle_signature::verify($manifest, (string) $document->signature)) {
            $this->fail(get_string('errorbundlesignature', 'local_bulkinstall'));
        }
        return $manifest;
    }

    /** Validate one required, non-empty text field. */
    private function require_text(mixed $value, string $property, int $maxlength): void {
        if (!is_string($value) || $value === '' || \core_text::strlen($value) > $maxlength) {
            $this->fail(get_string('errorbundletextproperty', 'local_bulkinstall', $property));
        }
    }

    /** Throw a single public manifest exception. */
    private function fail(string $detail): never {
        throw new \moodle_exception('errorbundlemanifest', 'local_bulkinstall', '', $detail);
    }
}
