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

		$item['document'] = $this->normalizePageDocument($this->decodeStoredJson($item['schema_json']));

		return $item;
	}

	public function resolvePrimaryVisualDocumentKey($requested_key = '') {
		$requested_key = $this->sanitizeDocumentKey($requested_key);

		if ($requested_key !== '') {
			return $requested_key;
		}

		$summary = $this->getWorkspaceSummary();

		foreach ((array) ($summary['page_documents'] ?? []) as $doc) {
			if (!empty($doc['key']) && (string) $doc['key'] === 'homepage') {
				return 'homepage';
			}
		}

		if (!empty($summary['page_documents'][0]['key'])) {
			return (string) $summary['page_documents'][0]['key'];
		}

		return 'homepage';
	}

	public function buildEmptyLandingbuilderSchema($key, $title = '', array $fallback_schema = []) {
		$document = $this->buildEmptyCanvasDocument($key, $title);

		return $this->buildLandingbuilderSchemaFromPageDocument($document, $fallback_schema);
	}

	public function ensureVerticalSliceDocument($key = 'vertical-slice-home', $user_id = 0) {
		$key = $this->sanitizeDocumentKey($key);

		if ($key === '') {
			$key = 'vertical-slice-home';
		}

		$existing = $this->getPageDocumentByKey($key);
		if ($existing) {
			return [
				'is_valid'    => true,
				'is_seeded'   => false,
				'document_key'=> $key,
				'document'    => $existing['document'] ?? []
			];
		}

		$document = $this->buildVerticalSliceStarterDocument($key);
		$result = $this->savePageDocument($document, $user_id, 'prototype');
		$result['is_seeded'] = !empty($result['is_valid']);
		$result['document_key'] = $key;
		$result['document'] = $document;

		return $result;
	}

	public function buildStarterLandingbuilderSchema($key, $title = '', array $fallback_schema = []) {
		$document = $this->buildVerticalSliceStarterDocument($key, $title);

		return $this->buildLandingbuilderSchemaFromPageDocument($document, $fallback_schema);
	}

	public function ensureEmptyPageDocument($key, $title = '', $user_id = 0) {
		$key = $this->sanitizeDocumentKey($key);
		if ($key === '') {
			$key = 'homepage';
		}

		$existing = $this->getPageDocumentByKey($key);
		if ($existing) {
			return [
				'is_valid'     => true,
				'is_seeded'    => false,
				'document_key' => $key,
				'document'     => $existing['document'] ?? []
			];
		}

		$document = $this->buildEmptyCanvasDocument($key, $title);
		$result = $this->savePageDocument($document, $user_id, 'draft');
		$result['is_seeded'] = false;
		$result['document_key'] = $key;
		$result['document'] = $document;

		return $result;
	}

	protected function buildEmptyCanvasDocument($key, $title = '') {
		$key = $this->sanitizeDocumentKey($key);
		if ($key === '') {
			$key = 'homepage';
		}

		$title = trim((string) $title);
		if ($title === '') {
			$title = $key;
		}

		return $this->normalizePageDocument([
			'kind'           => 'nordicbuilder.page',
			'schema_version' => '1.0',
			'key'            => $key,
			'title'          => $title,
			'page_type'      => 'standalone',
			'editor_mode'    => 'canvas',
			'meta'           => [
				'bridge_source' => 'nordicbuilder',
				'page_mode'     => 'full_takeover',
				'template'      => 'nordic',
				'adapter_key'   => '',
				'migration_policy' => [
					'source' => 'empty'
				]
			],
			'theme'          => [],
			'layout'         => [
				'layout_mode'   => 'sections',
				'content_slot'  => 'content_body',
				'shell_variant' => ''
			],
			'shell_slots'    => ['content_body'],
			'zones'          => [[
				'zone_key' => 'content_body',
				'title'    => 'Основное содержимое',
				'sections' => []
			]]
		]);
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
		$layout = isset($schema['layout']) && is_array($schema['layout']) ? $schema['layout'] : [];
		$page = array_merge($fallback_page, [
			'key'         => $page_key,
			'name'        => $page_key,
			'title'       => (string) ($fallback_page['title'] ?? $page_key),
			'status'      => (string) ($fallback_page['status'] ?? $status),
			'mode'        => (string) ($fallback_page['mode'] ?? 'full_takeover'),
			'page_type'   => (string) ($fallback_page['page_type'] ?? 'standalone'),
			'template'    => (string) (($layout['template'] ?? '') ?: ($fallback_page['template'] ?? 'nordic')),
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

	public function getBindingOptionsIndex($limit = 200) {
		$limit = max(1, (int) $limit);
		$limit = min($limit, 1000);

		if (!$this->db->isTableExists(self::BINDING_OPTIONS_TABLE)) {
			return [];
		}

		$items = $this->orderBy('updated_at', 'desc')
			->limit(0, $limit)
			->get(self::BINDING_OPTIONS_TABLE, function ($item) {
				return [
					'binding_key'   => (string) ($item['binding_key'] ?? ''),
					'page_key'      => (string) ($item['page_key'] ?? ''),
					'title'         => (string) ($item['title'] ?? ''),
					'schema_version'=> (string) ($item['schema_version'] ?? ''),
					'updated_at'    => (string) ($item['updated_at'] ?? ''),
				];
			});

		return $items ? array_values($items) : [];
	}

	public function deleteBindingOptionsByKey($binding_key) {
		$binding_key = $this->sanitizeDocumentKey($binding_key);
		if ($binding_key === '') {
			return false;
		}

		if (!$this->db->isTableExists(self::BINDING_OPTIONS_TABLE)) {
			return false;
		}

		$item = $this->getItemByField(self::BINDING_OPTIONS_TABLE, 'binding_key', $binding_key);
		if (!$item) {
			return false;
		}

		return $this->delete(self::BINDING_OPTIONS_TABLE, (int) $item['id']);
	}

	public function deleteBindingOptionsByPageKey($page_key) {
		$page_key = $this->sanitizeDocumentKey($page_key);
		if ($page_key === '') {
			return false;
		}

		if (!$this->db->isTableExists(self::BINDING_OPTIONS_TABLE)) {
			return false;
		}

		$this->filterEqual('page_key', $page_key);
		return (bool) $this->deleteFiltered(self::BINDING_OPTIONS_TABLE);
	}

	public function deletePageDocumentByKey($key) {
		$key = $this->sanitizeDocumentKey($key);
		if ($key === '') {
			return false;
		}

		if (!$this->db->isTableExists(self::PAGE_DOCUMENT_TABLE)) {
			return false;
		}

		$this->filterEqual('document_key', $key);
		return (bool) $this->deleteFiltered(self::PAGE_DOCUMENT_TABLE);
	}

	public function savePageDocument(array $document, $user_id = 0, $status = 'draft') {
		$normalized = $this->normalizePageDocument($document);

		return $this->saveStoredContract('page-document', self::PAGE_DOCUMENT_TABLE, 'document_key', $normalized, [
			'title'          => (string) ($normalized['title'] ?? ''),
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
				'message'    => 'Документ страницы уже существует в nordicbuilder.'
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
		$result['message'] = $this->buildMigrationImportMessage($document, (bool) $existing_document);

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

	public function getBindingOptionsCandidatesByPrefix($binding_key_prefix, $limit = 50) {
		$binding_key_prefix = $this->sanitizeDocumentKey($binding_key_prefix);
		$limit = max(1, (int) $limit);
		$limit = min($limit, 200);

		if ($binding_key_prefix === '') {
			return [];
		}

		if (!$this->db->isTableExists(self::BINDING_OPTIONS_TABLE)) {
			return [];
		}

		$items = $this->filterLike('binding_key', $binding_key_prefix . '%')
			->orderBy('updated_at', 'desc')
			->limit(0, $limit)
			->get(self::BINDING_OPTIONS_TABLE, function ($item) {
				$item['document'] = $this->decodeStoredJson($item['options_json'] ?? '');
				return $item;
			});

		return $items ? array_values($items) : [];
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

		$normalized_zones = $this->normalizePageDocumentZones(
			isset($document['zones']) && is_array($document['zones']) ? array_values($document['zones']) : [],
			$document['page_type'],
			$document['editor_mode']
		);

		$document['zones'] = $normalized_zones['zones'];
		$document['meta']['block_manifest_version'] = 'file-registry-v1';
		$document['meta']['migration_policy'] = array_merge(
			['source' => (string) ($document['meta']['migration_policy']['source'] ?? $document['meta']['bridge_source'] ?? 'page-document')],
			$normalized_zones['summary']
		);

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
		$page_type = (string) ($payload['page_type'] ?? 'standalone');
		$editor_mode = (string) ($payload['editor_mode'] ?? 'canvas');

		$allowed_page_types = ['standalone', 'system_overlay', 'ctype_overlay'];
		if ($page_type !== '' && !in_array($page_type, $allowed_page_types, true)) {
			$errors['page_type'] = 'Unsupported page_type.';
		}

		$allowed_editor_modes = ['canvas', 'overlay'];
		if ($editor_mode !== '' && !in_array($editor_mode, $allowed_editor_modes, true)) {
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

						if ($node_type === 'block') {
							$manifest = $this->getBlockManifest((string) ($node['source_key'] ?? ''));

							if (!$manifest) {
								$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns.' . $column_index . '.nodes.' . $node_index . '.source_key'] = 'Unknown block manifest.';
							} else {
								if (!in_array($page_type, (array) ($manifest['supports']['page_types'] ?? []), true)) {
									$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns.' . $column_index . '.nodes.' . $node_index . '.page_type'] = 'Block is not supported for this page_type.';
								}

								if (!in_array($editor_mode, (array) ($manifest['supports']['editor_modes'] ?? []), true)) {
									$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns.' . $column_index . '.nodes.' . $node_index . '.editor_mode'] = 'Block is not supported for this editor_mode.';
								}

								foreach ((array) ($manifest['props_schema'] ?? []) as $prop) {
									$prop_key = (string) ($prop['key'] ?? '');
									if ($prop_key === '' || !array_key_exists($prop_key, (array) ($node['options'] ?? []))) {
										continue;
									}

									$prop_value = $node['options'][$prop_key];
									if (($prop['type'] ?? 'string') === 'string' && !(is_scalar($prop_value) || $prop_value === null)) {
										$errors['zones.' . $zone_index . '.sections.' . $section_index . '.columns.' . $column_index . '.nodes.' . $node_index . '.options.' . $prop_key] = 'Block prop must be a string-compatible value.';
									}
								}
							}
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
		$migration_summary = $this->getEmptyMigrationPolicySummary();
		foreach ($sections as $section) {
			$zone_key = !empty($section['zone_key']) ? (string) $section['zone_key'] : 'content_body';

			if (!isset($zone_map[$zone_key])) {
				$zone_map[$zone_key] = [
					'zone_key' => $zone_key,
					'title'    => $zone_key,
					'sections' => []
				];
			}

			$zone_map[$zone_key]['sections'][] = $this->normalizePageDocumentSection(
				$section,
				$zone_key,
				(string) ($page['page_type'] ?? 'standalone'),
				(string) (((string) ($page['page_type'] ?? 'standalone')) === 'standalone' ? 'canvas' : 'overlay'),
				$migration_summary
			);
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
				'adapter_key'   => (string) ($page['adapter_key'] ?? ''),
				'migration_policy' => array_merge(['source' => 'landingbuilder'], $migration_summary)
			],
			'theme'          => isset($schema['theme']) && is_array($schema['theme']) ? $schema['theme'] : [],
			'layout'         => isset($schema['layout']) && is_array($schema['layout']) ? $schema['layout'] : [],
			'shell_slots'    => isset($schema['shell_slots']) && is_array($schema['shell_slots']) ? array_values($schema['shell_slots']) : [],
			'zones'          => array_values($zone_map)
		]);
	}

	protected function buildVerticalSliceStarterDocument($key = 'vertical-slice-home', $title = '') {
		$key = $this->sanitizeDocumentKey($key);

		if ($key === '') {
			$key = 'vertical-slice-home';
		}

		$title = trim((string) $title);
		if ($title === '') {
			$title = 'Готовый стартовый лендинг';
		}

		return $this->normalizePageDocument([
			'kind'           => 'nordicbuilder.page',
			'schema_version' => '1.0',
			'key'            => $key,
			'title'          => $title,
			'page_type'      => 'standalone',
			'editor_mode'    => 'canvas',
			'meta'           => [
				'bridge_source' => 'nordicbuilder',
				'page_mode'     => 'full_takeover',
				'template'      => 'nordic',
				'adapter_key'   => '',
				'migration_policy' => [
					'source' => 'starter-product-preset'
				]
			],
			'theme'          => [
				'template_preset'     => 'nm_landing',
				'global_style_preset' => 'nordic_catalog',
				'color_preset'        => 'nordic_day',
				'typography_preset'   => 'neutral',
				'container_preset'    => 'wide',
				'button_preset'       => 'solid_brand',
				'card_preset'         => 'raised',
				'section_spacing'     => 'comfortable'
			],
			'layout'         => [
				'layout_mode'   => 'sections',
				'content_slot'  => 'content_body',
				'shell_variant' => ''
			],
			'shell_slots'    => ['content_body'],
			'zones'          => [[
				'zone_key'  => 'content_body',
				'title'     => 'Основное содержимое',
				'sections'  => [
					[
						'uid'              => 'section-hero',
						'title'            => 'Стартовый оффер',
						'layout'           => '1col',
						'section_type'     => 'hero',
						'style_preset'     => 'hero',
						'background_tone'  => 'brand-soft',
						'container_preset' => 'standard',
						'spacing_preset'   => 'xl',
						'visibility'       => ['desktop' => true, 'tablet' => true, 'mobile' => true],
						'settings'         => ['background_class' => '', 'padding' => 'xl', 'css_class' => ''],
						'columns'          => [[
							'uid'        => 'section-hero-column-1',
							'title'      => 'Основная колонка',
							'visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
							'width'      => ['desktop' => 'auto', 'tablet' => 'auto', 'mobile' => 'auto'],
							'settings'   => ['align' => 'stretch', 'css_class' => ''],
							'nodes'      => [
								[
									'uid'               => 'section-hero-column-1-node-1',
									'type'              => 'block',
									'label'             => 'Главный экран',
									'class_name'        => '',
									'notes'             => '',
									'source_key'        => 'core.hero-heading',
									'device_visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
									'options'           => [
										'eyebrow' => 'Готовый старт для бизнеса',
										'title'   => 'Запустите страницу без пустого холста',
										'text'    => 'Здесь уже собран первый экран, смысловой оффер, преимущества и финальный призыв к действию. Дальше вы просто правите этот каркас под свой продукт.'
									]
								],
								[
									'uid'               => 'section-hero-column-1-node-2',
									'type'              => 'block',
									'label'             => 'Призыв к действию',
									'class_name'        => '',
									'notes'             => '',
									'source_key'        => 'core.hero-actions',
									'device_visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
									'options'           => [
										'title'           => 'Правьте готовую структуру под себя',
										'text'            => 'Поменяйте заголовок, карточки, преимущества и тексты кнопок. Страница уже собрана так, чтобы не начинать с нуля.',
										'primary_label'   => 'Начать правки',
										'secondary_label' => 'Открыть предпросмотр'
									]
								]
							]
						]]
					],
					[
						'uid'              => 'section-offer',
						'title'            => 'Что уже готово',
						'layout'           => '2col_equal',
						'section_type'     => 'content',
						'style_preset'     => 'content',
						'background_tone'  => 'base',
						'container_preset' => 'wide',
						'spacing_preset'   => 'lg',
						'visibility'       => ['desktop' => true, 'tablet' => true, 'mobile' => true],
						'settings'         => ['background_class' => '', 'padding' => 'lg', 'css_class' => ''],
						'columns'          => [
							[
								'uid'        => 'section-offer-column-1',
								'title'      => 'Основная колонка',
								'visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
								'width'      => ['desktop' => 'auto', 'tablet' => 'auto', 'mobile' => 'auto'],
								'settings'   => ['align' => 'stretch', 'css_class' => ''],
								'nodes'      => [
									[
										'uid'               => 'section-offer-column-1-node-1',
										'type'              => 'block',
										'label'             => 'Карточки',
										'class_name'        => '',
										'notes'             => '',
										'source_key'        => 'core.cards-grid',
										'device_visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
										'options'           => [
											'title'      => 'Что уже собрано в стартовой странице',
											'text'       => 'Этот макет можно сразу превращать в страницу услуги, небольшого агентства, мастера или локального бизнеса.',
											'items_text' => "Первый экран с оффером\nСекция с ключевыми смыслами\nПреимущества и финальный CTA"
										]
									],
									[
										'uid'               => 'section-offer-column-1-node-2',
										'type'              => 'block',
										'label'             => 'Преимущества',
										'class_name'        => '',
										'notes'             => '',
										'source_key'        => 'core.feature-list',
										'device_visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
										'options'           => [
											'title'      => 'Что вы обычно меняете первым',
											'items_text' => "Заголовок и обещание\nКарточки услуг или пакетов\nАргументы доверия и кнопки"
										]
									]
								]
							],
							[
								'uid'        => 'section-offer-column-2',
								'title'      => 'Боковая колонка',
								'visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
								'width'      => ['desktop' => 'auto', 'tablet' => 'auto', 'mobile' => 'auto'],
								'settings'   => ['align' => 'stretch', 'css_class' => ''],
								'nodes'      => [
									[
										'uid'               => 'section-offer-column-2-node-1',
										'type'              => 'block',
										'label'             => 'Пользовательский блок',
										'class_name'        => '',
										'notes'             => '',
										'source_key'        => 'custom.raw-block',
										'device_visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
										'options'           => [
											'title' => 'Как использовать этот старт',
											'text'  => 'Оставьте структуру как есть и последовательно замените тексты, карточки и преимущества на свои. Это быстрее и понятнее, чем собирать страницу с пустого полотна.'
										]
									]
								]
							]
						]
					],
					[
						'uid'              => 'section-finish',
						'title'            => 'Финальный призыв',
						'layout'           => '1col',
						'section_type'     => 'content',
						'style_preset'     => 'content',
						'background_tone'  => 'brand-soft',
						'container_preset' => 'standard',
						'spacing_preset'   => 'lg',
						'visibility'       => ['desktop' => true, 'tablet' => true, 'mobile' => true],
						'settings'         => ['background_class' => '', 'padding' => 'lg', 'css_class' => ''],
						'columns'          => [[
							'uid'        => 'section-finish-column-1',
							'title'      => 'Колонка',
							'visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
							'width'      => ['desktop' => 'auto', 'tablet' => 'auto', 'mobile' => 'auto'],
							'settings'   => ['align' => 'stretch', 'css_class' => ''],
							'nodes'      => [[
								'uid'               => 'section-finish-column-1-node-1',
								'type'              => 'block',
								'label'             => 'Финальный CTA',
								'class_name'        => '',
								'notes'             => '',
								'source_key'        => 'core.hero-actions',
								'device_visibility' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
								'options'           => [
									'title'           => 'Страница уже готова к адаптации под ваш продукт',
									'text'            => 'Сохраните структуру и начните менять содержимое по своему сценарию: услуги, предложения, кейсы, контакты или тарифы.',
									'primary_label'   => 'Сохранить и продолжить',
									'secondary_label' => 'Проверить итог'
								]
							]]
						]]
					]
				]
			]]
		]);
	}

	protected function getBlockManifestsPath() {
		return __DIR__ . '/data/block_manifests.php';
	}

	protected function getBlockManifestRegistry() {
		static $registry = null;

		if ($registry !== null) {
			return $registry;
		}

		$registry = [];
		$path = $this->getBlockManifestsPath();

		if (is_file($path)) {
			$loaded = include $path;

			if (is_array($loaded)) {
				foreach ($loaded as $key => $manifest) {
					$normalized = $this->normalizeBlockManifestDefinition(is_array($manifest) ? $manifest : [], is_string($key) ? $key : '');
					if (!empty($normalized['key'])) {
						$registry[$normalized['key']] = $normalized;
					}
				}
			}
		}

		if (!isset($registry['custom.raw-block'])) {
			$registry['custom.raw-block'] = $this->normalizeBlockManifestDefinition([], 'custom.raw-block');
		}

		return $registry;
	}

	protected function getBlockManifest($key) {
		$key = trim((string) $key);
		$registry = $this->getBlockManifestRegistry();

		return $registry[$key] ?? false;
	}

	protected function normalizeBlockManifestDefinition(array $manifest, $fallback_key = 'custom.raw-block') {
		$key = (string) ($manifest['key'] ?? $fallback_key ?: 'custom.raw-block');
		$meta = isset($manifest['meta']) && is_array($manifest['meta']) ? $manifest['meta'] : [];

		return [
			'kind'           => (string) ($manifest['kind'] ?? 'nordicbuilder.block'),
			'schema_version' => (string) ($manifest['schema_version'] ?? '1.0'),
			'key'            => $key,
			'title'          => (string) ($manifest['title'] ?? $key),
			'category'       => (string) ($manifest['category'] ?? 'custom'),
			'supports'       => isset($manifest['supports']) && is_array($manifest['supports']) ? $manifest['supports'] : [],
			'render'         => isset($manifest['render']) && is_array($manifest['render']) ? $manifest['render'] : [],
			'props_schema'   => isset($manifest['props_schema']) && is_array($manifest['props_schema']) ? array_values($manifest['props_schema']) : [],
			'defaults'       => isset($manifest['defaults']) && is_array($manifest['defaults']) ? $manifest['defaults'] : [],
			'meta'           => [
				'default_label'     => (string) ($meta['default_label'] ?? ($manifest['title'] ?? $key)),
				'summary'           => (string) ($meta['summary'] ?? ''),
				'migration_aliases' => isset($meta['migration_aliases']) && is_array($meta['migration_aliases']) ? array_values($meta['migration_aliases']) : []
			]
		];
	}

	protected function getEmptyMigrationPolicySummary() {
		return [
			'zones_normalized'       => 0,
			'sections_normalized'    => 0,
			'alias_resolved'         => 0,
			'custom_blocks'          => 0,
			'notes_promoted'         => 0,
			'labels_promoted'        => 0,
			'section_types_inferred' => 0
		];
	}

	protected function normalizePageDocumentZones(array $zones, $page_type = 'standalone', $editor_mode = 'canvas') {
		$summary = $this->getEmptyMigrationPolicySummary();
		$normalized = [];

		foreach ($zones as $zone_index => $zone) {
			if (!is_array($zone)) {
				continue;
			}

			$zone_key = $this->normalizeRuntimeZoneKey($zone['zone_key'] ?? ($zone['key'] ?? 'content_body'));
			$sections = [];

			foreach ((array) ($zone['sections'] ?? []) as $section) {
				if (!is_array($section)) {
					continue;
				}

				$sections[] = $this->normalizePageDocumentSection($section, $zone_key, $page_type, $editor_mode, $summary);
			}

			$normalized[] = [
				'zone_key' => $zone_key,
				'title'    => (string) ($zone['title'] ?? $zone_key ?: ('zone-' . ($zone_index + 1))),
				'sections' => $sections
			];

			$summary['zones_normalized']++;
		}

		if (!$normalized) {
			$normalized[] = [
				'zone_key' => 'content_body',
				'title'    => 'content_body',
				'sections' => []
			];
		}

		return ['zones' => $normalized, 'summary' => $summary];
	}

	protected function normalizePageDocumentSection(array $section, $zone_key, $page_type, $editor_mode, array &$summary) {
		$layout = (string) ($section['layout'] ?? '');
		$layout = $layout !== '' ? $layout : $this->inferSectionLayout($section);
		$section_type = (string) ($section['section_type'] ?? '');

		if ($section_type === '') {
			$section_type = $this->inferSectionTypeFromSection($section, $page_type);
			$summary['section_types_inferred']++;
		}

		$normalized = [
			'uid'              => (string) ($section['uid'] ?? ('section-' . substr(md5($zone_key . serialize($section)), 0, 8))),
			'title'            => trim((string) ($section['title'] ?? '')) ?: 'Секция',
			'layout'           => $layout,
			'zone_key'         => $zone_key,
			'section_type'     => $section_type,
			'style_preset'     => (string) ($section['style_preset'] ?? $this->inferSectionStylePreset($section_type, $layout)),
			'background_tone'  => (string) ($section['background_tone'] ?? ($section_type === 'hero' ? 'brand-soft' : 'base')),
			'container_preset' => (string) ($section['container_preset'] ?? ($layout === '1col' ? 'standard' : 'wide')),
			'spacing_preset'   => (string) ($section['spacing_preset'] ?? ($section_type === 'hero' ? 'xl' : 'md')),
			'visibility'       => $this->normalizeVisibilityMap($section['visibility'] ?? []),
			'settings'         => array_merge([
				'background_class' => '',
				'padding'          => 'md',
				'css_class'        => '',
				'zone_key'         => $zone_key
			], isset($section['settings']) && is_array($section['settings']) ? $section['settings'] : []),
			'columns'          => []
		];

		foreach ((array) ($section['columns'] ?? []) as $column_index => $column) {
			if (!is_array($column)) {
				continue;
			}

			$normalized['columns'][] = $this->normalizePageDocumentColumn(
				$column,
				$normalized['uid'],
				$column_index,
				$page_type,
				$editor_mode,
				$summary
			);
		}

		$summary['sections_normalized']++;

		return $normalized;
	}

	protected function normalizePageDocumentColumn(array $column, $section_uid, $column_index, $page_type, $editor_mode, array &$summary) {
		$normalized = [
			'uid'        => (string) ($column['uid'] ?? ($section_uid . '-column-' . ($column_index + 1))),
			'title'      => trim((string) ($column['title'] ?? '')) ?: ('Колонка ' . ($column_index + 1)),
			'visibility' => $this->normalizeVisibilityMap($column['visibility'] ?? []),
			'width'      => $this->normalizeColumnWidthMap($column['width'] ?? []),
			'settings'   => array_merge([
				'align'     => 'stretch',
				'css_class' => ''
			], isset($column['settings']) && is_array($column['settings']) ? $column['settings'] : []),
			'nodes'      => []
		];

		foreach ((array) ($column['nodes'] ?? []) as $node_index => $node) {
			if (!is_array($node)) {
				continue;
			}

			$normalized['nodes'][] = $this->normalizePageDocumentNode(
				$node,
				$normalized['uid'],
				$node_index,
				$page_type,
				$editor_mode,
				$summary
			);
		}

		return $normalized;
	}

	protected function normalizePageDocumentNode(array $node, $column_uid, $node_index, $page_type, $editor_mode, array &$summary) {
		$node_type = (string) ($node['type'] ?? 'block');

		if ($node_type === 'system_widget') {
			return [
				'uid'               => (string) ($node['uid'] ?? ($column_uid . '-node-' . ($node_index + 1))),
				'type'              => 'system_widget',
				'label'             => trim((string) ($node['label'] ?? '')) ?: (trim((string) ($node['widget_name'] ?? '')) ?: 'Системный виджет'),
				'class_name'        => (string) ($node['class_name'] ?? ''),
				'notes'             => (string) ($node['notes'] ?? ''),
				'source_key'        => '',
				'device_visibility' => $this->normalizeVisibilityMap($node['device_visibility'] ?? []),
				'options'           => isset($node['options']) && is_array($node['options']) ? $node['options'] : [],
				'widget_id'         => (int) ($node['widget_id'] ?? 0),
				'widget_name'       => (string) ($node['widget_name'] ?? ''),
				'widget_controller' => (string) ($node['widget_controller'] ?? '')
			];
		}

		list($manifest, $resolution) = $this->resolveBlockManifestForNode($node, $page_type, $editor_mode);
		$raw_label = trim((string) ($node['label'] ?? ''));
		$default_label = (string) ($manifest['meta']['default_label'] ?? $manifest['title'] ?? 'Блок');

		if ($resolution === 'alias') {
			$summary['alias_resolved']++;
		}

		if ($resolution === 'fallback') {
			$summary['custom_blocks']++;
		}

		$options_result = $this->normalizeBlockNodeOptions($manifest, isset($node['options']) && is_array($node['options']) ? $node['options'] : [], $raw_label, (string) ($node['notes'] ?? ''));
		$summary['notes_promoted'] += $options_result['summary']['notes_promoted'];

		$label = $raw_label;
		if ($label === '' || $label === (string) ($manifest['key'] ?? '') || $label === (string) ($manifest['title'] ?? '')) {
			$label = $default_label;
			$summary['labels_promoted']++;
		}

		return [
			'uid'               => (string) ($node['uid'] ?? ($column_uid . '-node-' . ($node_index + 1))),
			'type'              => 'block',
			'label'             => $label,
			'class_name'        => (string) ($node['class_name'] ?? ''),
			'notes'             => (string) ($node['notes'] ?? ''),
			'source_key'        => (string) ($manifest['key'] ?? 'custom.raw-block'),
			'device_visibility' => $this->normalizeVisibilityMap($node['device_visibility'] ?? []),
			'options'           => $options_result['options']
		];
	}

	protected function normalizeBlockNodeOptions(array $manifest, array $options, $raw_label = '', $notes = '') {
		$normalized = isset($manifest['defaults']) && is_array($manifest['defaults']) ? $manifest['defaults'] : [];
		$summary = ['notes_promoted' => 0];
		$notes = trim((string) $notes);

		foreach ((array) ($manifest['props_schema'] ?? []) as $prop) {
			$prop_key = (string) ($prop['key'] ?? '');
			if ($prop_key === '') {
				continue;
			}

			if (array_key_exists($prop_key, $options) && $options[$prop_key] !== null && $options[$prop_key] !== '') {
				$normalized[$prop_key] = $this->normalizeBlockPropValue($prop['type'] ?? 'string', $options[$prop_key]);
				continue;
			}

			if ($prop_key === 'title' && $raw_label !== '' && !$this->isManifestReferenceValue($raw_label, $manifest)) {
				$normalized[$prop_key] = trim((string) $raw_label);
				continue;
			}

			if (($prop_key === 'text' || $prop_key === 'items_text') && $notes !== '') {
				$normalized[$prop_key] = $notes;
				$summary['notes_promoted']++;
			}
		}

		foreach ($options as $option_key => $option_value) {
			if (!array_key_exists($option_key, $normalized)) {
				$normalized[$option_key] = $option_value;
			}
		}

		return ['options' => $normalized, 'summary' => $summary];
	}

	protected function normalizeBlockPropValue($type, $value) {
		if ($value === null) {
			return '';
		}

		if ($type === 'string') {
			if (is_array($value)) {
				return '';
			}

			return trim((string) $value);
		}

		return is_scalar($value) ? $value : '';
	}

	protected function resolveBlockManifestForNode(array $node, $page_type = 'standalone', $editor_mode = 'canvas') {
		$source_key = trim((string) ($node['source_key'] ?? ''));
		if ($source_key !== '') {
			$manifest = $this->getBlockManifest($source_key);
			if ($manifest && $this->isBlockManifestSupported($manifest, $page_type, $editor_mode)) {
				return [$manifest, 'direct'];
			}
		}

		$lookup_values = array_filter([
			$this->normalizeManifestLookupValue($node['label'] ?? ''),
			$this->normalizeManifestLookupValue($source_key)
		]);

		foreach ($this->getBlockManifestRegistry() as $manifest) {
			if (!$this->isBlockManifestSupported($manifest, $page_type, $editor_mode)) {
				continue;
			}

			$aliases = array_merge(
				[$manifest['key'], $manifest['title'], $manifest['meta']['default_label'] ?? ''],
				(array) ($manifest['meta']['migration_aliases'] ?? [])
			);

			foreach ($aliases as $alias) {
				if (in_array($this->normalizeManifestLookupValue($alias), $lookup_values, true)) {
					return [$manifest, 'alias'];
				}
			}
		}

		return [$this->getBlockManifest('custom.raw-block'), 'fallback'];
	}

	protected function isBlockManifestSupported(array $manifest, $page_type, $editor_mode) {
		$page_types = (array) ($manifest['supports']['page_types'] ?? []);
		$editor_modes = (array) ($manifest['supports']['editor_modes'] ?? []);

		if ($page_types && !in_array((string) $page_type, $page_types, true)) {
			return false;
		}

		if ($editor_modes && !in_array((string) $editor_mode, $editor_modes, true)) {
			return false;
		}

		return true;
	}

	protected function normalizeManifestLookupValue($value) {
		$value = trim((string) $value);
		if ($value === '') {
			return '';
		}

		$value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
		return preg_replace('/\s+/u', ' ', $value);
	}

	protected function isManifestReferenceValue($value, array $manifest) {
		$normalized = $this->normalizeManifestLookupValue($value);
		if ($normalized === '') {
			return false;
		}

		$aliases = array_merge(
			[$manifest['key'], $manifest['title'], $manifest['meta']['default_label'] ?? ''],
			(array) ($manifest['meta']['migration_aliases'] ?? [])
		);

		foreach ($aliases as $alias) {
			if ($this->normalizeManifestLookupValue($alias) === $normalized) {
				return true;
			}
		}

		return false;
	}

	protected function inferSectionLayout(array $section) {
		$columns_count = count((array) ($section['columns'] ?? []));

		if ($columns_count >= 3) {
			return '3col_equal';
		}

		if ($columns_count === 2) {
			return '2col_equal';
		}

		return '1col';
	}

	protected function inferSectionTypeFromSection(array $section, $page_type) {
		foreach ((array) ($section['columns'] ?? []) as $column) {
			foreach ((array) ($column['nodes'] ?? []) as $node) {
				if (!is_array($node) || ($node['type'] ?? 'block') !== 'block') {
					continue;
				}

				list($manifest) = $this->resolveBlockManifestForNode($node, $page_type, 'canvas');
				$category = (string) ($manifest['category'] ?? '');

				if ($category === 'hero') {
					return 'hero';
				}
			}
		}

		return $page_type === 'standalone' ? 'content' : 'overlay';
	}

	protected function inferSectionStylePreset($section_type, $layout) {
		if ($section_type === 'hero') {
			return $layout === '1col' ? 'hero' : 'hero-split';
		}

		return 'content';
	}

	protected function normalizeVisibilityMap($visibility) {
		$defaults = ['desktop' => true, 'tablet' => true, 'mobile' => true];

		return array_merge($defaults, is_array($visibility) ? $visibility : []);
	}

	protected function normalizeColumnWidthMap($width) {
		$defaults = ['desktop' => 'auto', 'tablet' => 'auto', 'mobile' => 'auto'];

		return array_merge($defaults, is_array($width) ? $width : []);
	}

	protected function normalizeRuntimeZoneKey($zone_key) {

		$zone_key = trim((string) $zone_key);
		if ($zone_key === '') {
			return 'content_body';
		}

		$legacy_map = [
			'main'           => 'content_body',
			'native_content' => 'content_body',
			'sidebar'        => 'content_sidebar_right'
		];

		return $legacy_map[$zone_key] ?? $zone_key;
	}

	protected function buildLandingbuilderSchemaFromPageDocument(array $document, array $fallback_schema = []) {
		$document = $this->normalizePageDocument($document);
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

	protected function buildMigrationImportMessage(array $document, $is_update = false) {
		$base_message = $is_update
			? 'Документ страницы обновлен из legacy landingbuilder.'
			: 'Документ страницы импортирован из landingbuilder.';

		$policy = isset($document['meta']['migration_policy']) && is_array($document['meta']['migration_policy'])
			? $document['meta']['migration_policy']
			: [];

		$parts = [];
		if (!empty($policy['alias_resolved'])) {
			$parts[] = 'alias resolved: ' . (int) $policy['alias_resolved'];
		}

		if (!empty($policy['notes_promoted'])) {
			$parts[] = 'notes promoted: ' . (int) $policy['notes_promoted'];
		}

		if (!empty($policy['custom_blocks'])) {
			$parts[] = 'custom blocks: ' . (int) $policy['custom_blocks'];
		}

		if (!$parts) {
			return $base_message;
		}

		return $base_message . ' Применена migration policy (' . implode(', ', $parts) . ').';
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