<?php
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for tests/update_staging_test.php.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen
 * @license   See LICENSE.md for the full terms.
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_bulkinstall;

use local_bulkinstall\local\staging_manager;
use local_lmh108\local\update_catalogue;
use local_lmh108\local\update_plan;
use local_lmh108\local\update_transfer;

/** Native positive cases use an explicitly isolated synthetic publisher, never production signing material. */
final class update_staging_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $secret = base64_decode((string)getenv('INLINE_UPDATES_TEST_SECRET'), true);
        if (!$secret || !class_exists(update_plan::class)
                || base64_encode(sodium_crypto_sign_publickey_from_secretkey($secret)) !== update_catalogue::KEY) {
            $this->markTestSkipped('Requires isolated synthetic publisher runtime; production pinned key must remain unchanged.');
        }
        unset_config('updatepublished', 'local_lmh108');
        set_config('updatebeta', 0, 'local_lmh108');
    }

    private function fixture(string $component = 'local_toastalerts', ?int $packageversion = null,
            array $dependencies = []): array {
        global $CFG;
        $installed = update_plan::installed(update_plan::snapshot());
        $version = $installed[$component] + 1;
        $name = substr($component, 6);
        $root = make_unique_writable_directory(make_request_directory());
        mkdir($root . '/' . $name);
        mkdir($root . '/' . $name . '/lang');
        mkdir($root . '/' . $name . '/lang/en');
        file_put_contents($root . '/' . $name . '/version.php', '<?php ' .
            '$plugin->component = ' . var_export($component, true) . '; ' .
            '$plugin->version = ' . ($packageversion ?? $version) . '; ' .
            '$plugin->requires = 2024100700; $plugin->release = "Synthetic update"; ' .
            '$plugin->supported = [405, 503]; $plugin->dependencies = ' . var_export($dependencies, true) . ';');
        file_put_contents($root . '/' . $name . '/lang/en/' . $component . '.php',
            '<?php $string["pluginname"] = "Synthetic update";');
        $zip = $root . '/package.zip';
        $this->assertTrue(get_file_packer('application/zip')->archive_to_pathname([$name => $root . '/' . $name], $zip));
        $sha = hash_file('sha256', $zip);
        return ['file' => ['path' => $zip, 'component' => $component, 'version' => $version],
            'release' => ['component' => $component, 'version' => $version, 'name' => 'Synthetic update',
                'label' => 'Synthetic update', 'channel' => 'stable', 'requires' => 2024100700,
                'branches' => [(int)$CFG->branch], 'phpmin' => '8.1', 'dependencies' => $dependencies,
                'sha256' => $sha, 'notes' => 'Synthetic local fixture',
                'download' => 'https://cms.108design.com/local/storefront/update_artifact.php?sha256=' . $sha]];
    }

    private function catalogue(array $releases, int $expires = 0): string {
        $payload = json_encode(['format' => '108design-updates-v1', 'published' => time() - 1,
            'expires' => $expires ?: time() + 3600, 'releases' => $releases], JSON_THROW_ON_ERROR);
        return json_encode(['payload' => base64_encode($payload), 'signature' => base64_encode(
            sodium_crypto_sign_detached(update_catalogue::DOMAIN . $payload,
                base64_decode(getenv('INLINE_UPDATES_TEST_SECRET'))))], JSON_THROW_ON_ERROR);
    }

    private function stage(array $fixtures): string {
        return (new staging_manager())->stage_verified_updates(array_column($fixtures, 'file'),
            $this->catalogue(array_column($fixtures, 'release')), update_plan::fingerprint(update_plan::snapshot()));
    }

    public function test_complete_signed_selection_uses_one_batch(): void {
        $manager = new staging_manager();
        $parent = $this->fixture('local_frontpage');
        $child = $this->fixture('local_toastalerts', null, ['local_frontpage' => $parent['release']['version']]);
        $id = $this->stage([$child, $parent]);
        try {
            $analysis = $manager->analyse($id);
            $this->assertTrue($analysis['caninstall'], json_encode($analysis['batcherrors']));
            $this->assertTrue($analysis['updates']);
            $this->assertCount(2, $manager->installables($analysis));
            $this->assertNull($analysis['bundle']);
        } finally {
            $manager->cleanup($id);
        }
    }

    public function test_signed_hash_does_not_hide_wrong_package_build(): void {
        $fixture = $this->fixture('local_toastalerts', 2020100100);
        $manager = new staging_manager();
        $id = $this->stage([$fixture]);
        try {
            $analysis = $manager->analyse($id);
            $this->assertFalse($analysis['caninstall']);
            $this->assertContains(get_string('errorupdateversion', 'local_bulkinstall'), $analysis['packages'][0]['errors']);
        } finally {
            $manager->cleanup($id);
        }
    }

    public function test_changed_staging_hash_blocks_the_entire_batch(): void {
        $manager = new staging_manager();
        $id = $this->stage([$this->fixture()]);
        try {
            $analysis = $manager->analyse($id);
            file_put_contents($analysis['packages'][0]['path'], 'tampered', FILE_APPEND);
            $changed = $manager->analyse($id);
            $this->assertFalse($changed['caninstall']);
            $this->assertContains(get_string('errorintegritychanged', 'local_bulkinstall'), $changed['packages'][0]['errors']);
        } finally {
            $manager->cleanup($id);
        }
    }

    public function test_changed_installation_blocks_staged_updates(): void {
        $manager = new staging_manager();
        $id = $this->stage([$this->fixture()]);
        try {
            set_config('version', 2020100100, 'local_toastalerts');
            \core_plugin_manager::reset_caches(true);
            $analysis = $manager->analyse($id);
            $this->assertFalse($analysis['caninstall']);
            $this->assertNotEmpty($analysis['batcherrors']);
        } finally {
            $manager->cleanup($id);
        }
    }

    public function test_missing_downloaded_dependency_is_not_a_complete_handover(): void {
        $parent = $this->fixture('local_frontpage');
        $child = $this->fixture('local_toastalerts', null, ['local_frontpage' => $parent['release']['version']]);
        $this->expectException(\moodle_exception::class);
        (new staging_manager())->stage_verified_updates([$child['file']],
            $this->catalogue([$child['release'], $parent['release']]), update_plan::fingerprint(update_plan::snapshot()));
    }

    public function test_expired_catalogue_blocks_new_staging(): void {
        $fixture = $this->fixture();
        $this->expectException(\RuntimeException::class);
        (new staging_manager())->stage_verified_updates([$fixture['file']],
            $this->catalogue([$fixture['release']], time() - 1), update_plan::fingerprint(update_plan::snapshot()));
    }

    public function test_another_session_cannot_delete_a_staged_batch(): void {
        global $SESSION;
        $manager = new staging_manager();
        $id = $this->stage([$this->fixture()]);
        $record = $SESSION->local_bulkinstall_staging[$id];
        unset($SESSION->local_bulkinstall_staging[$id]);
        try {
            $manager->cleanup($id);
            $this->fail('Foreign-session cleanup must be rejected');
        } catch (\moodle_exception $exception) {
            $this->assertSame('errorstorageexpired', $exception->errorcode);
        } finally {
            $SESSION->local_bulkinstall_staging[$id] = $record;
            $this->assertNotEmpty($manager->get_record($id));
            $manager->cleanup($id);
        }
    }

    public function test_download_retry_and_single_handover_preserve_verified_provenance(): void {
        $fixture = $this->fixture();
        set_config('updatecatalogue', $this->catalogue([$fixture['release']]), 'local_lmh108');
        $transfer = new class($fixture['file']['path']) extends update_transfer {
            public bool $wrong = true;
            private string $fixture;
            public function __construct(string $fixture) { $this->fixture = $fixture; }
            protected function fetch(string $url, string $path, int $limit): void {
                if ($this->wrong) {
                    file_put_contents($path, 'wrong hash');
                } else {
                    copy($this->fixture, $path);
                }
            }
        };
        $id = $transfer->start([$fixture['release']['component'] => $fixture['release']['version']]);
        try {
            $transfer->next($id);
            $this->fail('Wrong hash must stop the transfer');
        } catch (\RuntimeException $exception) {
            $this->assertSame([], $transfer->get($id)['files']);
        }
        $transfer->wrong = false;
        $this->assertTrue($transfer->next($id)['complete']);
        $this->assertTrue($transfer->next($id)['complete']); // Duplicate next never downloads again.
        $storage = $transfer->handover($id);
        $manager = new staging_manager();
        try {
            $this->assertTrue($manager->analyse($storage)['caninstall']);
            $this->expectExceptionMessage('expired');
            $transfer->handover($id);
        } finally {
            $manager->cleanup($storage);
        }
    }
}
