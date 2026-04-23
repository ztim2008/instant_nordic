<?php

declare(strict_types=1);

final class NordicblocksScaffoldStage1 {

    private const GENERATOR_NAME = 'nordicblocks-scaffold';
    private const GENERATOR_STAGE = 3;
    private const DESIGN_SYSTEM_MODE = 'global-first';
    private const REQUIRED_ENTITIES = ['title', 'subtitle'];
    private const REQUIRED_RUNTIME_ROOTS = ['meta', 'content', 'design', 'layout', 'data', 'entities', 'runtime'];

    public static function bootstrap(string $rootDir): void {
        require_once $rootDir . '/bootstrap.php';
        require_once $rootDir . '/system/controllers/nordicblocks/libs/InspectorDefinitionRegistry.php';
        require_once $rootDir . '/system/controllers/nordicblocks/libs/InspectorRegistryBuilder.php';
        require_once $rootDir . '/system/controllers/nordicblocks/libs/BlockContractNormalizer.php';
    }

    public static function parseCliArgs(array $argv): array {
        $parsed = [];

        foreach (array_slice($argv, 1) as $argument) {
            if ($argument === '--help' || $argument === '-h') {
                $parsed['help'] = true;
                continue;
            }

            if (strpos($argument, '--') !== 0) {
                $parsed['_'][] = $argument;
                continue;
            }

            $argument = substr($argument, 2);
            if ($argument === '') {
                continue;
            }

            if (strpos($argument, '=') === false) {
                $parsed[$argument] = true;
                continue;
            }

            [$key, $value] = explode('=', $argument, 2);
            $parsed[$key] = $value;
        }

        return $parsed;
    }

    public static function getProfileDefinitions(): array {
        return [
            'hero_like' => [
                'sourceModeProfile' => 'manual',
                'entities' => ['eyebrow', 'title', 'subtitle', 'meta', 'primaryButton', 'secondaryButton', 'media', 'mediaSurface'],
                'capabilities' => ['sectionBackground', 'sectionContainer', 'titleContent', 'subtitleContent', 'buttonsContent', 'mediaContent', 'eyebrowTypography', 'titleTypography', 'subtitleTypography', 'metaTypography', 'buttonsStyle', 'mediaStyle', 'mediaSurface', 'spacingLayout', 'alignmentLayout', 'responsiveTypography', 'responsiveSpacing', 'dataBindings'],
                'panels' => ['textEyebrowContent', 'textTitleContent', 'textSubtitleContent', 'buttonsContent', 'mediaContent', 'sectionBackground', 'sectionContainer', 'eyebrowTypography', 'titleTypography', 'subtitleTypography', 'metaTypography', 'buttonsStyle', 'mediaStyle', 'mediaSurface', 'spacingLayout', 'alignmentLayout', 'dataBindings'],
            ],
            'faq_like' => [
                'sourceModeProfile' => 'content_list',
                'entities' => ['eyebrow', 'title', 'subtitle', 'items', 'itemSurface', 'itemTitle', 'itemText'],
                'capabilities' => ['sectionBackground', 'sectionContainer', 'titleContent', 'subtitleContent', 'repeaterContent', 'eyebrowTypography', 'titleTypography', 'subtitleTypography', 'itemSurface', 'itemTypography', 'spacingLayout', 'alignmentLayout', 'responsiveTypography', 'responsiveSpacing', 'dataBindings', 'repeaterBindings'],
                'panels' => ['textEyebrowContent', 'textTitleContent', 'textSubtitleContent', 'repeaterItems', 'sectionBackground', 'sectionContainer', 'eyebrowTypography', 'titleTypography', 'subtitleTypography', 'itemSurface', 'itemTypography', 'spacingLayout', 'alignmentLayout', 'dataBindings', 'repeaterBindings'],
            ],
            'card_collection' => [
                'sourceModeProfile' => 'content_list',
                'entities' => ['title', 'subtitle', 'items', 'itemSurface', 'itemTitle', 'itemText', 'media'],
                'capabilities' => ['sectionBackground', 'sectionContainer', 'titleContent', 'subtitleContent', 'repeaterContent', 'titleTypography', 'subtitleTypography', 'mediaStyle', 'itemSurface', 'itemTypography', 'spacingLayout', 'alignmentLayout', 'responsiveTypography', 'responsiveSpacing', 'dataBindings', 'repeaterBindings'],
                'panels' => ['textTitleContent', 'textSubtitleContent', 'repeaterItems', 'sectionBackground', 'sectionContainer', 'titleTypography', 'subtitleTypography', 'mediaStyle', 'itemSurface', 'itemTypography', 'spacingLayout', 'alignmentLayout', 'dataBindings', 'repeaterBindings'],
            ],
            'catalog_like' => [
                'sourceModeProfile' => 'content_list',
                'entities' => ['title', 'subtitle', 'primaryButton', 'items', 'itemSurface', 'itemTitle', 'itemText', 'media', 'mediaSurface'],
                'capabilities' => ['sectionBackground', 'sectionContainer', 'titleContent', 'subtitleContent', 'buttonsContent', 'repeaterContent', 'titleTypography', 'subtitleTypography', 'buttonsStyle', 'mediaStyle', 'mediaSurface', 'itemSurface', 'itemTypography', 'spacingLayout', 'alignmentLayout', 'responsiveTypography', 'responsiveSpacing', 'dataBindings', 'repeaterBindings'],
                'panels' => ['textTitleContent', 'textSubtitleContent', 'buttonsContent', 'repeaterItems', 'sectionBackground', 'sectionContainer', 'titleTypography', 'subtitleTypography', 'buttonsStyle', 'mediaStyle', 'mediaSurface', 'itemSurface', 'itemTypography', 'spacingLayout', 'alignmentLayout', 'dataBindings', 'repeaterBindings'],
            ],
            'slider_cards' => [
                'sourceModeProfile' => 'content_list',
                'entities' => ['title', 'subtitle', 'primaryButton', 'viewport', 'track', 'slide', 'slideSurface', 'slideMedia', 'slideEyebrow', 'slideTitle', 'slideText', 'slideMeta', 'slidePrimaryAction', 'slideSecondaryAction', 'navigation', 'prevButton', 'nextButton', 'pagination', 'progress'],
                'capabilities' => ['sectionBackground', 'sectionContainer', 'titleContent', 'subtitleContent', 'buttonsContent', 'titleTypography', 'subtitleTypography', 'buttonsStyle', 'spacingLayout', 'responsiveTypography', 'responsiveSpacing', 'hasSlides', 'hasSliderNavigation', 'hasSliderPagination', 'hasSliderProgress', 'hasMobileSwipe', 'hasAutoplay', 'hasLoop', 'hasContentListSource'],
                'panels' => ['textTitleContent', 'textSubtitleContent', 'buttonsContent', 'sliderSlidesContent', 'sectionBackground', 'sectionContainer', 'titleTypography', 'subtitleTypography', 'buttonsStyle', 'sliderItemTypography', 'sliderMediaDesign', 'sliderSurfaceDesign', 'sliderNavigationDesign', 'sliderPaginationDesign', 'sliderProgressDesign', 'sliderLayout', 'sliderMotion', 'sliderNavigationLayout', 'sliderDataSource', 'sliderDataQuery', 'sliderDataVisibility'],
            ],
            'text_section' => [
                'sourceModeProfile' => 'manual',
                'entities' => ['eyebrow', 'title', 'subtitle', 'body', 'primaryButton'],
                'capabilities' => ['sectionBackground', 'sectionContainer', 'titleContent', 'subtitleContent', 'bodyContent', 'buttonsContent', 'eyebrowTypography', 'titleTypography', 'subtitleTypography', 'bodyTypography', 'buttonsStyle', 'spacingLayout', 'alignmentLayout', 'responsiveTypography', 'responsiveSpacing'],
                'panels' => ['textEyebrowContent', 'textTitleContent', 'textSubtitleContent', 'buttonsContent', 'sectionBackground', 'sectionContainer', 'eyebrowTypography', 'titleTypography', 'subtitleTypography', 'bodyTypography', 'buttonsStyle', 'spacingLayout', 'alignmentLayout'],
            ],
        ];
    }

    public static function getRegistry(): array {
        return [
            'entities' => NordicblocksInspectorDefinitionRegistry::getEntities(),
            'entityGroups' => NordicblocksInspectorDefinitionRegistry::getEntityGroups(),
            'capabilities' => NordicblocksInspectorDefinitionRegistry::getCapabilities(),
            'panelMap' => NordicblocksInspectorDefinitionRegistry::getPanelMap(),
        ];
    }

    public static function buildBlueprint(array $args, string $rootDir): array {
        $input = self::loadInputSpec($args);
        $profiles = self::getProfileDefinitions();
        $profileKey = self::normalizeSlug((string) ($input['profile'] ?? $args['profile'] ?? ''));
        $profile = $profiles[$profileKey] ?? [];

        $slug = self::normalizeSlug((string) ($input['slug'] ?? $args['slug'] ?? ''));
        $title = trim((string) ($input['title'] ?? $args['title'] ?? ''));
        $family = trim((string) ($input['family'] ?? $args['family'] ?? ''));
        $sourceModeProfile = trim((string) ($input['sourceModeProfile'] ?? $args['source-mode'] ?? $profile['sourceModeProfile'] ?? 'manual'));

        $entities = self::normalizeList($input['entities'] ?? ($args['entities'] ?? ($profile['entities'] ?? [])));
        $capabilities = self::normalizeList($input['capabilities'] ?? ($args['capabilities'] ?? ($profile['capabilities'] ?? [])));
        $panels = self::normalizeList($input['panels'] ?? ($args['panels'] ?? ($profile['panels'] ?? [])));

        $entityGroups = self::resolveEntityGroups($entities, self::getRegistry()['entityGroups']);

        return [
            'slug' => $slug,
            'title' => $title,
            'family' => $family,
            'profile' => $profileKey,
            'sourceModeProfile' => $sourceModeProfile,
            'designSystemMode' => (string) ($input['designSystemMode'] ?? $args['design-system-mode'] ?? self::DESIGN_SYSTEM_MODE),
            'allowLocalOverrides' => self::normalizeBool($input['allowLocalOverrides'] ?? ($args['allow-local-overrides'] ?? true), true),
            'requireExplicitInheritToggle' => self::normalizeBool($input['requireExplicitInheritToggle'] ?? ($args['require-inherit-toggle'] ?? true), true),
            'entities' => $entities,
            'entityGroups' => $entityGroups,
            'capabilities' => $capabilities,
            'panels' => $panels,
            'paths' => self::buildPaths($rootDir, $slug, $family),
        ];
    }

    public static function validateBlueprint(array $blueprint, string $rootDir): array {
        $registry = self::getRegistry();
        $issues = [];

        if ($blueprint['slug'] === '') {
            $issues[] = self::issue('error', 'structure', 'missing_slug', 'Не передан slug блока.');
        } elseif (!preg_match('/^[a-z0-9_-]+$/', $blueprint['slug'])) {
            $issues[] = self::issue('error', 'structure', 'invalid_slug', 'Slug должен содержать только a-z, 0-9, _ и -.');
        }

        if ($blueprint['title'] === '') {
            $issues[] = self::issue('error', 'structure', 'missing_title', 'Не передан title блока.');
        }

        if ($blueprint['family'] === '') {
            $issues[] = self::issue('error', 'docs', 'missing_family', 'Не передан family блока.');
        }

        if ($blueprint['profile'] === '') {
            $issues[] = self::issue('error', 'structure', 'missing_profile', 'Не передан profile блока.');
        } elseif (!isset(self::getProfileDefinitions()[$blueprint['profile']])) {
            $issues[] = self::issue('error', 'structure', 'unknown_profile', 'Указан неизвестный profile scaffold-а.');
        }

        foreach (self::REQUIRED_ENTITIES as $requiredEntity) {
            if (!in_array($requiredEntity, $blueprint['entities'], true)) {
                $issues[] = self::issue('error', 'contract', 'missing_required_entity_' . $requiredEntity, 'Blueprint обязан содержать сущность ' . $requiredEntity . '.');
            }
        }

        $unknownEntities = array_values(array_diff($blueprint['entities'], array_keys($registry['entities'])));
        foreach ($unknownEntities as $entityKey) {
            $issues[] = self::issue('error', 'registry', 'unknown_entity_' . $entityKey, 'Сущность ' . $entityKey . ' отсутствует в InspectorDefinitionRegistry.');
        }

        $unknownCapabilities = array_values(array_diff($blueprint['capabilities'], array_keys($registry['capabilities'])));
        foreach ($unknownCapabilities as $capabilityKey) {
            $issues[] = self::issue('error', 'registry', 'unknown_capability_' . $capabilityKey, 'Capability ' . $capabilityKey . ' отсутствует в InspectorDefinitionRegistry.');
        }

        $unknownPanels = array_values(array_diff($blueprint['panels'], array_keys($registry['panelMap'])));
        foreach ($unknownPanels as $panelKey) {
            $issues[] = self::issue('error', 'registry', 'unknown_panel_' . $panelKey, 'Панель ' . $panelKey . ' отсутствует в InspectorDefinitionRegistry.');
        }

        if (in_array('itemSurface', $blueprint['entities'], true) || in_array('itemTitle', $blueprint['entities'], true) || in_array('itemText', $blueprint['entities'], true)) {
            if (!in_array('items', $blueprint['entities'], true)) {
                $issues[] = self::issue('error', 'contract', 'missing_items_entity', 'Item-level сущности допустимы только вместе с сущностью items.');
            }
        }

        if (in_array('slideSurface', $blueprint['entities'], true)
            || in_array('slideMedia', $blueprint['entities'], true)
            || in_array('slideEyebrow', $blueprint['entities'], true)
            || in_array('slideTitle', $blueprint['entities'], true)
            || in_array('slideText', $blueprint['entities'], true)
            || in_array('slideMeta', $blueprint['entities'], true)
            || in_array('slidePrimaryAction', $blueprint['entities'], true)
            || in_array('slideSecondaryAction', $blueprint['entities'], true)) {
            if (!in_array('slide', $blueprint['entities'], true)) {
                $issues[] = self::issue('error', 'contract', 'missing_slide_entity', 'Slide-level сущности допустимы только вместе с сущностью slide.');
            }
        }

        foreach ($blueprint['panels'] as $panelKey) {
            if (!isset($registry['panelMap'][$panelKey])) {
                continue;
            }

            $panel = $registry['panelMap'][$panelKey];
            foreach ((array) ($panel['requiresCapabilities'] ?? []) as $capabilityKey) {
                if (!in_array($capabilityKey, $blueprint['capabilities'], true)) {
                    $issues[] = self::issue('error', 'registry', 'panel_requires_capability_' . $panelKey . '_' . $capabilityKey, 'Панель ' . $panelKey . ' требует capability ' . $capabilityKey . '.');
                }
            }

            foreach ((array) ($panel['requiresEntities'] ?? []) as $entityKey) {
                if (!in_array($entityKey, $blueprint['entities'], true)) {
                    $issues[] = self::issue('error', 'registry', 'panel_requires_entity_' . $panelKey . '_' . $entityKey, 'Панель ' . $panelKey . ' требует сущность ' . $entityKey . '.');
                }
            }

            $requiresAnyEntities = (array) ($panel['requiresAnyEntities'] ?? []);
            if ($requiresAnyEntities && !array_intersect($requiresAnyEntities, $blueprint['entities'])) {
                $issues[] = self::issue('error', 'registry', 'panel_requires_any_entity_' . $panelKey, 'Панель ' . $panelKey . ' требует хотя бы одну из сущностей: ' . implode(', ', $requiresAnyEntities) . '.');
            }
        }

        if (!in_array($blueprint['sourceModeProfile'], ['manual', 'content_item', 'content_list'], true)) {
            $issues[] = self::issue('error', 'contract', 'invalid_source_mode_profile', 'sourceModeProfile должен быть manual, content_item или content_list.');
        }

        if ($blueprint['sourceModeProfile'] === 'content_item' && !in_array('dataBindings', $blueprint['capabilities'], true)) {
            $issues[] = self::issue('error', 'runtime', 'content_item_requires_data_bindings', 'Профиль content_item требует capability dataBindings.');
        }

        if ($blueprint['sourceModeProfile'] === 'content_list') {
            $hasLegacyCollection = in_array('items', $blueprint['entities'], true)
                && in_array('dataBindings', $blueprint['capabilities'], true)
                && in_array('repeaterBindings', $blueprint['capabilities'], true);
            $hasSliderCollection = in_array('slide', $blueprint['entities'], true)
                && in_array('hasContentListSource', $blueprint['capabilities'], true);

            if (!$hasLegacyCollection && !$hasSliderCollection) {
                $issues[] = self::issue('error', 'runtime', 'content_list_requires_collection_contract', 'Профиль content_list требует либо legacy contract items + dataBindings + repeaterBindings, либо slider contract slide + hasContentListSource.');
            }
        }

        if ($blueprint['designSystemMode'] !== self::DESIGN_SYSTEM_MODE) {
            $issues[] = self::issue('error', 'design_system', 'non_global_first_design_mode', 'Stage 1 scaffold допускает только global-first design mode.');
        }

        if (!empty($blueprint['allowLocalOverrides']) && empty($blueprint['requireExplicitInheritToggle'])) {
            $issues[] = self::issue('error', 'design_system', 'local_override_without_inherit_toggle', 'Локальные override запрещены без явного inherit toggle.');
        }

        if ($blueprint['slug'] !== '' && is_dir($blueprint['paths']['liveBlockDir'])) {
            $issues[] = self::issue('error', 'structure', 'slug_already_exists', 'Блок с таким slug уже существует в live директории.');
        }

        if ($blueprint['slug'] !== '' && is_dir($blueprint['paths']['packageBlockDir'])) {
            $issues[] = self::issue('error', 'sync', 'package_slug_already_exists', 'Блок с таким slug уже существует в package mirror.');
        }

        if ($blueprint['family'] !== '' && !is_file($blueprint['paths']['familyDoc'])) {
            $issues[] = self::issue('error', 'docs', 'missing_family_doc', 'Для family не найден канонический family-doc: ' . basename($blueprint['paths']['familyDoc']) . '.');
        }

        return self::finalizeValidation($issues, [
            'mode' => 'scaffold_blueprint',
            'blueprint' => $blueprint,
            'plan' => self::buildScaffoldPlan($blueprint),
            'generated' => [
                'manifest.php' => self::generateManifest($blueprint),
                'schema.json' => self::generateSchema($blueprint),
                'render.php' => self::generateRenderStub($blueprint),
            ],
        ]);
    }

    public static function validateExistingBlock(string $slug, string $rootDir): array {
        $slug = self::normalizeSlug($slug);
        $issues = [];
        $paths = self::buildPaths($rootDir, $slug, '');

        if ($slug === '') {
            $issues[] = self::issue('error', 'structure', 'missing_block', 'Не передан slug существующего блока.');
            return self::finalizeValidation($issues, ['mode' => 'existing_block', 'slug' => $slug]);
        }

        $requiredFiles = [
            'manifest' => $paths['liveManifest'],
            'render' => $paths['liveRender'],
            'schema' => $paths['liveSchema'],
            'packageManifest' => $paths['packageManifest'],
            'packageRender' => $paths['packageRender'],
            'packageSchema' => $paths['packageSchema'],
        ];

        foreach ($requiredFiles as $label => $path) {
            if (!is_file($path)) {
                $issues[] = self::issue('error', 'structure', 'missing_' . $label, 'Отсутствует обязательный файл: ' . $path . '.');
            }
        }

        $manifest = [];
        if (is_file($paths['liveManifest'])) {
            $manifest = require $paths['liveManifest'];
            if (!is_array($manifest)) {
                $issues[] = self::issue('error', 'contract', 'invalid_manifest_type', 'manifest.php должен возвращать массив.');
                $manifest = [];
            }
        }

        $schema = [];
        if (is_file($paths['liveSchema'])) {
            $schema = json_decode((string) file_get_contents($paths['liveSchema']), true);
            if (!is_array($schema)) {
                $issues[] = self::issue('error', 'contract', 'invalid_schema_json', 'schema.json не является валидным JSON.');
                $schema = [];
            }
        }

        $generatorMeta = self::detectGeneratorMeta($manifest, $schema);
        $isManagedBlock = !empty($generatorMeta['managed']);

        if ($manifest) {
            $manifestEntities = array_keys((array) ($manifest['entities'] ?? []));
            foreach (self::REQUIRED_ENTITIES as $requiredEntity) {
                if (!in_array($requiredEntity, $manifestEntities, true)) {
                    $issues[] = self::compatibilityIssue($isManagedBlock, 'contract', 'manifest_missing_' . $requiredEntity, 'Manifest существующего блока не содержит сущность ' . $requiredEntity . '.');
                }
            }

            $registry = self::getRegistry();
            foreach ($manifestEntities as $entityKey) {
                if (!isset($registry['entities'][$entityKey])) {
                    $issues[] = self::compatibilityIssue($isManagedBlock, 'registry', 'unknown_manifest_entity_' . $entityKey, 'Manifest ссылается на неизвестную сущность ' . $entityKey . '.');
                }
            }

            foreach (array_keys((array) ($manifest['capabilities'] ?? [])) as $capabilityKey) {
                if (!isset($registry['capabilities'][$capabilityKey])) {
                    $issues[] = self::compatibilityIssue($isManagedBlock, 'registry', 'unknown_manifest_capability_' . $capabilityKey, 'Manifest ссылается на неизвестную capability ' . $capabilityKey . '.');
                }
            }

            foreach (array_keys((array) ($manifest['panels'] ?? [])) as $panelKey) {
                if (!isset($registry['panelMap'][$panelKey])) {
                    $issues[] = self::compatibilityIssue($isManagedBlock, 'registry', 'unknown_manifest_panel_' . $panelKey, 'Manifest ссылается на неизвестную panel ' . $panelKey . '.');
                }
            }
        }

        if (class_exists('NordicblocksInspectorRegistryBuilder')) {
            try {
                $registryBuild = NordicblocksInspectorRegistryBuilder::build($slug);
                if (empty($registryBuild['entities']) || empty($registryBuild['panels'])) {
                    $issues[] = self::compatibilityIssue($isManagedBlock, 'runtime', 'empty_registry_build', 'InspectorRegistryBuilder не смог собрать полноценный registry для блока.');
                }
            } catch (Throwable $exception) {
                $issues[] = self::issue('error', 'runtime', 'registry_builder_failure', 'InspectorRegistryBuilder завершился ошибкой: ' . $exception->getMessage());
            }
        }

        if (class_exists('NordicblocksBlockContractNormalizer') && !NordicblocksBlockContractNormalizer::supportsContractType($slug)) {
            $issues[] = self::compatibilityIssue($isManagedBlock, 'runtime', 'normalizer_missing_support', 'BlockContractNormalizer не поддерживает этот block type.');
        }

        if (class_exists('cmsCore')) {
            $model = cmsCore::getModel('nordicblocks');
            if ($model && method_exists($model, 'isFirstWaveBlockType') && !$model->isFirstWaveBlockType($slug)) {
                $issues[] = self::compatibilityIssue($isManagedBlock, 'runtime', 'missing_first_wave_registration', 'modelNordicblocks не считает block type частью first-wave registry.');
            }
        }

        foreach ([['live' => $paths['liveManifest'], 'package' => $paths['packageManifest']], ['live' => $paths['liveRender'], 'package' => $paths['packageRender']], ['live' => $paths['liveSchema'], 'package' => $paths['packageSchema']]] as $pair) {
            if (is_file($pair['live']) && is_file($pair['package'])) {
                if ((string) file_get_contents($pair['live']) !== (string) file_get_contents($pair['package'])) {
                    $issues[] = self::compatibilityIssue($isManagedBlock, 'sync', 'mirror_mismatch_' . basename($pair['live']), 'Live и package mirror расходятся: ' . basename($pair['live']) . '.');
                }
            }
        }

        $checklistPath = $rootDir . '/docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json';
        if (is_file($checklistPath)) {
            $checklist = json_decode((string) file_get_contents($checklistPath), true);
            $blocks = is_array($checklist['blocks'] ?? null) ? $checklist['blocks'] : [];
            if (!array_key_exists($slug, $blocks)) {
                $issues[] = self::issue('warning', 'docs', 'missing_checklist_entry', 'В NEW_BLOCK_CHECKLIST_STATUS.json нет записи для блока ' . $slug . '. Для legacy block types это допустимо, для новых block types запись обязательна.');
            }
        }

        return self::finalizeValidation($issues, [
            'mode' => 'existing_block',
            'slug' => $slug,
            'managedBlock' => $isManagedBlock,
            'generator' => $generatorMeta,
            'paths' => $paths,
        ]);
    }

    public static function applyBlueprint(array $report, string $checkpointRef, string $rootDir): array {
        if (($report['mode'] ?? '') !== 'scaffold_blueprint') {
            throw new RuntimeException('Apply mode supports scaffold_blueprint reports only.');
        }

        if (in_array((string) ($report['status'] ?? ''), ['FAIL', 'FAILED_DS_GUARD'], true)) {
            throw new RuntimeException('Scaffold apply aborted because blueprint validation did not pass.');
        }

        $checkpointRef = trim($checkpointRef);
        if ($checkpointRef === '') {
            throw new RuntimeException('Apply mode requires --checkpoint=<git-tag>.');
        }

        if (!self::checkpointExists($rootDir, $checkpointRef)) {
            throw new RuntimeException('Checkpoint tag not found: ' . $checkpointRef);
        }

        $blueprint = (array) ($report['blueprint'] ?? []);
        $generated = (array) ($report['generated'] ?? []);
        $fileMap = self::buildGeneratedFileMap($blueprint, $generated);

        foreach ([$blueprint['paths']['liveBlockDir'] ?? '', $blueprint['paths']['packageBlockDir'] ?? ''] as $dirPath) {
            if ($dirPath === '') {
                continue;
            }
            if (!is_dir($dirPath) && !mkdir($dirPath, 0775, true) && !is_dir($dirPath)) {
                throw new RuntimeException('Не удалось создать директорию: ' . $dirPath);
            }
        }

        $writtenFiles = [];
        foreach ($fileMap as $path => $content) {
            if (file_put_contents($path, $content) === false) {
                throw new RuntimeException('Не удалось записать файл: ' . $path);
            }
            $writtenFiles[] = $path;
        }

        $docsTouched = self::syncDocumentation($blueprint, $checkpointRef);
        $writtenFiles = array_values(array_unique(array_merge($writtenFiles, $docsTouched)));

        return [
            'checkpoint' => $checkpointRef,
            'writtenFiles' => $writtenFiles,
        ];
    }

    public static function buildScaffoldPlan(array $blueprint): array {
        return [
            'create' => [
                $blueprint['paths']['liveManifest'],
                $blueprint['paths']['liveRender'],
                $blueprint['paths']['liveSchema'],
                $blueprint['paths']['packageManifest'],
                $blueprint['paths']['packageRender'],
                $blueprint['paths']['packageSchema'],
            ],
            'patch' => [
                $blueprint['paths']['rootDir'] . '/system/controllers/nordicblocks/model.php',
                $blueprint['paths']['rootDir'] . '/package/system/controllers/nordicblocks/model.php',
                $blueprint['paths']['rootDir'] . '/system/controllers/nordicblocks/libs/BlockContractNormalizer.php',
                $blueprint['paths']['rootDir'] . '/package/system/controllers/nordicblocks/libs/BlockContractNormalizer.php',
                $blueprint['paths']['rootDir'] . '/system/controllers/nordicblocks/libs/BindingMapper.php',
                $blueprint['paths']['rootDir'] . '/package/system/controllers/nordicblocks/libs/BindingMapper.php',
                $blueprint['paths']['rootDir'] . '/system/controllers/nordicblocks/libs/DataSourceResolver.php',
                $blueprint['paths']['rootDir'] . '/package/system/controllers/nordicblocks/libs/DataSourceResolver.php',
                $blueprint['paths']['rootDir'] . '/docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json',
                $blueprint['paths']['familyDoc'],
            ],
        ];
    }

    public static function formatReport(array $report): string {
        $lines = [];
        $lines[] = 'Status: ' . $report['status'];
        if (!empty($report['summary']['errors'])) {
            $lines[] = 'Errors: ' . $report['summary']['errors'];
        }
        if (!empty($report['summary']['warnings'])) {
            $lines[] = 'Warnings: ' . $report['summary']['warnings'];
        }

        foreach ($report['issues'] as $issue) {
            $prefix = !empty($issue['legacyDebt']) ? 'LEGACY ' : '';
            $lines[] = $prefix . strtoupper((string) $issue['severity']) . ' [' . $issue['category'] . '] ' . $issue['message'];
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    private static function loadInputSpec(array $args): array {
        $specPath = trim((string) ($args['spec'] ?? ''));
        if ($specPath === '') {
            return [];
        }

        if (!is_file($specPath)) {
            throw new RuntimeException('Spec file not found: ' . $specPath);
        }

        $decoded = json_decode((string) file_get_contents($specPath), true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Spec file is not a valid JSON object: ' . $specPath);
        }

        return $decoded;
    }

    private static function buildPaths(string $rootDir, string $slug, string $family): array {
        $familyDoc = $family !== ''
            ? $rootDir . '/docs/blocks/' . strtoupper($family) . '.md'
            : $rootDir . '/docs/blocks/.missing-family.md';

        return [
            'rootDir' => $rootDir,
            'liveBlockDir' => $rootDir . '/system/controllers/nordicblocks/blocks/' . $slug,
            'packageBlockDir' => $rootDir . '/package/system/controllers/nordicblocks/blocks/' . $slug,
            'liveManifest' => $rootDir . '/system/controllers/nordicblocks/blocks/' . $slug . '/manifest.php',
            'liveRender' => $rootDir . '/system/controllers/nordicblocks/blocks/' . $slug . '/render.php',
            'liveSchema' => $rootDir . '/system/controllers/nordicblocks/blocks/' . $slug . '/schema.json',
            'packageManifest' => $rootDir . '/package/system/controllers/nordicblocks/blocks/' . $slug . '/manifest.php',
            'packageRender' => $rootDir . '/package/system/controllers/nordicblocks/blocks/' . $slug . '/render.php',
            'packageSchema' => $rootDir . '/package/system/controllers/nordicblocks/blocks/' . $slug . '/schema.json',
            'familyDoc' => $familyDoc,
        ];
    }

    private static function syncDocumentation(array $blueprint, string $checkpointRef): array {
        $touched = [];
        $checklistPath = $blueprint['paths']['rootDir'] . '/docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json';

        if (is_file($checklistPath)) {
            $checklist = json_decode((string) file_get_contents($checklistPath), true);
            if (!is_array($checklist)) {
                throw new RuntimeException('Не удалось разобрать NEW_BLOCK_CHECKLIST_STATUS.json.');
            }

            if (!isset($checklist['blocks']) || !is_array($checklist['blocks'])) {
                $checklist['blocks'] = [];
            }

            $existingEntry = is_array($checklist['blocks'][$blueprint['slug']] ?? null) ? $checklist['blocks'][$blueprint['slug']] : [];
            $checklist['updated_at'] = date('Y-m-d');
            $checklist['blocks'][$blueprint['slug']] = self::buildChecklistEntry($blueprint, $checkpointRef, $existingEntry);
            ksort($checklist['blocks']);

            $encodedChecklist = json_encode($checklist, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
            if (file_put_contents($checklistPath, $encodedChecklist) === false) {
                throw new RuntimeException('Не удалось обновить checklist: ' . $checklistPath);
            }

            $touched[] = $checklistPath;

            $familyDocPath = (string) ($blueprint['paths']['familyDoc'] ?? '');
            if ($familyDocPath !== '' && is_file($familyDocPath)) {
                $familyDoc = (string) file_get_contents($familyDocPath);
                $updatedFamilyDoc = self::syncFamilyDoc($familyDoc, $blueprint['family'], $checklist['blocks']);
                if ($updatedFamilyDoc !== $familyDoc) {
                    if (file_put_contents($familyDocPath, $updatedFamilyDoc) === false) {
                        throw new RuntimeException('Не удалось обновить family doc: ' . $familyDocPath);
                    }
                    $touched[] = $familyDocPath;
                }
            }
        }

        return $touched;
    }

    private static function buildChecklistEntry(array $blueprint, string $checkpointRef, array $existingEntry): array {
        $defaultItems = [
            'spec_locked' => true,
            'block_registered' => true,
            'contract_data_layer' => true,
            'editor_shell' => true,
            'shared_runtime_css' => true,
            'package_mirror' => true,
            'live_smoke' => false,
        ];

        $existingItems = is_array($existingEntry['items'] ?? null) ? $existingEntry['items'] : [];
        $items = array_merge($defaultItems, $existingItems);

        return [
            'title' => $blueprint['title'],
            'family' => $blueprint['family'],
            'status' => (string) ($existingEntry['status'] ?? 'in_progress'),
            'checkpoint' => $checkpointRef,
            'items' => $items,
            'notes' => 'Scaffold-generated managed block. Runtime registration, contract support, data layer and package mirror подключаются через stage 3 pipeline; live smoke остаётся обязательным вручную.',
        ];
    }

    private static function syncFamilyDoc(string $content, string $family, array $blocks): string {
        $familyBlocks = [];
        foreach ($blocks as $slug => $block) {
            if (($block['family'] ?? '') !== $family) {
                continue;
            }

            $familyBlocks[$slug] = [
                'title' => (string) ($block['title'] ?? $slug),
                'status' => (string) ($block['status'] ?? 'in_progress'),
                'checkpoint' => (string) ($block['checkpoint'] ?? ''),
                'live_smoke' => !empty($block['items']['live_smoke']),
            ];
        }

        if (!$familyBlocks) {
            return $content;
        }

        ksort($familyBlocks);
        $generatedSection = self::renderFamilyRegistrySection($familyBlocks);
        $pattern = '/(?:\n|^)## Scaffold Registry\n<!-- NORDICBLOCKS_SCAFFOLD_REGISTRY_START -->.*?<!-- NORDICBLOCKS_SCAFFOLD_REGISTRY_END -->\n?/s';
        $cleanedContent = preg_replace($pattern, "\n", $content);

        return rtrim((string) $cleanedContent) . "\n\n" . $generatedSection . "\n";
    }

    private static function renderFamilyRegistrySection(array $familyBlocks): string {
        $lines = [
            '## Scaffold Registry',
            '<!-- NORDICBLOCKS_SCAFFOLD_REGISTRY_START -->',
            'Этот раздел обновляется автоматически scaffold apply pipeline и показывает текущие scaffold-managed block types этой family.',
            '',
            '| Slug | Title | Status | Checkpoint | Live smoke |',
            '| --- | --- | --- | --- | --- |',
        ];

        foreach ($familyBlocks as $slug => $block) {
            $lines[] = '| ' . $slug . ' | ' . str_replace('|', '\\|', $block['title']) . ' | ' . $block['status'] . ' | ' . ($block['checkpoint'] !== '' ? $block['checkpoint'] : '-') . ' | ' . ($block['live_smoke'] ? 'yes' : 'no') . ' |';
        }

        $lines[] = '<!-- NORDICBLOCKS_SCAFFOLD_REGISTRY_END -->';

        return implode("\n", $lines);
    }

    private static function resolveEntityGroups(array $entities, array $sharedGroups): array {
        $resolved = [];

        foreach ($sharedGroups as $groupKey => $group) {
            $groupEntities = array_values(array_intersect((array) ($group['entities'] ?? []), $entities));
            if (!$groupEntities) {
                continue;
            }

            $resolved[$groupKey] = [
                'label' => (string) ($group['label'] ?? $groupKey),
                'entities' => $groupEntities,
            ];
        }

        return $resolved;
    }

    private static function generateManifest(array $blueprint): string {
        $manifest = [
            'title' => $blueprint['title'] . ' inspector manifest',
            'generator' => self::buildGeneratorMeta($blueprint),
            'entities' => array_fill_keys($blueprint['entities'], []),
            'entityGroups' => $blueprint['entityGroups'],
            'capabilities' => array_fill_keys($blueprint['capabilities'], true),
            'panels' => array_fill_keys($blueprint['panels'], []),
        ];

        return "<?php\n\nreturn " . self::exportPhp($manifest) . ";\n";
    }

    private static function generateSchema(array $blueprint): string {
        $schema = [
            'title' => $blueprint['title'],
            'type' => $blueprint['slug'],
            'profile' => $blueprint['profile'],
            'generator' => self::buildGeneratorMeta($blueprint),
            'fields' => self::buildSchemaFields($blueprint),
        ];

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    }

    private static function generateRenderStub(array $blueprint): string {
        $functionPrefix = 'nb_' . str_replace('-', '_', $blueprint['slug']);
        $cssClass = str_replace('_', '-', $blueprint['slug']);
        $titleDefault = self::escapePhpString($blueprint['title']);

        $template = <<<'PHP'
<?php

    /* Generated by __GENERATOR_NAME__ stage __GENERATOR_STAGE__. */

$__FUNCTION_PREFIX___contract = (isset($block_contract) && is_array($block_contract) && ((string) ($block_contract['meta']['blockType'] ?? '') === '__BLOCK_SLUG__'))
    ? $block_contract
    : [];

if (!function_exists('__FUNCTION_PREFIX___visible')) {
    function __FUNCTION_PREFIX___visible($value, $default = true) {
        if ($value === null || $value === '') {
            return (bool) $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        return !in_array(strtolower((string) $value), ['0', 'false', 'off', 'no'], true);
    }
}

$__FUNCTION_PREFIX___content = is_array($__FUNCTION_PREFIX___contract['content'] ?? null) ? $__FUNCTION_PREFIX___contract['content'] : [];
$__FUNCTION_PREFIX___design = is_array($__FUNCTION_PREFIX___contract['design']['entities'] ?? null) ? $__FUNCTION_PREFIX___contract['design']['entities'] : [];
$__FUNCTION_PREFIX___title_visible = __FUNCTION_PREFIX___visible($__FUNCTION_PREFIX___design['title']['visible'] ?? true, true);
$__FUNCTION_PREFIX___subtitle_visible = __FUNCTION_PREFIX___visible($__FUNCTION_PREFIX___design['subtitle']['visible'] ?? true, true);
$__FUNCTION_PREFIX___title = (string) ($__FUNCTION_PREFIX___content['title'] ?? '__TITLE_DEFAULT__');
$__FUNCTION_PREFIX___subtitle = (string) ($__FUNCTION_PREFIX___content['subtitle'] ?? 'Подзаголовок блока');
?>
<section class="nb-section nb-__CSS_CLASS__">
    <div class="nb-container">
        <?php if ($__FUNCTION_PREFIX___title_visible && $__FUNCTION_PREFIX___title !== '') { ?>
            <h2 class="nb-__CSS_CLASS____title"><?php echo htmlspecialchars($__FUNCTION_PREFIX___title, ENT_QUOTES, 'UTF-8'); ?></h2>
        <?php } ?>
        <?php if ($__FUNCTION_PREFIX___subtitle_visible && $__FUNCTION_PREFIX___subtitle !== '') { ?>
            <div class="nb-__CSS_CLASS____subtitle"><?php echo nl2br(htmlspecialchars($__FUNCTION_PREFIX___subtitle, ENT_QUOTES, 'UTF-8')); ?></div>
        <?php } ?>
    </div>
</section>
PHP;

        return strtr($template, [
            '__FUNCTION_PREFIX__' => $functionPrefix,
            '__GENERATOR_NAME__' => self::GENERATOR_NAME,
            '__GENERATOR_STAGE__' => (string) self::GENERATOR_STAGE,
            '__BLOCK_SLUG__' => self::escapePhpString($blueprint['slug']),
            '__TITLE_DEFAULT__' => $titleDefault,
            '__CSS_CLASS__' => self::escapePhpString($cssClass),
        ]) . "\n";
    }

    private static function buildSchemaFields(array $blueprint): array {
        $common = [
            ['key' => 'heading', 'type' => 'text', 'default' => $blueprint['title']],
            ['key' => 'subheading', 'type' => 'textarea', 'default' => 'Подзаголовок блока'],
            ['key' => 'theme', 'type' => 'select', 'default' => 'light'],
        ];

        if ($blueprint['profile'] === 'hero_like') {
            array_unshift($common, ['key' => 'eyebrow', 'type' => 'text', 'default' => '']);
            $common[] = ['key' => 'btn_primary_label', 'type' => 'text', 'default' => 'Подробнее'];
            $common[] = ['key' => 'btn_primary_url', 'type' => 'text', 'default' => '#'];
            $common[] = ['key' => 'image', 'type' => 'text', 'default' => ''];
        }

        if ($blueprint['profile'] === 'faq_like') {
            array_unshift($common, ['key' => 'eyebrow', 'type' => 'text', 'default' => 'FAQ']);
            $common[] = ['key' => 'items', 'type' => 'repeater', 'default' => []];
        }

        if (in_array($blueprint['profile'], ['card_collection', 'catalog_like'], true)) {
            $common[] = ['key' => 'section_link_label', 'type' => 'text', 'default' => 'Открыть все'];
            $common[] = ['key' => 'section_link_url', 'type' => 'text', 'default' => '#'];
            $common[] = ['key' => 'items', 'type' => 'repeater', 'default' => []];
        }

        if ($blueprint['profile'] === 'slider_cards') {
            $common[] = ['key' => 'section_link_label', 'type' => 'text', 'default' => 'Открыть все'];
            $common[] = ['key' => 'section_link_url', 'type' => 'text', 'default' => '#'];
            $common[] = ['key' => 'slides', 'type' => 'repeater', 'default' => []];
        }

        if ($blueprint['profile'] === 'text_section') {
            array_unshift($common, ['key' => 'eyebrow', 'type' => 'text', 'default' => '']);
            $common[] = ['key' => 'body', 'type' => 'textarea', 'default' => 'Основной текст блока'];
            $common[] = ['key' => 'btn_primary_label', 'type' => 'text', 'default' => 'Подробнее'];
            $common[] = ['key' => 'btn_primary_url', 'type' => 'text', 'default' => '#'];
        }

        return $common;
    }

    private static function finalizeValidation(array $issues, array $payload): array {
        $summary = ['errors' => 0, 'warnings' => 0];

        foreach ($issues as $issue) {
            if ($issue['severity'] === 'error') {
                $summary['errors']++;
            }
            if ($issue['severity'] === 'warning') {
                $summary['warnings']++;
            }
        }

        $status = 'PASS';
        if ($summary['errors'] > 0) {
            $designErrors = array_filter($issues, static function (array $issue): bool {
                return $issue['severity'] === 'error' && $issue['category'] === 'design_system';
            });
            $status = $designErrors ? 'FAILED_DS_GUARD' : 'FAIL';
        } elseif ($summary['warnings'] > 0) {
            $status = 'PASS_WITH_WARNINGS';
        }

        return array_merge($payload, [
            'status' => $status,
            'summary' => $summary,
            'issues' => $issues,
        ]);
    }

    private static function normalizeSlug(string $value): string {
        return preg_replace('/[^a-z0-9_-]/', '', strtolower(trim($value)));
    }

    private static function normalizeList($value): array {
        if (is_string($value)) {
            $value = preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
        }

        if (!is_array($value)) {
            return [];
        }

        $normalized = [];
        foreach ($value as $item) {
            $item = trim((string) $item);
            if ($item === '') {
                continue;
            }
            $normalized[] = $item;
        }

        return array_values(array_unique($normalized));
    }

    private static function normalizeBool($value, bool $default): bool {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return $default;
        }

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return $default;
    }

    private static function issue(string $severity, string $category, string $code, string $message, array $meta = []): array {
        return array_merge([
            'severity' => $severity,
            'category' => $category,
            'code' => $code,
            'message' => $message,
        ], $meta);
    }

    private static function compatibilityIssue(bool $isManagedBlock, string $category, string $code, string $message): array {
        if ($isManagedBlock) {
            return self::issue('error', $category, $code, $message);
        }

        return self::issue('warning', $category, $code, $message . ' Обнаружено как legacy debt существующего блока.', [
            'legacyDebt' => true,
        ]);
    }

    private static function buildGeneratorMeta(array $blueprint): array {
        return [
            'name' => self::GENERATOR_NAME,
            'managed' => true,
            'stage' => self::GENERATOR_STAGE,
            'profile' => (string) ($blueprint['profile'] ?? ''),
            'designSystemMode' => (string) ($blueprint['designSystemMode'] ?? self::DESIGN_SYSTEM_MODE),
            'sourceModeProfile' => (string) ($blueprint['sourceModeProfile'] ?? 'manual'),
        ];
    }

    private static function detectGeneratorMeta(array $manifest, array $schema): array {
        $manifestMeta = is_array($manifest['generator'] ?? null) ? $manifest['generator'] : [];
        $schemaMeta = is_array($schema['generator'] ?? null) ? $schema['generator'] : [];

        return $manifestMeta ?: $schemaMeta;
    }

    private static function buildGeneratedFileMap(array $blueprint, array $generated): array {
        return [
            (string) ($blueprint['paths']['liveManifest'] ?? '') => (string) ($generated['manifest.php'] ?? ''),
            (string) ($blueprint['paths']['liveRender'] ?? '') => (string) ($generated['render.php'] ?? ''),
            (string) ($blueprint['paths']['liveSchema'] ?? '') => (string) ($generated['schema.json'] ?? ''),
            (string) ($blueprint['paths']['packageManifest'] ?? '') => (string) ($generated['manifest.php'] ?? ''),
            (string) ($blueprint['paths']['packageRender'] ?? '') => (string) ($generated['render.php'] ?? ''),
            (string) ($blueprint['paths']['packageSchema'] ?? '') => (string) ($generated['schema.json'] ?? ''),
        ];
    }

    private static function checkpointExists(string $rootDir, string $checkpointRef): bool {
        $command = 'git -C ' . escapeshellarg($rootDir) . ' tag -l ' . escapeshellarg($checkpointRef);
        $result = trim((string) shell_exec($command));

        return $result === $checkpointRef;
    }

    private static function exportPhp($value, int $depth = 0): string {
        if (!is_array($value)) {
            return var_export($value, true);
        }

        if ($value === []) {
            return '[]';
        }

        $indent = str_repeat('    ', $depth);
        $childIndent = str_repeat('    ', $depth + 1);
        $isList = array_keys($value) === range(0, count($value) - 1);
        $lines = ['['];

        foreach ($value as $key => $item) {
            $prefix = $isList ? '' : var_export((string) $key, true) . ' => ';
            $lines[] = $childIndent . $prefix . self::exportPhp($item, $depth + 1) . ',';
        }

        $lines[] = $indent . ']';

        return implode("\n", $lines);
    }

    private static function escapePhpString(string $value): string {
        return str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
    }
}