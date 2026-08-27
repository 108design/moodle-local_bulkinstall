# Moodle plugin bundle formats 1 and 2

This document is the normative human-readable specification implemented by
`local_bulkinstall` 1.2.0. Version 1 remains accepted for administrator-created compatibility bundles. Prepared 108design downloads use signed version 2. The accompanying schemas are `schemas/bundle-v1.schema.json` and `schemas/bundle-v2.schema.json`.

## Signed prepared bundles (version 2)

Version 2 adds `publisher`, `keyid`, and `signature`. `publisher` is `108design`; `keyid` identifies the public Ed25519 key pinned in the plugin. The signature covers canonical UTF-8 JSON reconstructed from all manifest fields except `signature`, including the ordered plugin list and lower-case SHA-256 values. A missing or invalid signature blocks the complete batch before staging or installation.

The private publisher key is never included in a plugin, bundle, repository, or deployment artifact. The offline `tools/sign_plugin_bundle.php` helper signs a final manifest only after every nested plugin ZIP and SHA-256 value is final.

### Outer archive filename

Signed 108design bundles use a Frankenstyle-inspired outer archive name:

```text
bundle_<name>-<version>.zip
```

The exact lower-case `bundle_` prefix distinguishes a multi-plugin bundle from ordinary Moodle plugin packages such
as `local_example.zip` or `block_example.zip`. For format 2 the complete filename must match
`^bundle_[a-z0-9][a-z0-9._-]*\.zip$`; for example
`bundle_108design-free-starter-2026.08.24.1.zip`. The manifest `id` remains independent metadata and does not include
the prefix automatically.

Administrator-created compatibility bundles using format 1 may use any otherwise valid ZIP filename. Using the same
`bundle_` convention is recommended for clarity but is not required, so third-party and existing administrator
packages remain compatible.

## Outer ZIP layout

The bundle is one ZIP archive. Its root contains exactly:

- one file named `bundle.json` (lower case); and
- every ordinary Moodle plugin ZIP named by `plugins[].file`.

All entries must be files directly at the archive root. Directories, path
segments, additional files, duplicate entries, and entries whose names differ
only by letter case are invalid. Manifest file names are case-sensitive and
must match archive entry names exactly. The outer bundle must be uploaded by
itself and cannot be mixed with separately uploaded plugin ZIPs.

Example layout:

```text
bundle_example-course-tools-1.1.0.zip
├── bundle.json
├── mod_example.zip
└── local_examplehelper.zip
```

Contents at the archive root:

```text
bundle.json
mod_example.zip
local_examplehelper.zip
```

The manifest is UTF-8 JSON, must contain a JSON object at its top level, and is
limited to 256 KiB. Version 1 does not accept properties other than those
listed below.

## bundle.json

```json
{
  "format": "moodle-plugin-bundle",
  "formatversion": 2,
  "id": "example-course-tools",
  "name": "Example Course Tools",
  "version": "1.1.0",
  "description": "Optional description for administrators.",
  "publisher": "108design",
  "keyid": "bundle-2026-01",
  "plugins": [
    {
      "file": "mod_example.zip",
      "component": "mod_example",
      "sha256": "0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef"
    }
  ],
  "signature": "an-86-character-base64url-ed25519-signature"
}
```

## Top-level fields

| Field | Required | JSON type | Rule |
|---|---:|---|---|
| `format` | yes | string | Exactly `moodle-plugin-bundle`. |
| `formatversion` | yes | integer | `1` for compatibility bundles or `2` for signed prepared downloads. |
| `id` | yes | string | Matches `^[a-z][a-z0-9_-]{1,99}$` (2–100 ASCII characters). |
| `name` | yes | string | Non-empty; at most 200 Unicode characters. |
| `version` | yes | string | Non-empty; at most 100 Unicode characters. This is bundle metadata, not a Moodle build number. |
| `description` | no | string | At most 2,000 Unicode characters; an empty string is allowed. |
| `plugins` | yes | array | 1–50 plugin objects. |

Version 2 additionally requires `publisher`, `keyid`, and `signature` as described above. These fields are forbidden in version 1.

## Plugin fields

Every object in `plugins` contains exactly these three required properties and
no others:

| Field | JSON type | Rule |
|---|---|---|
| `file` | string | A root-level filename ending in `.zip` (case-insensitive suffix), at most 255 Unicode characters, with no slash, backslash, NUL, or U+0000–U+001F control character. Names must be unique without regard to letter case. |
| `component` | string | Matches `^[a-z][a-z0-9_]*_[a-z][a-z0-9_]*$` and is unique in the manifest. The nested package must declare exactly this Moodle component in its root `version.php`. |
| `sha256` | string | Exactly 64 hexadecimal characters. It is calculated from the unchanged bytes of the nested ZIP file; upper- or lower-case hexadecimal is accepted. |

The archive must contain exactly the files listed in `plugins`, with no missing
or unlisted ZIPs. After manifest and hash validation, each nested ZIP goes
through the same package, Moodle-version, dependency, downgrade, duplicate,
size, integrity, and Moodle core validation used for separately uploaded ZIPs.

## Limits and forward compatibility

- One bundle contains at most 50 plugin ZIPs.
- `bundle.json` is at most 256 KiB.
- The nested ZIP files may occupy at most 512 MiB in total after the outer ZIP
  is unpacked.
- Existing per-package and batch limits documented in the plugin README also
  apply after extraction.
- A consumer must reject an unsupported `formatversion`; it must not guess how
  to interpret a newer manifest.
- JSON member names are case-sensitive.
- The runtime additionally enforces cross-item uniqueness for `file` and
  `component`; standard JSON Schema cannot express these property-based
  uniqueness constraints directly.

## Creating SHA-256 values

Calculate the digest after the individual Moodle plugin ZIP is final. Do not
modify or recreate that ZIP after writing the digest into `bundle.json`. Then
put the unchanged plugin ZIP and manifest into the outer bundle ZIP.
