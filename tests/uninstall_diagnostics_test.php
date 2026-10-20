<?php
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_bulkinstall;

use local_bulkinstall\local\uninstall_diagnostics;

/** Technical logging tests need no database fixtures or reset. */
final class uninstall_diagnostics_test extends \PHPUnit\Framework\TestCase {
    private string $directory;
    private string $log;
    private string $previouslog;
    private array $previouspost;
    private array $previousget;
    private array $previousserver;

    protected function setUp(): void {
        global $CFG;
        parent::setUp();
        $this->directory = $CFG->tempdir . '/local_bulkinstall_diagnostics_' . bin2hex(random_bytes(6));
        mkdir($this->directory, 0700, true);
        $this->log = $this->directory . '/error.log';
        $this->previouslog = ini_get('error_log');
        ini_set('error_log', $this->log);
        $this->previouspost = $_POST; $this->previousget = $_GET; $this->previousserver = $_SERVER;
        $_POST = ['action' => 'execute', 'token' => 'private-token'];
        $_GET = ['sesskey' => 'private-sesskey'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $defaults = (new \ReflectionClass(uninstall_diagnostics::class))->getDefaultProperties();
        foreach ($defaults as $name => $value) {
            $property = new \ReflectionProperty(uninstall_diagnostics::class, $name);
            $property->setValue(null, $value);
        }
    }

    protected function tearDown(): void {
        uninstall_diagnostics::finish();
        ini_set('error_log', $this->previouslog);
        $_POST = $this->previouspost; $_GET = $this->previousget; $_SERVER = $this->previousserver;
        if (is_file($this->log)) { unlink($this->log); }
        rmdir($this->directory);
        parent::tearDown();
    }

    private function records(): array {
        $records = [];
        foreach (file($this->log, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            if (preg_match('/\[local_bulkinstall\] (.*)$/', $line, $match)) {
                $records[] = json_decode($match[1], true, 512, JSON_THROW_ON_ERROR);
            }
        }
        return $records;
    }

    private function sensitive(string $secret): void {
        throw new \RuntimeException($secret);
    }

    public function test_context_without_request_secrets_or_exception_arguments(): void {
        uninstall_diagnostics::phase('native_uninstall', 'local_example');
        try { $this->sensitive('private-message-and-argument'); } catch (\Throwable $exception) {
            uninstall_diagnostics::exception($exception, true);
        }
        $records = $this->records();
        $this->assertCount(1, $records);
        $this->assertSame('native_uninstall', $records[0]['phase']);
        $this->assertSame('local_example', $records[0]['component']);
        $this->assertTrue($records[0]['handled']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{16}$/', $records[0]['request']);
        $raw = file_get_contents($this->log);
        foreach (['private-token', 'private-sesskey', 'private-message-and-argument', '"args"', '"userid"'] as $value) {
            $this->assertStringNotContainsString($value, $raw);
        }
    }

    public function test_same_exception_is_not_logged_twice(): void {
        $exception = new \RuntimeException('not logged');
        uninstall_diagnostics::exception($exception);
        uninstall_diagnostics::exception($exception);
        $this->assertCount(1, $this->records());
    }

    public function test_distinct_failures_remain_observable(): void {
        uninstall_diagnostics::exception(new \RuntimeException('first'));
        uninstall_diagnostics::exception(new \RuntimeException('second'));
        $this->assertCount(2, $this->records());
    }

    public function test_public_moodle_error_identity_and_anonymous_class_path(): void {
        $exception = new class('not logged') extends \RuntimeException {
            public string $errorcode = 'uninstallstale';
            public string $module = 'local_bulkinstall';
        };
        uninstall_diagnostics::exception($exception);
        $record = $this->records()[0];
        $this->assertSame('uninstallstale', $record['errorcode']);
        $this->assertSame('local_bulkinstall', $record['module']);
        $this->assertStringNotContainsString(__FILE__, $record['exception']);
        $this->assertStringNotContainsString("\0", $record['exception']);
    }

    public function test_untrusted_context_is_not_copied_into_log(): void {
        $_POST['action'] = "secret\nforged";
        uninstall_diagnostics::phase('secret invalid phase', '../../private-component');
        uninstall_diagnostics::exception(new \RuntimeException());
        $record = $this->records()[0];
        $this->assertSame('unknown', $record['action']);
        $this->assertSame('unknown', $record['phase']);
        $this->assertNull($record['component']);
    }

    public function test_logging_failure_does_not_replace_original_error(): void {
        ini_set('error_log', $this->directory);
        set_error_handler(static function(): void { throw new \RuntimeException('Log write failed'); });
        try {
            uninstall_diagnostics::exception(new \RuntimeException('Original error'));
            $this->assertTrue(true); // A failing PHP logger must not escape into the uninstall flow.
        } finally {
            restore_error_handler();
        }
    }

    private function deep(int $depth): void {
        if ($depth) { $this->deep($depth - 1); } else { throw new \RuntimeException(); }
    }

    public function test_trace_is_bounded_and_has_only_technical_frame_fields(): void {
        try { $this->deep(45); } catch (\Throwable $exception) { uninstall_diagnostics::exception($exception); }
        $trace = $this->records()[0]['trace'];
        $this->assertCount(32, $trace);
        foreach ($trace as $frame) { $this->assertSame(['file', 'line', 'call'], array_keys($frame)); }
    }

    public function test_normal_completion_does_not_emit_interruption(): void {
        uninstall_diagnostics::executing(true);
        uninstall_diagnostics::finish();
        uninstall_diagnostics::shutdown();
        $this->assertFileDoesNotExist($this->log);
    }
}
