<?php

return [
	'page-document' => [
		'status'        => 'planned',
		'driver'        => 'sql-json-column',
		'repository'    => 'page versions repository',
		'read_model'    => 'Workspace hydrates the active page version as the canonical page document.',
		'write_model'   => 'Validated page document is written back as versioned schema JSON.',
		'bridge_source' => 'landingbuilder.page runtime payload',
		'notes'         => [
			'page document remains the editor and runtime source of truth',
			'publish flow must operate on versioned page documents'
		]
	],
	'block-manifest' => [
		'status'        => 'registry-live',
		'driver'        => 'file-registry',
		'repository'    => 'component library registry',
		'read_model'    => 'Editor library and runtime validation read manifests from the registry.',
		'write_model'   => 'New block manifests are added in package/live source and promoted through package sync.',
		'bridge_source' => 'landingbuilder block definitions',
		'notes'         => [
			'current source is file-based registry',
			'page-document validation already uses this registry for block props',
			'future persistence may add admin-managed registry records without changing the contract'
		]
	],
	'adapter-manifest' => [
		'status'        => 'registry-stub',
		'driver'        => 'file-registry',
		'repository'    => 'adapter registry',
		'read_model'    => 'Builder routing and runtime matching read adapter manifests from the registry.',
		'write_model'   => 'Adapter manifests are versioned in source until a dedicated registry UI appears.',
		'bridge_source' => 'landingbuilder adapter bindings',
		'notes'         => [
			'matching rules stay declarative',
			'adapter runtime handler refs must stay stable across releases'
		]
	],
	'preset-token' => [
		'status'        => 'planned',
		'driver'        => 'sql-json-column',
		'repository'    => 'preset token repository',
		'read_model'    => 'Token layers are resolved in inheritance order before workspace preview and runtime render.',
		'write_model'   => 'Preset documents are saved as reusable token JSON layers.',
		'bridge_source' => 'landingbuilder theme and preset payloads',
		'notes'         => [
			'token layers must stay independent from page structure',
			'global defaults and page presets should share one contract family'
		]
	],
	'binding-options' => [
		'status'        => 'planned',
		'driver'        => 'json-fragment',
		'repository'    => 'binding options repository',
		'read_model'    => 'Binding matching and fallback rules are loaded with the page document for preview and runtime.',
		'write_model'   => 'Binding options are saved per binding after schema validation.',
		'bridge_source' => 'landingbuilder binding runtime options',
		'notes'         => [
			'binding options must not duplicate structural page data',
			'zone participation must remain aligned with adapter manifests'
		]
	]
];