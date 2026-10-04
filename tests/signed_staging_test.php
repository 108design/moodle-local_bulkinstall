<?php
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
// See LICENSE.md for the full terms.

namespace local_bulkinstall;

use local_bulkinstall\local\staging_manager;

defined('MOODLE_INTERNAL') || die();

/** Signed public fixtures exercise the File API and whole-batch preflight. */
final class signed_staging_test extends \advanced_testcase {
    public function test_signed_bundle_dependency_is_resolved_without_activation(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $manager = new staging_manager();
        $id = $manager->stage([$this->upload(2)]);
        try {
            $analysis = $manager->analyse($id);
            $this->assertTrue($analysis['caninstall']);
            $this->assertSame(2, $analysis['bundle']['formatversion']);
            $this->assertSame([], $analysis['batcherrors']);
            $this->assertCount(2, $analysis['packages']);
            $installables = $manager->installables($analysis);
            $this->assertSame(['local_bundletestfirst', 'local_bundletestsecond'],
                array_column($installables, 'component'));
            foreach ($installables as $installable) {
                $this->assertFileExists($installable->zipfilepath);
            }
        } finally {
            $manager->cleanup($id);
        }
        $this->expectException(\moodle_exception::class);
        $manager->get_record($id);
    }

    public function test_renaming_signed_bundle_does_not_block_installation(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $manager = new staging_manager();
        $id = $manager->stage([$this->upload(2, 'renamed.zip')]);
        try {
            $analysis = $manager->analyse($id);
            $this->assertTrue($analysis['caninstall']);
            $this->assertFalse($analysis['bundle']['publisherfilenamevalid']);
        } finally {
            $manager->cleanup($id);
        }
    }

    public function test_modified_staged_package_blocks_entire_batch(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $manager = new staging_manager();
        $id = $manager->stage([$this->upload(2)]);
        try {
            $analysis = $manager->analyse($id);
            file_put_contents($analysis['packages'][0]['path'], 'tampered', FILE_APPEND);
            $changed = $manager->analyse($id);
            $this->assertFalse($changed['caninstall']);
            $this->assertContains(get_string('errorintegritychanged', 'local_bulkinstall'),
                $changed['packages'][0]['errors']);
            $this->expectException(\coding_exception::class);
            $manager->installables($changed);
        } finally {
            $manager->cleanup($id);
        }
    }

    public function test_signed_journey_requires_manager_for_other_products(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $manager = new staging_manager();
        $id = $manager->stage([$this->upload(3)]);
        try {
            $analysis = $manager->analyse($id);
            $this->assertSame(3, $analysis['bundle']['formatversion']);
            $this->assertSame('installation-test', $analysis['bundle']['activation']['storeproduct']);
            $this->assertFalse($analysis['caninstall']);
            $this->assertContains(get_string('errorbundlelmhmissing', 'local_bulkinstall'), $analysis['batcherrors']);
        } finally {
            $manager->cleanup($id);
        }
    }

    public function test_tampering_signed_manifest_is_rejected_before_preflight(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $directory = make_unique_writable_directory(make_request_directory());
        $packer = get_file_packer('application/zip');
        $fixture = __DIR__ . '/fixtures/bundle_installation-test-v2-1.0.0.zip.fixture';
        $this->assertNotFalse($packer->extract_to_pathname($fixture, $directory));
        $manifest = json_decode(file_get_contents($directory . '/bundle.json'), true, 32, JSON_THROW_ON_ERROR);
        $manifest['name'] = 'Tampered';
        file_put_contents($directory . '/bundle.json', json_encode($manifest, JSON_THROW_ON_ERROR));
        $path = $directory . '/tampered.zip';
        $this->assertTrue($packer->archive_to_pathname([
            'bundle.json' => $directory . '/bundle.json',
            'local_bundletestfirst.zip' => $directory . '/local_bundletestfirst.zip',
            'local_bundletestsecond.zip' => $directory . '/local_bundletestsecond.zip',
        ], $path));
        $file = $this->stored_file($path, 'bundle_tampered.zip');
        try {
            $this->expectException(\moodle_exception::class);
            (new staging_manager())->stage([$file]);
        } finally {
            remove_dir($directory);
        }
    }

    private function upload(int $version, ?string $name = null): \stored_file {
        $fixture = 'bundle_installation-test-v' . $version . '-1.0.0.zip';
        return $this->stored_file(__DIR__ . '/fixtures/' . $fixture . '.fixture', $name ?? $fixture);
    }

    private function stored_file(string $path, string $name): \stored_file {
        global $USER;
        return get_file_storage()->create_file_from_pathname([
            'contextid' => \context_user::instance($USER->id)->id,
            'component' => 'user', 'filearea' => 'draft', 'itemid' => file_get_unused_draft_itemid(),
            'filepath' => '/', 'filename' => $name,
        ], $path);
    }
}
