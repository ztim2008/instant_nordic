import { useEffect, useLayoutEffect, useMemo, useState } from 'react';
import {
    BORDER_STYLE_PRESETS,
    STYLE_FIELD_GROUPS,
    getDeviceLabel,
    getInteractionStateLabel,
    getStyleBranchKey,
    pageState
} from '../lib/constants.js';
import {
    applyPreviewCss,
    buildPreviewCss,
    buildPreviewRule,
    buildRulesUrl,
    clearPreviewCss,
    createEmptyRule,
    getMergedDeclarations,
    loadStyleRule,
    normalizeDraftValues,
    saveStyleRule,
    toColorInputValue
} from '../lib/rules.js';
import { useEditorStore } from '../store/editorStore.js';

const EMPTY_STATUS = {
    tone: 'muted',
    message: 'Выберите узел на странице или в панели слоев.'
};

const BOX_SIDES = [
    { key: 'top', label: 'Верх' },
    { key: 'right', label: 'Право' },
    { key: 'bottom', label: 'Низ' },
    { key: 'left', label: 'Лево' }
];

const BOX_GROUPS = [
    {
        title: 'Отступы',
        editors: [
            { title: 'Внутренний', prefix: 'padding', control: 'text', placeholder: '24px' },
            { title: 'Внешний', prefix: 'margin', control: 'text', placeholder: '16px' }
        ]
    },
    {
        title: 'Рамка по сторонам',
        editors: [
            { title: 'Толщина', prefix: 'border', suffix: 'width', control: 'text', placeholder: '1px' },
            { title: 'Цвет', prefix: 'border', suffix: 'color', control: 'color', placeholder: '#d5cec0' },
            { title: 'Стиль', prefix: 'border', suffix: 'style', control: 'select', options: BORDER_STYLE_PRESETS }
        ]
    }
];

function buildDraftValues(rule, deviceKey, stateKey) {
    const merged = getMergedDeclarations(rule, deviceKey, stateKey);
    const nextValues = {};

    STYLE_FIELD_GROUPS.forEach((group) => {
        group.fields.forEach((field) => {
            nextValues[field.key] = merged[field.key] ? String(merged[field.key]) : '';
        });
    });

    nextValues['padding-top'] = readBoxSideValue(merged, 'padding', 'top');
    nextValues['padding-right'] = readBoxSideValue(merged, 'padding', 'right');
    nextValues['padding-bottom'] = readBoxSideValue(merged, 'padding', 'bottom');
    nextValues['padding-left'] = readBoxSideValue(merged, 'padding', 'left');
    nextValues['margin-top'] = readBoxSideValue(merged, 'margin', 'top');
    nextValues['margin-right'] = readBoxSideValue(merged, 'margin', 'right');
    nextValues['margin-bottom'] = readBoxSideValue(merged, 'margin', 'bottom');
    nextValues['margin-left'] = readBoxSideValue(merged, 'margin', 'left');

    BOX_SIDES.forEach(({ key }) => {
        nextValues[`border-${key}-width`] = readBoxSideValue(merged, 'border', key, 'width');
        nextValues[`border-${key}-color`] = readBoxSideValue(merged, 'border', key, 'color');
        nextValues[`border-${key}-style`] = readBoxSideValue(merged, 'border', key, 'style');
    });

    return nextValues;
}

function FieldControl({ field, value, onChange }) {
    if (field.control === 'color') {
        const colorValue = toColorInputValue(value);

        return (
            <div className="ne-color-control">
                <input
                    className="ne-color-control__picker"
                    type="color"
                    value={colorValue || '#000000'}
                    onChange={(event) => onChange(event.target.value)}
                />
                <input
                    className="ne-input ne-input--mono"
                    type="text"
                    value={value}
                    onChange={(event) => onChange(event.target.value)}
                    placeholder={field.placeholder}
                />
            </div>
        );
    }

    if (field.control === 'select') {
        return (
            <select className="ne-input ne-input--select" value={value} onChange={(event) => onChange(event.target.value)}>
                {(field.options || []).map((option) => (
                    <option key={option.value || 'empty'} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        );
    }

    return (
        <input
            className="ne-input ne-input--mono"
            type="text"
            value={value}
            onChange={(event) => onChange(event.target.value)}
            placeholder={field.placeholder}
        />
    );
}

function buildTargetDescriptor(selected, targetMode) {
    if (targetMode === 'role' && selected.role) {
        return {
            type: 'role',
            key: selected.role,
            storagePath: `role:${selected.role}`,
            title: selected.role,
            subtitle: 'Изменения пойдут во все элементы с этой semantic role.'
        };
    }

    return {
        type: 'node',
        key: selected.nodeId,
        storagePath: selected.nodeId ? `node:${selected.nodeId}` : '',
        title: selected.title || selected.nodeId,
        subtitle: 'Изменения применяются только к выбранному узлу.'
    };
}

function parseBoxShorthand(rawValue) {
    const tokens = String(rawValue || '').trim().split(/\s+/).filter(Boolean);

    if (!tokens.length) {
        return { top: '', right: '', bottom: '', left: '' };
    }

    if (tokens.length === 1) {
        return { top: tokens[0], right: tokens[0], bottom: tokens[0], left: tokens[0] };
    }

    if (tokens.length === 2) {
        return { top: tokens[0], right: tokens[1], bottom: tokens[0], left: tokens[1] };
    }

    if (tokens.length === 3) {
        return { top: tokens[0], right: tokens[1], bottom: tokens[2], left: tokens[1] };
    }

    return {
        top: tokens[0],
        right: tokens[1],
        bottom: tokens[2],
        left: tokens[3]
    };
}

function readBoxSideValue(declarations, prefix, side, suffix = '') {
    const explicitKey = suffix ? `${prefix}-${side}-${suffix}` : `${prefix}-${side}`;
    const shorthandKey = suffix ? `${prefix}-${suffix}` : prefix;

    if (declarations[explicitKey]) {
        return String(declarations[explicitKey]);
    }

    const shorthand = parseBoxShorthand(declarations[shorthandKey]);

    return shorthand[side] || '';
}

function getBoxEditorKey(editor) {
    return `${editor.prefix}-${editor.suffix || 'box'}`;
}

function getBoxFieldKey(editor, sideKey) {
    return editor.suffix ? `${editor.prefix}-${sideKey}-${editor.suffix}` : `${editor.prefix}-${sideKey}`;
}

function getBoxEditorFieldKeys(editor) {
    return BOX_SIDES.map(({ key }) => getBoxFieldKey(editor, key));
}

function areBoxEditorSidesLinked(editor, values) {
    const [firstKey, ...restKeys] = getBoxEditorFieldKeys(editor);
    const firstValue = String(values?.[firstKey] || '');

    return restKeys.every((fieldKey) => String(values?.[fieldKey] || '') === firstValue);
}

function buildBoxLinkState(values) {
    const nextState = {};

    BOX_GROUPS.forEach((group) => {
        group.editors.forEach((editor) => {
            nextState[getBoxEditorKey(editor)] = areBoxEditorSidesLinked(editor, values);
        });
    });

    return nextState;
}

function getBoxLinkSeedValue(editor, values) {
    const fieldKeys = getBoxEditorFieldKeys(editor);
    const seededKey = fieldKeys.find((fieldKey) => String(values?.[fieldKey] || '').trim() !== '');

    return seededKey ? String(values?.[seededKey] || '') : String(values?.[fieldKeys[0]] || '');
}

function applyBoxEditorValue(editor, values, nextValue) {
    const nextValues = { ...values };

    BOX_SIDES.forEach(({ key }) => {
        nextValues[getBoxFieldKey(editor, key)] = nextValue;
    });

    return nextValues;
}

function BoxEditor({ editor, draftValues, isLinked, onChange, onToggleLink, onResetEditor, onResetSide }) {
    return (
        <div className="ne-box-editor">
            <div className="ne-box-editor__header">
                <div>
                    <div className="ne-box-editor__title">{editor.title}</div>
                    <div className="ne-box-editor__subtitle">{isLinked ? 'Все стороны связаны' : 'Стороны редактируются отдельно'}</div>
                </div>
                <div className="ne-box-editor__actions">
                    <button className={`ne-inline-button${isLinked ? ' is-active' : ''}`} type="button" onClick={onToggleLink}>
                        {isLinked ? 'Развязать' : 'Связать'}
                    </button>
                    <button className="ne-inline-button" type="button" onClick={onResetEditor}>
                        Сбросить
                    </button>
                </div>
            </div>
            <div className="ne-box-editor__grid">
                {BOX_SIDES.map((side) => {
                    const field = {
                        key: getBoxFieldKey(editor, side.key),
                        control: editor.control,
                        placeholder: editor.placeholder,
                        options: editor.options
                    };

                    return (
                        <label key={field.key} className="ne-field ne-field--dense">
                            <span className="ne-field__label">{side.label}</span>
                            <div className="ne-box-editor__input-row">
                                <FieldControl field={field} value={draftValues[field.key] || ''} onChange={(value) => onChange(side.key, value)} />
                                <button
                                    className="ne-box-editor__reset"
                                    type="button"
                                    onClick={() => onResetSide(side.key)}
                                    title={`Сбросить сторону: ${side.label}`}
                                >
                                    x
                                </button>
                            </div>
                        </label>
                    );
                })}
            </div>
        </div>
    );
}

export function InspectorPanel({ iframeRef, frameRevision }) {
    const selected = useEditorStore((state) => state.selected);
    const pushSessionEvent = useEditorStore((state) => state.pushSessionEvent);
    const [interactionState, setInteractionState] = useState('default');
    const [targetMode, setTargetMode] = useState('node');
    const [isMetaExpanded, setIsMetaExpanded] = useState(false);
    const [styleRule, setStyleRule] = useState(() => createEmptyRule(''));
    const [draftValues, setDraftValues] = useState({});
    const [boxLinks, setBoxLinks] = useState({});
    const [status, setStatus] = useState(EMPTY_STATUS);
    const [isBusy, setIsBusy] = useState(false);
    const deviceKey = getStyleBranchKey(pageState.device);
    const currentTarget = useMemo(() => buildTargetDescriptor(selected, targetMode), [selected, targetMode]);

    useEffect(() => {
        setTargetMode('node');
        setIsMetaExpanded(false);
    }, [selected.nodeId]);

    useEffect(() => {
        let cancelled = false;

        if (!currentTarget.key) {
            setStyleRule(createEmptyRule(''));
            setDraftValues({});
            setBoxLinks({});
            setStatus(EMPTY_STATUS);
            clearPreviewCss(iframeRef.current);
            return () => {
                cancelled = true;
            };
        }

        setIsBusy(true);
        setStatus({ tone: 'muted', message: 'Загружаю сохраненное правило стилей.' });

        loadStyleRule(currentTarget).then((rule) => {
            if (cancelled) {
                return;
            }

            const nextRule = rule?.target_key ? rule : createEmptyRule(currentTarget);

            setStyleRule(nextRule);
            setDraftValues(buildDraftValues(nextRule, deviceKey, interactionState));
            setStatus({
                tone: 'ok',
                message: nextRule.id
                    ? 'Найдено существующее правило. Изменения можно сохранять сразу.'
                    : 'Правила еще нет. Первое сохранение создаст его.'
            });
        }).catch(() => {
            if (cancelled) {
                return;
            }

            const emptyRule = createEmptyRule(currentTarget);
            setStyleRule(emptyRule);
            setDraftValues(buildDraftValues(emptyRule, deviceKey, interactionState));
            setStatus({ tone: 'warn', message: 'Не удалось загрузить правило. Можно продолжить и сохранить новое.' });
        }).finally(() => {
            if (!cancelled) {
                setIsBusy(false);
            }
        });

        return () => {
            cancelled = true;
        };
    }, [currentTarget, deviceKey, iframeRef, interactionState]);

    useEffect(() => {
        if (!currentTarget.key) {
            return;
        }

        const nextDraftValues = buildDraftValues(styleRule, deviceKey, interactionState);

        setDraftValues(nextDraftValues);
        setBoxLinks(buildBoxLinkState(nextDraftValues));
    }, [currentTarget.key, deviceKey, interactionState, styleRule]);

    const currentDeclarations = useMemo(() => normalizeDraftValues(draftValues), [draftValues]);

    const previewRule = useMemo(() => {
        if (!currentTarget.key) {
            return createEmptyRule('');
        }

        return buildPreviewRule(styleRule, deviceKey, interactionState, currentDeclarations, currentTarget);
    }, [currentDeclarations, currentTarget, deviceKey, interactionState, styleRule]);

    useLayoutEffect(() => {
        if (!currentTarget.key) {
            clearPreviewCss(iframeRef.current);
            return;
        }

        applyPreviewCss(iframeRef.current, buildPreviewCss(previewRule));
    }, [currentTarget.key, frameRevision, iframeRef, previewRule]);

    async function handleSave() {
        if (!currentTarget.key) {
            setStatus({ tone: 'warn', message: 'Сначала выберите узел.' });
            return;
        }

        setIsBusy(true);
        setStatus({ tone: 'muted', message: 'Сохраняю стиль...' });

        try {
            const result = await saveStyleRule({
                targetType: currentTarget.type,
                targetKey: currentTarget.key,
                title: selected.title ? `Editor · ${selected.title}` : `Editor · ${currentTarget.key}`,
                rule: previewRule
            });

            setStyleRule(result.rule);
            setDraftValues(buildDraftValues(result.rule, deviceKey, interactionState));
            setStatus({ tone: 'ok', message: result.message });
            pushSessionEvent('Стиль сохранен', currentTarget.storagePath || currentTarget.key);
        } catch (error) {
            setStatus({ tone: 'warn', message: error?.message || 'Сохранение сейчас недоступно.' });
        } finally {
            setIsBusy(false);
        }
    }

    function handleFieldChange(fieldKey, fieldValue) {
        setDraftValues((current) => ({
            ...current,
            [fieldKey]: fieldValue
        }));
    }

    function handleBoxFieldChange(editor, sideKey, fieldValue) {
        const editorKey = getBoxEditorKey(editor);
        const fieldKey = getBoxFieldKey(editor, sideKey);

        setDraftValues((current) => {
            if (!boxLinks[editorKey]) {
                return {
                    ...current,
                    [fieldKey]: fieldValue
                };
            }

            return applyBoxEditorValue(editor, current, fieldValue);
        });
    }

    function handleBoxLinkToggle(editor) {
        const editorKey = getBoxEditorKey(editor);
        const nextLinked = !boxLinks[editorKey];

        setBoxLinks((current) => ({
            ...current,
            [editorKey]: nextLinked
        }));

        if (!nextLinked) {
            return;
        }

        setDraftValues((current) => applyBoxEditorValue(editor, current, getBoxLinkSeedValue(editor, current)));
    }

    function handleBoxReset(editor) {
        setDraftValues((current) => applyBoxEditorValue(editor, current, ''));
    }

    function handleBoxSideReset(editor, sideKey) {
        const editorKey = getBoxEditorKey(editor);
        const fieldKey = getBoxFieldKey(editor, sideKey);

        setBoxLinks((current) => {
            if (!current[editorKey]) {
                return current;
            }

            return {
                ...current,
                [editorKey]: false
            };
        });

        setDraftValues((current) => ({
            ...current,
            [fieldKey]: ''
        }));
    }

    const scopeNote = deviceKey === 'desktop'
        ? 'Desktop задает основную ветку. Планшет наследует desktop, а mobile наследует desktop + tablet, пока вы не зададите override.'
        : deviceKey === 'tablet'
            ? 'Сейчас редактируется tablet override. Если поле пустое, планшет берет значение из desktop.'
            : 'Сейчас редактируется mobile override. Если поле пустое, телефон берет значение из desktop и tablet.';

    const metaChips = [selected.role, selected.tag, selected.source, selected.mode].filter(Boolean);

    return (
        <div className="ne-panel ne-inspector">
            <div className="ne-panel__header">
                <div>
                    <h2 className="ne-panel__title">Инспектор</h2>
                    <div className="ne-panel__subtitle">Правило стилей для выбранного узла без перехода в отдельную форму.</div>
                </div>
            </div>

            <section className={`ne-inspector-fold${isMetaExpanded ? ' is-open' : ''}`}>
                <button
                    className="ne-inspector-fold__toggle"
                    type="button"
                    onClick={() => setIsMetaExpanded((current) => !current)}
                    aria-expanded={isMetaExpanded}
                >
                    <div className="ne-inspector-card">
                        <div className="ne-inspector-card__title">{selected.title || 'Еще не выбран'}</div>
                        <div className="ne-inspector-card__path">{currentTarget.storagePath || 'узел еще не выбран'}</div>
                        {metaChips.length ? (
                            <div className="ne-inspector-fold__chips">
                                {metaChips.map((chip) => (
                                    <span key={chip} className="ne-meta-chip">{chip}</span>
                                ))}
                            </div>
                        ) : null}
                    </div>
                    <span className="ne-inspector-fold__toggle-label">{isMetaExpanded ? 'Свернуть детали' : 'Показать детали'}</span>
                </button>

                {isMetaExpanded ? (
                    <div className="ne-inspector-fold__body">
                        <div className="ne-kv-list">
                            <div className="ne-kv-list__row">
                                <dt>Роль</dt>
                                <dd>{selected.role || '—'}</dd>
                            </div>
                            <div className="ne-kv-list__row">
                                <dt>Тег</dt>
                                <dd>{selected.tag || '—'}</dd>
                            </div>
                            <div className="ne-kv-list__row">
                                <dt>Источник</dt>
                                <dd>{selected.source || '—'}</dd>
                            </div>
                            <div className="ne-kv-list__row">
                                <dt>Режим</dt>
                                <dd>{selected.mode || '—'}</dd>
                            </div>
                        </div>

                        {selected.role ? (
                            <div className="ne-inspector__target-switcher">
                                <div className="ne-segmented" role="tablist" aria-label="Область применения правила">
                                    <button
                                        className={`ne-segmented__button${targetMode === 'node' ? ' is-active' : ''}`}
                                        type="button"
                                        onClick={() => setTargetMode('node')}
                                    >
                                        Только этот узел
                                    </button>
                                    <button
                                        className={`ne-segmented__button${targetMode === 'role' ? ' is-active' : ''}`}
                                        type="button"
                                        onClick={() => setTargetMode('role')}
                                    >
                                        Вся роль
                                    </button>
                                </div>
                                <div className="ne-note-card ne-note-card--tight">{currentTarget.subtitle}</div>
                            </div>
                        ) : null}

                        <div className="ne-note-card">{scopeNote}</div>
                    </div>
                ) : null}
            </section>

            <div className="ne-inspector__state-switcher">
                {['default', 'hover'].map((stateKey) => (
                    <button
                        key={stateKey}
                        className={`ne-segmented__button${interactionState === stateKey ? ' is-active' : ''}`}
                        type="button"
                        onClick={() => setInteractionState(stateKey)}
                    >
                        {getInteractionStateLabel(stateKey)}
                    </button>
                ))}
            </div>

            <div className="ne-inspector__sections">
                {STYLE_FIELD_GROUPS.map((group) => (
                    <section key={group.title} className="ne-inspector-section">
                        <div className="ne-inspector-section__title">{group.title}</div>
                        <div className="ne-inspector-grid">
                            {group.fields.map((field) => (
                                <label key={field.key} className="ne-field ne-field--dense">
                                    <span className="ne-field__label">{field.label}</span>
                                    <FieldControl field={field} value={draftValues[field.key] || ''} onChange={(value) => handleFieldChange(field.key, value)} />
                                </label>
                            ))}
                        </div>
                    </section>
                ))}

                {BOX_GROUPS.map((group) => (
                    <section key={group.title} className="ne-inspector-section">
                        <div className="ne-inspector-section__title">{group.title}</div>
                        <div className="ne-box-editor-stack">
                            {group.editors.map((editor) => (
                                <BoxEditor
                                    key={`${group.title}-${editor.title}`}
                                    editor={editor}
                                    draftValues={draftValues}
                                    isLinked={Boolean(boxLinks[getBoxEditorKey(editor)])}
                                    onChange={(sideKey, value) => handleBoxFieldChange(editor, sideKey, value)}
                                    onToggleLink={() => handleBoxLinkToggle(editor)}
                                    onResetEditor={() => handleBoxReset(editor)}
                                    onResetSide={(sideKey) => handleBoxSideReset(editor, sideKey)}
                                />
                            ))}
                        </div>
                    </section>
                ))}
            </div>

            <div className={`ne-status-card ne-status-card--${status.tone || 'muted'}`}>
                {status.message}
            </div>

            <div className="ne-action-stack">
                <button className="ne-button" type="button" disabled={isBusy || !currentTarget.key} onClick={handleSave}>
                    {isBusy ? 'Сохраняю...' : 'Сохранить стиль'}
                </button>
                <a className="ne-button ne-button--ghost" href={buildRulesUrl(currentTarget.storagePath)} target="_blank" rel="noreferrer">
                    Открыть правило CSS
                </a>
            </div>
        </div>
    );
}