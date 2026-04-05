<?php

class modelNordicbuilder extends cmsModel {

	const PAGE_DOCUMENT_TABLE = 'nordicbuilder_page_documents';
	const PRESET_TOKEN_TABLE = 'nordicbuilder_preset_tokens';
	const BINDING_OPTIONS_TABLE = 'nordicbuilder_binding_options';

	public function hasInstalledSchema() {
		return $this->hasPersistenceTables();
	}

	public function getContractsRegistry() {
		$contracts = [];

		foreach (glob($this->getContractsDir() . '/*.json') as $file_path) {
			$contracts[] = $this->readContractDefinition($file_path);
		}

		usort($contracts, function ($left, $right) {
			return strcmp($left['title'], $right['title']);
		});

		return $contracts;
	}

	public function getWorkspaceSummary() {
		$contracts = $this->getContractsRegistry();
		$valid_contracts_count = 0;

		foreach ($contracts as $contract) {
			if (!empty($contract['is_valid'])) {
				$valid_contracts_count++;
			}
		}

		return [
			'contracts_count'       => count($contracts),
			'valid_contracts_count' => $valid_contracts_count,
			'persistence'           => $this->getPersistenceSummary(),
			'migration'             => $this->getLandingbuilderMigrationSummary(),
			'page_documents'        => $this->getPageDocumentsForWorkspace()
		];
	}

	public function getPageDocumentsForWorkspace() {
		if (!$this->db->isTableExists(self::PAGE_DOCUMENT_TABLE)) {
			return [];
		}

		$documents = $this->orderBy('updated_at', 'desc')->get(self::PAGE_DOCUMENT_TABLE, function ($item) {
			return [
				'key'            => $item['document_key'],
				'title'          => $item['title'],
				'page_type'      => $item['page_type'],
				'editor_mode'    => $item['editor_mode'],
				'status'         => $item['status'],
				'schema_version' => $item['schema_version'],
				'updated_at'     => $item['updated_at']
			];
		});

		return $documents ? array_values($documents) : [];
	}

	public function getLandingbuilderPagesForImport() {
		$landingbuilder = cmsCore::getModel('landingbuilder');

		if (!$landingbuilder || !method_exists($landingbuilder, 'getPagesForAdmin')) {
			return [];
		}

		$pages = $landingbuilder->getPagesForAdmin();
		if (!$pages) {
			return [];
		}

		$stored_documents_index = $this->getStoredPageDocumentIndex();

		$pages = array_map(function ($page) use ($stored_documents_index) {
			$key = (string) ($page['key'] ?? '');
			$stored_document = $stored_documents_index[$key] ?? null;

			return [
				'key'               => $key,
				'title'             => (string) ($page['title'] ?? ''),
				'mode'              => (string) ($page['mode'] ?? ''),
				'page_type'         => (string) ($page['page_type'] ?? ''),
				'adapter_key'       => (string) ($page['adapter_key'] ?? ''),
				'status'            => (string) ($page['status'] ?? 'draft'),
				'is_imported'       => (bool) $stored_document,
				'document_status'   => (string) ($stored_document['status'] ?? ''),
				'document_updated_at'=> (string) ($stored_document['updated_at'] ?? ''),
				'migration_state'   => $stored_document ? 'imported' : 'pending'
			];
		}, $pages);

		usort($pages, function ($left, $right) {
			if ((int) $left['is_imported'] === (int) $right['is_imported']) {
				return strcmp($left['title'], $right['title']);
			}

			return $left['is_imported'] ? 1 : -1;
		});

		return $pages;
	}

	public function getLandingbuilderMigrationSummary() {
		$pages = $this->getLandingbuilderPagesForImport();
		$summary = [
			'total_pages'    => count($pages),
			'imported_count' => 0,
			'pending_count'  => 0,
			'pages'          => $pages
		];

		foreach ($pages as $page) {
			if (!empty($page['is_imported'])) {
				$summary['imported_count']++;
			} else {
				$summary['pending_count']++;
			}
		}

		return $summary;
	}

	public function getContractDetails($key) {
		foreach (glob($this->getContractsDir() . '/*.json') as $file_path) {
			$contract = $this->readContractDefinition($file_path);
			$base_name = pathinfo(basename($file_path), PATHINFO_FILENAME);

			if ($contract['key'] !== $key && $base_name !== $key) {
				continue;
			}

			$contract['source_path'] = $this->buildContractSourcePath($contract['file_name']);
			$contract['raw_definition'] = $this->getContractRawDefinition($file_path);
			$contract['storage_stub'] = $this->getContractStorageStub($contract);

			return $contract;
		}

		return null;
	}

	public function getPageDocumentByKey($key) {
		$key = $this->sanitizeDocumentKey($key);
		if ($key === '') {
			return false;
		}

		$item = $this->getStoredContractByKey(self::PAGE_DOCUMENT_TABLE, 'document_key', $key);

		if (!$item) {
			return false;
		}

		$item['document'] = $this->decodeStoredJson($item['schema_json']);

		return $item;
	}

	public function getBridgePageForLandingbuilder($page_key, array $fallback_page = []) {
		$stored_document = $this->getPageDocumentByKey($page_key);

		if (!$stored_document) {
			return false;
		}

		$document = isset($stored_document['document']) && is_array($stored_document['document']) ? $stored_document['document'] : [];
		$schema = $this->buildLandingbuilderSchemaFromPageDocument($document, $fallback_page['schema'] ?? []);

		$page = array_merge($fallback_page, [
			'id'              => (int) ($fallback_page['id'] ?? 0),
			'key'             => (string) ($document['key'] ?? $page_key),
			'name'            => (string) ($document['key'] ?? $page_key),
			'title'           => (string) ($document['title'] ?? ($fallback_page['title'] ?? $page_key)),
			'status'          => (string) ($stored_document['status'] ?? ($fallback_page['status'] ?? 'draft')),
			'mode'            => $this->resolveLandingbuilderModeFromDocument($document, $fallback_page['mode'] ?? 'full_takeover'),
			'page_mode'       => $this->resolveLandingbuilderModeFromDocument($document, $fallback_page['page_mode'] ?? 'full_takeover'),
			'template'        => (string) (($document['layout']['template'] ?? '') ?: ($document['meta']['template'] ?? ($fallback_page['template'] ?? 'nordic'))),
			'page_type'       => (string) ($document['page_type'] ?? ($fallback_page['page_type'] ?? 'standalone')),
			'adapter_key'     => (string) (($document['meta']['adapter_key'] ?? '') ?: ($fallback_page['adapter_key'] ?? '')),
			'updated_at'      => (string) ($stored_document['updated_at'] ?? ($fallback_page['updated_at'] ?? date('Y-m-d H:i:s'))),
			'schema'          => $schema,
			'bridge_source'   => 'nordicbuilder',
			'storage_backend' => 'nordicbuilder'
		]);

		$page['page_mode'] = $page['mode'];

		return $page;
	}

	public function saveBridgePageSchema($page_key, array $schema, array $fallback_page = [], $user_id = 0, $status = 'draft') {
		$page = array_merge($fallback_page, [
			'key'         => $page_key,
			'name'        => $page_key,
			'title'       => (string) ($fallback_page['title'] ?? $page_key),
			'status'      => (string) ($fallback_page['status'] ?? $status),
			'mode'        => (string) ($fallback_page['mode'] ?? 'full_takeover'),
			'page_type'   => (string) ($fallback_page['page_type'] ?? 'standalone'),
			'template'    => (string) ($fallback_page['template'] ?? 'nordic'),
			'adapter_key' => (string) ($fallback_page['adapter_key'] ?? ''),
			'schema'      => $schema
		]);

		$document = $this->buildPageDocumentFromLandingbuilder($page);

		return $this->savePageDocument($document, $user_id, (string) ($status ?: ($fallback_page['status'] ?? 'draft')));
	}

	public function getPresetTokenByKey($key) {
		$key = $this->sanitizeDocumentKey($key);
		if ($key === '') {
			return false;
		}

		$item = $this->getStoredContractByKey(self::PRESET_TOKEN_TABLE, 'preset_key', $key);

		if (!$item) {
			return false;
		}

		$item['document'] = $this->decodeStoredJson($item['tokens_json']);

		return $item;
	}

	public function getBindingOptionsByKey($key) {
		$key = $this->sanitizeDocumentKey($key);
		if ($key === '') {
			return false;
		}

		$item = $this->getStoredContractByKey(self::BINDING_OPTIONS_TABLE, 'binding_key', $key);

		if (!$item) {
			return false;
		}

		$item['document'] = $this->decodeStoredJson($item['options_json']);

		return $item;
	}

	public function savePageDocument(array $document, $user_id = 0, $status = 'draft') {
		$normalized = $this->normalizePageDocument($document);

		return $this->saveStoredContract('page-document', self::PAGE_DOCUMENT_TABLE, 'document_key', $normalized, [
			'title'          => (string) ($document['title'] ?? ''),
			'schema_version' => (string) ($normalized['schema_version'] ?? ''),
			'page_type'      => (string) ($normalized['page_type'] ?? ''),
			'editor_mode'    => (string) ($normalized['editor_mode'] ?? ''),
			'status'         => (string) ($status ?: 'draft'),
			'schema_json'    => $this->encodeStoredJson($normalized)
		], $user_id);
	}

	public function importLandingbuilderPage($page_key, $user_id = 0, array $options = []) {
		$page_key = $this->sanitizeDocumentKey($page_key);
		$overwrite_existing = array_key_exists('overwrite_existing', $options) ? (bool) $options['overwrite_existing'] : true;

		if ($page_key === '') {
			return [
				'is_valid' => false,
				'errors'   => ['page_key' => 'Page key is empty.']
			];
		}

		$existing_document = $this->getPageDocumentByKey($page_key);
		if ($existing_document && !$overwrite_existing) {
			return [
				'is_valid'   => true,
				'is_skipped' => true,
				'page_key'   => $page_key,
				'title'      => (string) ($existing_document['title'] ?? $page_key),
				'errors'     => [],
				'message'    => 'Page document already exists in nordicbuilder.'
			];
		}

		$page_result = $this->resolveLandingbuilderMigrationPage($page_key);
		if (empty($page_result['is_valid'])) {
			return $page_result;
		}

		$page = $page_result['page'];
		$document = $this->buildPageDocumentFromLandingbuilder($page);
		$result = $this->savePageDocument($document, $user_id, (string) ($page['status'] ?? 'draft'));

		$result['page_key'] = $page_key;
		$result['title'] = (string) ($page['title'] ?? $page_key);
		$result['is_skipped'] = false;
		$result['message'] = $existing_document
			? 'Page document updated from legacy landingbuilder.'
			: 'Page document imported from landingbuilder.';

		return $result;
	}

	public function importLandingbuilderPagesBulk(array $page_keys = [], $user_id = 0, array $options = []) {
		$overwrite_existing = !empty($options['overwrite_existing']);
		$pages = $this->getLandingbuilderPagesForImport();
		$page_map = [];

		foreach ($pages as $page) {
			if (!empty($page['key'])) {
				$page_map[$page['key']] = $page;
			}
		}

		if (!$page_keys) {
			foreach ($pages as $page) {
				if ($overwrite_existing || empty($page['is_imported'])) {
					$page_keys[] = $page['key'];
				}
			}
		}

		$page_keys = array_values(array_unique(array_filter(array_map([$this, 'sanitizeDocumentKey'], $page_keys))));
		$items = [];
		$imported_count = 0;
		$skipped_count = 0;
		$failed_count = 0;

		foreach ($page_keys as $page_key) {
			$page_meta = $page_map[$page_key] ?? null;

			if (!$page_meta) {
				$failed_count++;
				$items[] = [
					'key'     => $page_key,
					'title'   => $page_key,
					'status'  => 'failed',
					'message' => 'Landingbuilder page is missing in migration catalog.'
				];
				continue;
			}

			$result = $this->importLandingbuilderPage($page_key, $user_id, [
				'overwrite_existing' => $overwrite_existing
			]);

			if (!empty($result['is_valid']) && empty($result['is_skipped'])) {
				$imported_count++;
				$items[] = [
					'key'     => $page_key,
					'title'   => (string) ($result['title'] ?? $page_meta['title'] ?? $page_key),
					'status'  => 'imported',
					'message' => (string) ($result['message'] ?? 'Page document imported.')
				];
				continue;
			}

			if (!empty($result['is_skipped'])) {
				$skipped_count++;
				$items[] = [
					'key'     => $page_key,
					'title'   => (string) ($result['title'] ?? $page_meta['title'] ?? $page_key),
					'status'  => 'skipped',
					'message' => (string) ($result['message'] ?? 'Page skipped.')
				];
				continue;
			}

			$failed_count++;
			$items[] = [
				'key'     => $page_key,
				'title'   => (string) ($page_meta['title'] ?? $page_key),
				'status'  => 'failed',
				'message' => $this->stringifyMigrationErrors((array) ($result['errors'] ?? []))
			];
		}

		return [
			'is_valid' => $failed_count === 0,
			'summary'  => [
				'requested_count'   => count($page_keys),
				'imported_count'    => $imported_count,
				'skipped_count'     => $skipped_count,
				'failed_count'      => $failed_count,
				'overwrite_existing'=> $overwrite_existing
			],
			'items'    => $items
		];
	}

	protected function resolveLandingbuilderMigrationPage($page_key) {
		$landingbuilder = cmsCore::getModel('landingbuilder');

		if (!$landingbuilder || (!method_exists($landingbuilder, 'getPageForMigration') && !method_exists($landingbuilder, 'getPageByKey'))) {
			return [
				'is_valid' => false,
				'errors'   => ['bridge' => 'Landingbuilder bridge model is unavailable.']
			];
		}

		$page = method_exists($landingbuilder, 'getPageForMigration')
			? $landingbuilder->getPageForMigration($page_key)
			: $landingbuilder->getPageByKey($page_key);

		if (!$page) {
			return [
				'is_valid' => false,
				'errors'   => ['page_key' => 'Landingbuilder page not found.']
			];
		}

		return [
			'is_valid' => true,
			'page'     => $page
		];
	}

	public function savePresetToken(array $document, $user_id = 0) {
		return $this->saveStoredContract('preset-token', self::PRESET_TOKEN_TABLE, 'preset_key', $document, [
			'title'          => (string) ($document['title'] ?? ''),
			'scope'          => (string) ($document['scope'] ?? ''),
			'schema_version' => (string) ($document['schema_version'] ?? ''),
			'tokens_json'    => $this->encodeStoredJson($document)
		], $user_id);
	}

	public function saveBindingOptions($binding_key, array $document, $user_id = 0) {
		if (empty($document['key'])) {
			$document['key'] = $binding_key;
		}

		return $this->saveStoredContract('binding-options', self::BINDING_OPTIONS_TABLE, 'binding_key', $document, [
			'title'          => (string) ($document['title'] ?? ''),
			'page_key'       => (string) ($document['page_key'] ?? ''),
			'schema_version' => (string) ($document['schema_version'] ?? ''),
			'options_json'   => $this->encodeStoredJson($document)
		], $user_id);
	}

	public function validateContractPayload($contract_key, array $payload) {
		$contract = $this->getContractDetails($contract_key);

		if (!$contract) {
			return [
				'is_valid' => false,
				'errors'   => ['contract' => 'Contract is not registered.'],
				'contract' => null
			];
		}

		$errors = [];

		foreach ($contract['required_fields'] as $field) {
			if (!array_key_exists($field, $payload)) {
				$errors[$field] = 'Required field is missing.';
				continue;
			}

			if (!$this->hasRequiredContractValue($payload[$field])) {
				$errors[$field] = 'Required field is empty.';
			}
		}

		if ($contract_key === 'page-document') {
			$errors = array_merge($errors, $this->validatePageDocumentStructure($payload));
		}

		return [
			'is_valid' => empty($errors),
			'errors'   => $errors,
			'contract' => $contract
		];
	}

	public function getPersistenceSummary() {
		$tables = [];
		$installed_tables_count = 0;

		foreach ($this->getPersistenceTablesConfig() as $table) {
			$is_installed = $this->db->isTableExists($table['table_name']);
			if ($is_installed) {
				$installed_tables_count++;
			}

			$tables[] = [
				'title'          => $table['title'],
				'table_name'     => $table['table_name'],
				'contract_key'   => $table['contract_key'],
				'storage_target' => $table['storage_target'],
				'is_installed'   => $is_installed,
				'count'          => $is_installed ? (int) $this->getCount($table['table_name'], 'id', true) : 0
			];
		}

		return [
			'is_installed'           => $installed_tables_count === count($tables),
			'installed_tables_count' => $installed_tables_count,
			'total_tables_count'     => count($tables),
			'tables'                 => $tables
		];
	}

	protected function getContractsDir() {
		return __DIR__ . '/data/contracts';
	}

	protected function getStorageStubPath() {
		return __DIR__ . '/data/storage.stub.php';
	}

	protected function getPersistenceTablesConfig() {
		return [
			[
				'title'          => 'Page Documents',
				'table_name'     => self::PAGE_DOCUMENT_TABLE,
				'contract_key'   => 'page-document',
				'storage_target' => 'page_versions.schema_json'
			],
			[
				'title'          => 'Preset Tokens',
				'table_name'     => self::PRESET_TOKEN_TABLE,
				'contract_key'   => 'preset-token',
				'storage_target' => 'presets.tokens_json'
			],
			[
				'title'          => 'Binding Options',
				'table_name'     => self::BINDING_OPTIONS_TABLE,
				'contract_key'   => 'binding-options',
				'storage_target' => 'bindings.options_json'
			]
		];
	}

	protected function normalizePageDocument(array $document) {
		$document['kind'] = !empty($document['kind']) ? (string) $document['kind'] : 'nordicbuilder.contract';
		$document['schema_version'] = !empty($document['schema_version']) ? (string) $document['schema_version'] : '1.0';
		$document['key'] = $this->sanitizeDocumentKey($document['key'] ?? '');
		$document['title'] = trim((string) ($document['title'] ?? ''));
		$document['page_type'] = !empty($document['page_type']) ? (string) $document['page_type'] : 'standalone';
		$document['editor_mode'] = !empty($document['editor_mode']) ? (string) $document['editor_mode'] : 'canvas';
		$document['meta'] = isset($document['meta']) && is_array($document['meta']) ? $document['meta'] : [];
		$document['theme'] = isset($document['theme']) && is_array($document['theme']) ? $document['theme'] : [];
		$document['layout'] = isset($document['layout']) && is_array($document['layout']) ? $document['layout'] : [];
		$document['shell_slots'] = isset($document['shell_slots']) && is_array($document['shell_slots']) ? array_values($document['shell_slots']) : [];
		$document['zones'] = isset($document['zones']) && is_array($document['zones']) ? array_values($document['zones']) : [];

		return $document;
	}

	protected function hasPersistenceTables() {
		foreach ($this->getPersistenceTablesConfig() as $table) {
			if (!$this->db->isTableExists($table['table_name'])) {
				return false;
			}
		}

		return true;
	}

	protected function readContractDefinition($file_path) {
		$file_name = basename($file_path);
		$base_name = pathinfo($file_name, PATHINFO_FILENAME);
		$raw = @file_get_contents($file_path);

		if ($raw === false) {
			return $this->buildInvalidContract($base_name, $file_name, 'Unable to read file');
		}

		$decoded = json_decode($raw, true);

		if (!is_array($decoded)) {
			return $this->buildInvalidContract($base_name, $file_name, json_last_error_msg());
		}

		return [
			'is_valid'        => isset($decoded['kind'], $decoded['schema_version'], $decoded['key'], $decoded['title']),
			'file_name'       => $file_name,
			'source_path'     => $this->buildContractSourcePath($file_name),
			'key'             => $decoded['key'] ?? $base_name,
			'title'           => $decoded['title'] ?? $base_name,
			'kind'            => $decoded['kind'] ?? '',
			'schema_version'  => $decoded['schema_version'] ?? '',
			'contract_kind'   => $decoded['contract_kind'] ?? '',
			'bridge_kind'     => $decoded['bridge_kind'] ?? '',
			'storage_target'  => $decoded['storage_target'] ?? '',
			'description'     => $decoded['description'] ?? '',
			'required_fields' => array_values($decoded['required_fields'] ?? []),
			'notes'           => array_values($decoded['notes'] ?? []),
			'error'           => ''
		];
	}

	protected function buildInvalidContract($base_name, $file_name, $error_message) {
		return [
			'is_valid'        => false,
			'file_name'       => $file_name,
			'source_path'     => $this->buildContractSourcePath($file_name),
			'key'             => $base_name,
			'title'           => $base_name,
			'kind'            => '',
			'schema_version'  => '',
			'contract_kind'   => '',
			'bridge_kind'     => '',
			'storage_target'  => '',
			'description'     => '',
			'required_fields' => [],
			'notes'           => [],
			'error'           => $error_message
		];
	}

	protected function saveStoredContract($contract_key, $table_name, $key_field, array $document, array $storage_data, $user_id = 0) {
		$validation = $this->validateContractPayload($contract_key, $document);

		if (!$validation['is_valid']) {
			return $validation;
		}

		if (!$this->db->isTableExists($table_name)) {
			return [
				'is_valid' => false,
				'errors'   => ['storage' => 'Persistence table is not installed.'],
				'contract' => $validation['contract']
			];
		}

		$key = $this->sanitizeDocumentKey($document['key'] ?? '');

		if ($key === '') {
			return [
				'is_valid' => false,
				'errors'   => ['key' => 'Document key is empty or invalid.'],
				'contract' => $validation['contract']
			];
		}

		$now = date('Y-m-d H:i:s');
		$data = array_merge($storage_data, [
			$key_field   => $key,
			'updated_by' => (int) $user_id,
			'updated_at' => $now
		]);

		$existing = $this->getItemByField($table_name, $key_field, $key);

		if ($existing) {
			$this->update($table_name, $existing['id'], $data);
		} else {
			$data['created_at'] = $now;
			$this->insert($table_name, $data);
		}

		return [
			'is_valid' => true,
			'errors'   => [],
			'contract' => $validation['contract']
		];
	}

	protected function validatePageDocumentStructure(array $payload) {
		$errors = [];

		$allowed_page_types = ['standalone', 'system_overlay', 'ctype_overlay'];
		if (!empty($payload['page_type']) && !in_array($payload['page_type'], $allowed_page_types, true)) {
			$errors['page_type'] = 'Unsupported page_type.';
		}

		$allowed_editor_modes = ['canvas', 'overlay'];
		if (!empty($payload['editor_mode']) && !in_array($payload['editor_mode'], $allowed_editor_modes, true)) {
			$errors['editor_mode'] = 'Unsupported editor_mode.';
		}

		if (empty($payload['kind']) || !in_array($payload['kind'], ['nordicbuilder.page', 'nordicbuilder.contract'], true)) {
			$errors['kind'] = 'Unsupported page document kind.';
		}

		if (!isset($payload['meta']) || !is_array($payload['meta'])) {
			$errors['meta'] = 'Meta must be an object.';
		}

		if (isset($payload['theme']) && !is_array($payload['theme'])) {
			$errors['theme'] = 'Theme must be an object.';
		}

		if (isset($payload['layout']) && !is_array($payload['layout'])) {
			$errors['layout'] = 'Layout must be an object.';
		}

		if (!isset($payload['zones']) || !is_array($payload['zones']) || !$payload['zones']) {
			$errors['zones'] = 'Zones must contain at least one zone.';
			return $errors;
		}

		foreach ($payload['zones'] as $zone_index => $zone) {
			if (!is_array($zone)) {
				$errors['zones.' . $zone_index] = 'Zone must be an object.';
				continue;
			}

			if (empty($zone['zone_key'])) {
				$errors['zones.' . $zone_index . '.zone_key'] = 'Zone key is required.';
			}

			if (!isset($zone['sections']) || !is_array($zone['sections'])) {
				$errors['zones.' . $zone_index . '.sections'] = 'Zone sections must be an array.';
				continue;
			}

			foreach ($zone['sections'] as $section_index => $section) {
				if (!is_array($section)) {
					$errors['zones.' . $zone_index . '.sections.' . $section_index] = 'Section must be an object.';
					continue;
				}

				if (empty($section['uid'])) {
					$errors['zones.' . $zone_index . '.sections.' . $section_index . '.uid'] = 'Section uid is required.';
				}

				if (empty($section['title'])) {
					$errors['zones.' . $zone_index . '.sections.' . $section_index . '.title'] = 'Section title is required.';
				}

				if (!isset($section['columns']) || !is_array($section['columns']) || !$section['columns']) {
					$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns'] = 'Section columns must be a non-empty array.';
					continue;
				}

				foreach ($section['columns'] as $column_index => $column) {
					if (!is_array($column)) {
						$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns.' . $column_index] = 'Column must be an object.';
						continue;
					}

					if (empty($column['uid'])) {
						$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns.' . $column_index . '.uid'] = 'Column uid is required.';
					}

					if (!isset($column['nodes']) || !is_array($column['nodes'])) {
						$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns.' . $column_index . '.nodes'] = 'Column nodes must be an array.';
						continue;
					}

					foreach ($column['nodes'] as $node_index => $node) {
						if (!is_array($node)) {
							$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns.' . $column_index . '.nodes.' . $node_index] = 'Node must be an object.';
							continue;
						}

						if (empty($node['uid'])) {
							$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns.' . $column_index . '.nodes.' . $node_index . '.uid'] = 'Node uid is required.';
						}

						$node_type = (string) ($node['type'] ?? '');
						if (!in_array($node_type, ['block', 'system_widget'], true)) {
							$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns.' . $column_index . '.nodes.' . $node_index . '.type'] = 'Unsupported node type.';
						}

						if ($node_type === 'block' && empty($node['label']) && empty($node['source_key'])) {
							$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns.' . $column_index . '.nodes.' . $node_index . '.label'] = 'Block node must define label or source_key.';
						}

						if ($node_type === 'system_widget' && empty($node['label']) && empty($node['widget_name'])) {
							$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns.' . $column_index . '.nodes.' . $node_index . '.widget_name'] = 'System widget node must define label or widget_name.';
						}

						if (isset($node['options']) && !is_array($node['options'])) {
							$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns.' . $column_index . '.nodes.' . $node_index . '.options'] = 'Node options must be an object.';
						}
					}
				}
			}
		}

		return $errors;
	}

	protected function buildPageDocumentFromLandingbuilder(array $page) {
		$schema = isset($page['schema']) && is_array($page['schema']) ? $page['schema'] : [];
		$sections = isset($schema['sections']) && is_array($schema['sections']) ? $schema['sections'] : [];

		$zone_map = [];
		foreach ($sections as $section) {
			$zone_key = !empty($section['zone_key']) ? (string) $section['zone_key'] : 'content_body';

			if (!isset($zone_map[$zone_key])) {
				$zone_map[$zone_key] = [
					'zone_key' => $zone_key,
					'title'    => $zone_key,
					'sections' => []
				];
			}

			$zone_map[$zone_key]['sections'][] = $section;
		}

		if (!$zone_map) {
			$zone_map['content_body'] = [
				'zone_key' => 'content_body',
				'title'    => 'content_body',
				'sections' => []
			];
		}

		return $this->normalizePageDocument([
			'kind'           => 'nordicbuilder.page',
			'schema_version' => (string) ($schema['schema_version'] ?? '1.0'),
			'key'            => (string) ($page['key'] ?? $page['name'] ?? ''),
			'title'          => (string) ($page['title'] ?? ''),
			'page_type'      => (string) ($page['page_type'] ?? 'standalone'),
			'editor_mode'    => (string) (($page['page_type'] ?? 'standalone') === 'standalone' ? 'canvas' : 'overlay'),
			'meta'           => [
				'bridge_source' => 'landingbuilder',
				'page_mode'     => (string) ($page['mode'] ?? ''),
				'template'      => (string) ($page['template'] ?? 'nordic'),
				'adapter_key'   => (string) ($page['adapter_key'] ?? '')
			],
			'theme'          => isset($schema['theme']) && is_array($schema['theme']) ? $schema['theme'] : [],
			'layout'         => isset($schema['layout']) && is_array($schema['layout']) ? $schema['layout'] : [],
			'shell_slots'    => isset($schema['shell_slots']) && is_array($schema['shell_slots']) ? array_values($schema['shell_slots']) : [],
			'zones'          => array_values($zone_map)
		]);
	}

	protected function buildLandingbuilderSchemaFromPageDocument(array $document, array $fallback_schema = []) {
		$schema = is_array($fallback_schema) ? $fallback_schema : [];
		$schema['schema_version'] = (string) ($document['schema_version'] ?? ($schema['schema_version'] ?? '1.0'));
		$schema['theme'] = isset($document['theme']) && is_array($document['theme']) ? $document['theme'] : (isset($schema['theme']) && is_array($schema['theme']) ? $schema['theme'] : []);
		$schema['layout'] = isset($document['layout']) && is_array($document['layout']) ? $document['layout'] : (isset($schema['layout']) && is_array($schema['layout']) ? $schema['layout'] : []);
		$schema['shell_slots'] = isset($document['shell_slots']) && is_array($document['shell_slots']) ? array_values($document['shell_slots']) : (isset($schema['shell_slots']) && is_array($schema['shell_slots']) ? array_values($schema['shell_slots']) : []);
		$schema['sections'] = [];

		foreach ((array) ($document['zones'] ?? []) as $zone) {
			foreach ((array) ($zone['sections'] ?? []) as $section) {
				if (!is_array($section)) {
					continue;
				}

				if (empty($section['zone_key'])) {
					$section['zone_key'] = (string) ($zone['zone_key'] ?? 'content_body');
				}

				$schema['sections'][] = $section;
			}
		}

		return $schema;
	}

	protected function resolveLandingbuilderModeFromDocument(array $document, $default_mode = 'full_takeover') {
		if (!empty($document['meta']['page_mode'])) {
			return (string) $document['meta']['page_mode'];
		}

		if (($document['page_type'] ?? '') === 'standalone') {
			return 'full_takeover';
		}

		return (string) $default_mode;
	}

	protected function getContractStorageStub($contract) {
		$storage_registry = $this->readStorageStubRegistry();
		$storage_stub = isset($storage_registry[$contract['key']]) && is_array($storage_registry[$contract['key']])
			? $storage_registry[$contract['key']]
			: [];

		return [
			'storage_target' => $contract['storage_target'] ?: ($storage_stub['storage_target'] ?? ''),
			'status'         => $storage_stub['status'] ?? 'stub',
			'driver'         => $storage_stub['driver'] ?? 'registry-only',
			'repository'     => $storage_stub['repository'] ?? 'Not assigned yet',
			'read_model'     => $storage_stub['read_model'] ?? 'Read flow is not described yet.',
			'write_model'    => $storage_stub['write_model'] ?? 'Write flow is not described yet.',
			'bridge_source'  => $storage_stub['bridge_source'] ?? ($contract['bridge_kind'] ?: '—'),
			'notes'          => array_values($storage_stub['notes'] ?? [])
		];
	}

	protected function readStorageStubRegistry() {
		$storage_stub_path = $this->getStorageStubPath();

		if (!is_file($storage_stub_path)) {
			return [];
		}

		$registry = include $storage_stub_path;

		return is_array($registry) ? $registry : [];
	}

	protected function buildContractSourcePath($file_name) {
		return 'system/controllers/nordicbuilder/data/contracts/' . $file_name;
	}

	protected function getContractRawDefinition($file_path) {
		$raw = @file_get_contents($file_path);

		if ($raw === false) {
			return '';
		}

		$decoded = json_decode($raw, true);

		if (!is_array($decoded)) {
			return trim($raw);
		}

		$pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

		return $pretty !== false ? $pretty : trim($raw);
	}

	protected function getStoredContractByKey($table_name, $field_name, $key) {
		if (!$this->db->isTableExists($table_name)) {
			return false;
		}

		return $this->getItemByField($table_name, $field_name, $key);
	}

	protected function getStoredPageDocumentIndex() {
		if (!$this->db->isTableExists(self::PAGE_DOCUMENT_TABLE)) {
			return [];
		}

		$documents = $this->get(self::PAGE_DOCUMENT_TABLE, function ($item) {
			return [
				'key'        => (string) ($item['document_key'] ?? ''),
				'status'     => (string) ($item['status'] ?? ''),
				'updated_at' => (string) ($item['updated_at'] ?? '')
			];
		});

		if (!$documents) {
			return [];
		}

		$index = [];
		foreach ($documents as $document) {
			if (!empty($document['key'])) {
				$index[$document['key']] = $document;
			}
		}

		return $index;
	}

	protected function stringifyMigrationErrors(array $errors) {
		if (!$errors) {
			return 'Migration failed.';
		}

		$messages = [];
		foreach ($errors as $field => $error) {
			$messages[] = $field . ': ' . $error;
		}

		return implode(' | ', $messages);
	}

	protected function encodeStoredJson(array $payload) {
		$json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

		return $json !== false ? $json : '{}';
	}

	protected function decodeStoredJson($payload) {
		$decoded = json_decode((string) $payload, true);

		return is_array($decoded) ? $decoded : [];
	}

	protected function hasRequiredContractValue($value) {
		if ($value === null) {
			return false;
		}

		if (is_string($value)) {
			return trim($value) !== '';
		}

		return true;
	}

	protected function sanitizeDocumentKey($key) {
		$key = trim((string) $key);

		if ($key === '') {
			return '';
		}

		$key = preg_replace('/[^a-z0-9_:\/.\-]+/i', '-', $key);

		return trim($key, '-');
	}
}