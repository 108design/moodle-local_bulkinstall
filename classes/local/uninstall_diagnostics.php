<?php
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_bulkinstall\local;

/**
 * Bounded technical diagnostics for one uninstall request, including early bootstrap failures.
 *
 * Loading this standalone class has no effect. It deliberately needs no Moodle bootstrap:
 * the entry point must be able to report a failure while loading config.php itself.
 * Messages, trace arguments, request/session data and user identifiers are never logged.
 */
final class uninstall_diagnostics {
    private static ?string $requestid = null;
    private static string $action = 'view';
    private static string $phase = 'bootstrap';
    private static ?string $component = null;
    private static bool $complete = false;
    private static bool $executing = false;
    private static bool $failed = false;
    private static bool $shutdownreported = false;
    /** @var \Throwable[] Keep exception identities for deduplication within this request only. */
    private static array $reported = [];

    /** Start once; observes shutdown without replacing Moodle's exception or PHP error handlers. */
    public static function start(): string {
        if (self::$requestid !== null) {
            return self::$requestid;
        }
        try {
            self::$requestid = bin2hex(random_bytes(8));
        } catch (\Throwable $exception) {
            self::$requestid = 'unavailable';
        }
        $action = $_POST['action'] ?? $_GET['action'] ?? 'view';
        self::$action = is_string($action) && in_array($action,
            ['view', 'preview', 'exclude', 'apply', 'cancel', 'recheck', 'finish', 'execute'], true)
                ? $action : 'unknown';
        register_shutdown_function([self::class, 'shutdown']);
        return self::$requestid;
    }

    /** Record a fixed technical stage, never raw request values. */
    public static function phase(string $phase, ?string $component = null): void {
        self::start();
        self::$phase = preg_match('/^[a-z_]{1,40}$/D', $phase) ? $phase : 'unknown';
        self::$component = $component !== null && preg_match('/^[a-z][a-z0-9]*_[a-z][a-z0-9_]*$/D', $component)
            ? substr($component, 0, 100) : null;
    }

    /** Track destructive execution so a plugin's exit/die is observable without an automatic retry. */
    public static function executing(bool $executing): void {
        self::start();
        self::$executing = $executing;
    }

    /** Successful rendering or an intentional redirect has completed this entry point. */
    public static function finish(): void {
        self::$complete = true;
        self::$executing = false;
    }

    /** Log an exception once. Logging must never hide the original failure or affect authority. */
    public static function exception(\Throwable $exception, bool $handled = false): void {
        try {
            self::start();
            foreach (self::$reported as $previous) {
                if ($previous === $exception) {
                    return;
                }
            }
            self::$failed = true;
            if (count(self::$reported) < 64) {
                self::$reported[] = $exception;
            }
            $frames = [];
            foreach (array_slice($exception->getTrace(), 0, 32) as $frame) {
                $frames[] = [
                    'file' => self::path($frame['file'] ?? ''), 'line' => (int)($frame['line'] ?? 0),
                    'call' => substr(self::classname($frame['class'] ?? '')
                        . ($frame['type'] ?? '') . ($frame['function'] ?? ''), 0, 200),
                ];
            }
            $properties = get_object_vars($exception);
            self::write([
                'kind' => 'exception', 'handled' => $handled, 'exception' => self::classname(get_class($exception)),
                'errorcode' => self::identifier($properties['errorcode'] ?? ''),
                'module' => self::identifier($properties['module'] ?? ''),
                'file' => self::path($exception->getFile()), 'line' => $exception->getLine(), 'trace' => $frames,
            ]);
        } catch (\Throwable $loggingfailure) {
            // Diagnostics are best-effort; never replace the original exception with a log failure.
        }
    }

    /** Capture fatal errors or exit/die during a batch; no raw PHP message is persisted. */
    public static function shutdown(): void {
        try {
            if (self::$shutdownreported) {
                return;
            }
            $last = error_get_last();
            $fatal = $last && in_array($last['type'],
                [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true);
            if ($fatal || (!self::$complete && !self::$failed && self::$executing)) {
                self::$shutdownreported = true;
                self::$failed = true;
                self::write(['kind' => $fatal ? 'fatal' : 'interrupted',
                    'php_error_type' => $fatal ? (int)$last['type'] : null,
                    'file' => $fatal ? self::path($last['file']) : null,
                    'line' => $fatal ? (int)$last['line'] : null]);
            }
        } catch (\Throwable $loggingfailure) {
            // A shutdown logger must not itself cause another fatal error.
        }
    }

    private static function identifier($value): ?string {
        return is_string($value) && preg_match('/^[a-zA-Z0-9_]{1,100}$/D', $value) ? $value : null;
    }

    private static function classname(string $class): string {
        // PHP appends the complete source path to anonymous class names after a NUL byte.
        return substr(explode("\0", $class, 2)[0], 0, 200);
    }

    /** Keep Moodle-relative technical paths; omit external machine/user directory prefixes. */
    private static function path(string $file): string {
        global $CFG;
        $file = str_replace('\\', '/', $file);
        foreach (['dirroot', 'root'] as $field) {
            $root = isset($CFG->$field) ? rtrim(str_replace('\\', '/', $CFG->$field), '/') . '/' : '';
            if ($root !== '' && str_starts_with($file, $root)) {
                return substr($file, strlen($root));
            }
        }
        return basename($file);
    }

    private static function write(array $event): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $event = ['request' => self::$requestid, 'action' => self::$action,
            'method' => in_array($method, ['GET', 'POST', 'CLI'], true) ? $method : 'OTHER',
            'phase' => self::$phase, 'component' => self::$component] + $event;
        $json = json_encode($event, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json !== false) {
            @error_log('[local_bulkinstall] ' . $json);
        }
    }
}
