import { FONT_IMPORTS, builderState } from './constants.js';

const MEDIA_MAP = {
    mobile: '@media (max-width: 767.98px)',
    tablet: '@media (min-width: 768px) and (max-width: 991.98px)',
    desktop: '@media (min-width: 992px)'
};

const DEVICE_BRANCH_ORDER = ['desktop', 'tablet', 'mobile'];

const STATE_SUFFIX = {
    hover: ':hover',
    active: ':active',
    focus: ':focus',
    'focus-visible': ':focus-visible',
    visited: ':visited',
    before: '::before',
    after: '::after'
};

const PIXEL_PROPERTIES = new Set([
    'font-size',
    'letter-spacing',
    'gap',
    'max-width',
    'min-height',
    'border-radius',
    'padding-top',
    'padding-right',
    'padding-bottom',
    'padding-left',
    'margin-top',
    'margin-right',
    'margin-bottom',
    'margin-left',
    'border-top-width',
    'border-right-width',
    'border-bottom-width',
    'border-left-width'
]);

const COLOR_PROPERTIES = new Set([
    'color',
    'background-color',
    'border-top-color',
    'border-right-color',
    'border-bottom-color',
    'border-left-color'
]);

export function buildNodeSelector(targetKey) {
    if (!targetKey) {
        return '';
    }

    return `[data-nordic-id="${escapeCssAttributeValue(targetKey)}"]`;
}

export function buildRoleSelector(targetKey) {
    if (!targetKey) {
        return '';
    }

    return `[data-nordic-role="${escapeCssAttributeValue(targetKey)}"]`;
}

export function createEmptyRule(targetInput = '') {
    const target = normalizeTargetInput(targetInput);

    return {
        id: 0,
        title: '',
        path: buildTargetSelector(target),
        target_type: target.type,
        target_key: target.key,
        styles: {},
        custom: {}
    };
}

export function buildRulesUrl(pathValue) {
    if (!builderState.rules_url || !pathValue) {
        return String(builderState.rules_url || '#');
    }

    const separator = builderState.rules_url.includes('?') ? '&' : '?';
    return `${builderState.rules_url}${separator}${new URLSearchParams({ path: pathValue }).toString()}`;
}

export async function loadStyleRule(targetInput) {
    const target = normalizeTargetInput(targetInput);

    if (!builderState.style_rule_url || !builderState.csrf_token || !window.fetch || !target.key) {
        return createEmptyRule(target);
    }

    const response = await window.fetch(builderState.style_rule_url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: serializeBody({
            csrf_token: builderState.csrf_token,
            mode: 'load',
            target_type: target.type,
            target_key: target.key
        })
    });

    const result = await response.json();

    return result?.rule ? normalizeLoadedRule(result.rule, target) : createEmptyRule(target);
}

export async function saveStyleRule({ targetType = 'node', targetKey, title, rule }) {
    const target = normalizeTargetInput({ type: targetType, key: targetKey });

    if (!builderState.style_rule_url || !builderState.csrf_token || !window.fetch || !target.key) {
        throw new Error('save unavailable');
    }

    const response = await window.fetch(builderState.style_rule_url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: serializeBody({
            csrf_token: builderState.csrf_token,
            mode: 'save',
            target_type: target.type,
            target_key: target.key,
            title,
            rule_id: rule?.id || 0,
            styles: JSON.stringify(rule?.styles || {}),
            custom: JSON.stringify(rule?.custom || {})
        })
    });

    const result = await response.json();

    if (!result || result.error) {
        throw new Error(result?.message || 'save failed');
    }

    return {
        rule: result.rule ? normalizeLoadedRule(result.rule, target) : createEmptyRule(target),
        message: result.message || 'Стиль сохранен.'
    };
}

export function getMergedDeclarations(rule, deviceKey, stateKey) {
    return getInheritedDeviceKeys(deviceKey).reduce((accumulator, branchKey) => ({
        ...accumulator,
        ...getBranch(rule, branchKey, stateKey)
    }), {});
}

export function setBranch(rule, deviceKey, stateKey, declarations) {
    const nextRule = cloneRule(rule);
    const normalizedDeviceKey = normalizeDeviceKey(deviceKey);

    if (!nextRule.styles[normalizedDeviceKey]) {
        nextRule.styles[normalizedDeviceKey] = {};
    }

    if (!Object.keys(declarations).length) {
        delete nextRule.styles[normalizedDeviceKey][stateKey];

        if (!Object.keys(nextRule.styles[normalizedDeviceKey]).length) {
            delete nextRule.styles[normalizedDeviceKey];
        }

        return nextRule;
    }

    nextRule.styles[normalizedDeviceKey][stateKey] = declarations;

    return nextRule;
}

export function normalizeDraftValues(values) {
    const declarations = {};

    Object.entries(values || {}).forEach(([propertyName, rawValue]) => {
        const value = normalizeStyleValue(propertyName, rawValue);

        if (!value) {
            return;
        }

        declarations[propertyName] = value;
    });

    if ((declarations['border-top-width'] || declarations['border-right-width'] || declarations['border-bottom-width'] || declarations['border-left-width'] || declarations['border-top-color'] || declarations['border-right-color'] || declarations['border-bottom-color'] || declarations['border-left-color']) && !declarations['border-style']) {
        declarations['border-style'] = 'solid';
    }

    ['top', 'right', 'bottom', 'left'].forEach((side) => {
        const widthKey = `border-${side}-width`;
        const colorKey = `border-${side}-color`;
        const styleKey = `border-${side}-style`;

        if ((declarations[widthKey] || declarations[colorKey]) && !declarations[styleKey] && !declarations['border-style']) {
            declarations[styleKey] = 'solid';
        }
    });

    return declarations;
}

export function buildPreviewRule(rule, deviceKey, stateKey, declarations, targetInput = '') {
    const target = normalizeTargetInput(targetInput);
    const baseRule = cloneRule(rule?.path ? normalizeLoadedRule(rule, target) : createEmptyRule(target));

    if (!baseRule.path) {
        baseRule.path = buildTargetSelector(baseRule.target_key ? { type: baseRule.target_type, key: baseRule.target_key } : target);
    }

    if (!baseRule.target_key && target.key) {
        baseRule.target_key = target.key;
    }

    if (!baseRule.target_type && target.type) {
        baseRule.target_type = target.type;
    }

    return setBranch(baseRule, deviceKey, stateKey, declarations);
}

export function buildPreviewCss(rule) {
    if (!rule?.path) {
        return '';
    }

    const normalizedRule = normalizeLoadedRule(rule, { type: rule?.target_type, key: rule?.target_key });
    const fontImportCss = buildFontImportCss(collectFontImportUrls(normalizedRule));

    let css = '';

    DEVICE_BRANCH_ORDER.forEach((device) => {
        const deviceStates = collectStateKeysForDevice(normalizedRule, device);
        let deviceCss = '';

        deviceStates.forEach((stateKey) => {
            const declarations = getMergedDeclarations(normalizedRule, device, stateKey);
            const customCss = getMergedCustomCss(normalizedRule, device, stateKey);
            let block = '';

            Object.entries(declarations).forEach(([propertyName, propertyValue]) => {
                const value = String(propertyValue || '').trim();

                if (!value) {
                    return;
                }

                block += `${propertyName}:${value};`;
            });

            if (customCss) {
                block += customCss;
            }

            if (block) {
                deviceCss += `${normalizedRule.path}${STATE_SUFFIX[stateKey] || ''}{${block}}`;
            }
        });

        css += wrapCss(device, deviceCss);
    });

    return `${fontImportCss}${css}`;
}

export function applyPreviewCss(iframe, cssText) {
    const doc = iframe?.contentDocument;

    if (!doc) {
        return;
    }

    let node = doc.getElementById('nordic-editor-shell-preview');

    if (!node) {
        node = doc.createElement('style');
        node.id = 'nordic-editor-shell-preview';
        (doc.head || doc.documentElement).appendChild(node);
    }

    node.textContent = cssText;
}

export function clearPreviewCss(iframe) {
    applyPreviewCss(iframe, '');
}

export function toColorInputValue(value) {
    const normalized = String(value || '').trim();

    if (/^#[0-9a-f]{6}$/i.test(normalized)) {
        return normalized;
    }

    if (/^#[0-9a-f]{3}$/i.test(normalized)) {
        return `#${normalized[1]}${normalized[1]}${normalized[2]}${normalized[2]}${normalized[3]}${normalized[3]}`;
    }

    return '';
}

function getBranch(rule, deviceKey, stateKey) {
    if (!rule?.styles?.[deviceKey]?.[stateKey]) {
        return {};
    }

    return { ...rule.styles[deviceKey][stateKey] };
}

function normalizeLoadedRule(rule, targetInput) {
    const fallbackTarget = normalizeTargetInput(targetInput, rule?.target_type || 'node');
    const targetType = normalizeTargetType(rule?.target_type || fallbackTarget.type);
    const targetKey = String(rule?.target_key || fallbackTarget.key || '').trim();
    const target = normalizeTargetInput({ type: targetType, key: targetKey }, targetType);

    return {
        id: Number.parseInt(rule.id || 0, 10) || 0,
        title: String(rule.title || '').trim(),
        path: String(rule.path || buildTargetSelector(target)),
        target_type: target.type,
        target_key: target.key,
        styles: normalizeStyleBranches(rule.styles && typeof rule.styles === 'object' ? rule.styles : {}),
        custom: normalizeCustomBranches(rule.custom && typeof rule.custom === 'object' ? rule.custom : {})
    };
}

function wrapCss(device, cssText) {
    if (!cssText.trim()) {
        return '';
    }

    return MEDIA_MAP[device] ? `${MEDIA_MAP[device]}{${cssText}}` : cssText;
}

function cloneRule(rule) {
    return JSON.parse(JSON.stringify(rule || createEmptyRule('')));
}

function normalizeTargetInput(targetInput, fallbackType = 'node') {
    if (typeof targetInput === 'string') {
        return {
            type: normalizeTargetType(fallbackType),
            key: String(targetInput || '').trim()
        };
    }

    return {
        type: normalizeTargetType(targetInput?.type || targetInput?.targetType || fallbackType),
        key: String(targetInput?.key || targetInput?.targetKey || '').trim()
    };
}

function normalizeTargetType(targetType) {
    return targetType === 'role' ? 'role' : 'node';
}

function buildTargetSelector(target) {
    if (!target?.key) {
        return '';
    }

    return target.type === 'role' ? buildRoleSelector(target.key) : buildNodeSelector(target.key);
}

function normalizeDeviceKey(deviceKey) {
    return deviceKey === 'tablet' || deviceKey === 'mobile' ? deviceKey : 'desktop';
}

function getInheritedDeviceKeys(deviceKey) {
    const normalizedDeviceKey = normalizeDeviceKey(deviceKey);

    if (normalizedDeviceKey === 'mobile') {
        return ['desktop', 'tablet', 'mobile'];
    }

    if (normalizedDeviceKey === 'tablet') {
        return ['desktop', 'tablet'];
    }

    return ['desktop'];
}

function collectStateKeysForDevice(rule, deviceKey) {
    const keys = [];

    getInheritedDeviceKeys(deviceKey).forEach((branchKey) => {
        Object.keys(rule?.styles?.[branchKey] || {}).forEach((stateKey) => keys.push(stateKey));
        Object.keys(rule?.custom?.[branchKey] || {}).forEach((stateKey) => keys.push(stateKey));
    });

    if (!keys.length) {
        return ['default'];
    }

    const ordered = [];
    ['default', 'hover', 'active', 'focus', 'focus-visible', 'visited', 'before', 'after'].forEach((stateKey) => {
        if (keys.includes(stateKey) && !ordered.includes(stateKey)) {
            ordered.push(stateKey);
        }
    });

    keys.forEach((stateKey) => {
        if (!ordered.includes(stateKey)) {
            ordered.push(stateKey);
        }
    });

    return ordered;
}

function getMergedCustomCss(rule, deviceKey, stateKey) {
    return getInheritedDeviceKeys(deviceKey)
        .map((branchKey) => String(rule?.custom?.[branchKey]?.[stateKey] || '').trim())
        .filter(Boolean)
        .join(' ');
}

function collectFontImportUrls(rule) {
    const urls = new Set();

    Object.values(rule?.styles || {}).forEach((branch) => {
        Object.values(branch || {}).forEach((stateDeclarations) => {
            const fontFamily = String(stateDeclarations?.['font-family'] || '').trim();
            const importUrl = FONT_IMPORTS[fontFamily];

            if (importUrl) {
                urls.add(importUrl);
            }
        });
    });

    return Array.from(urls);
}

function buildFontImportCss(urls) {
    if (!urls.length) {
        return '';
    }

    return `${urls.map((url) => `@import url("${url}");`).join('')}`;
}

function normalizeStyleBranches(styles) {
    if (!styles || typeof styles !== 'object') {
        return {};
    }

    if (isDeviceAwarePayload(styles)) {
        const baseBranch = sanitizeStyleBranch(styles.base);
        const desktopBranch = mergeStyleBranches(baseBranch, sanitizeStyleBranch(styles.desktop));
        const tabletBranch = sanitizeStyleBranch(styles.tablet);
        const mobileBranch = sanitizeStyleBranch(styles.mobile);

        return compactBranches({
            desktop: desktopBranch,
            tablet: tabletBranch,
            mobile: mobileBranch
        });
    }

    return compactBranches({ desktop: sanitizeStyleBranch(styles) });
}

function normalizeCustomBranches(custom) {
    if (!custom || typeof custom !== 'object') {
        return {};
    }

    if (isDeviceAwarePayload(custom)) {
        const baseBranch = sanitizeCustomBranch(custom.base);
        const desktopBranch = mergeCustomBranches(baseBranch, sanitizeCustomBranch(custom.desktop));
        const tabletBranch = sanitizeCustomBranch(custom.tablet);
        const mobileBranch = sanitizeCustomBranch(custom.mobile);

        return compactBranches({
            desktop: desktopBranch,
            tablet: tabletBranch,
            mobile: mobileBranch
        });
    }

    return compactBranches({ desktop: sanitizeCustomBranch(custom) });
}

function sanitizeStyleBranch(branch) {
    const normalized = {};

    if (!branch || typeof branch !== 'object') {
        return normalized;
    }

    Object.entries(branch).forEach(([stateKey, declarations]) => {
        if (!declarations || typeof declarations !== 'object' || Array.isArray(declarations)) {
            return;
        }

        const stateDeclarations = {};

        Object.entries(declarations).forEach(([propertyName, propertyValue]) => {
            const value = String(propertyValue || '').trim();

            if (!value) {
                return;
            }

            stateDeclarations[propertyName] = value;
        });

        if (Object.keys(stateDeclarations).length) {
            normalized[stateKey] = stateDeclarations;
        }
    });

    return normalized;
}

function sanitizeCustomBranch(branch) {
    const normalized = {};

    if (!branch || typeof branch !== 'object') {
        return normalized;
    }

    Object.entries(branch).forEach(([stateKey, cssText]) => {
        if (cssText && typeof cssText !== 'object') {
            const value = String(cssText || '').trim();

            if (value) {
                normalized[stateKey] = value;
            }
        }
    });

    return normalized;
}

function mergeStyleBranches(baseBranch, overrideBranch) {
    const merged = { ...baseBranch };

    Object.entries(overrideBranch || {}).forEach(([stateKey, declarations]) => {
        merged[stateKey] = {
            ...(merged[stateKey] || {}),
            ...declarations
        };
    });

    return merged;
}

function mergeCustomBranches(baseBranch, overrideBranch) {
    const merged = { ...baseBranch };

    Object.entries(overrideBranch || {}).forEach(([stateKey, cssText]) => {
        const baseText = String(merged[stateKey] || '').trim();
        const nextText = String(cssText || '').trim();

        merged[stateKey] = [baseText, nextText].filter(Boolean).join(' ');
    });

    return merged;
}

function compactBranches(branches) {
    const normalized = {};

    Object.entries(branches || {}).forEach(([branchKey, states]) => {
        if (states && Object.keys(states).length) {
            normalized[branchKey] = states;
        }
    });

    return normalized;
}

function isDeviceAwarePayload(payload) {
    const keys = Object.keys(payload || {});
    const hasDevice = keys.some((key) => ['base', 'desktop', 'tablet', 'mobile'].includes(key));
    const hasState = keys.some((key) => ['default', 'hover', 'active', 'focus', 'focus-visible', 'visited', 'before', 'after'].includes(key));

    return hasDevice && !hasState;
}

function normalizeStyleValue(propertyName, rawValue) {
    const value = String(rawValue || '').trim();

    if (!value) {
        return '';
    }

    if (PIXEL_PROPERTIES.has(propertyName) && /^-?\d+(\.\d+)?$/.test(value)) {
        return `${value}px`;
    }

    if (COLOR_PROPERTIES.has(propertyName) && /^[0-9a-f]{3}([0-9a-f]{3})?$/i.test(value)) {
        return `#${value}`;
    }

    return value;
}

function escapeCssAttributeValue(value) {
    return String(value || '').replace(/\\/g, '\\\\').replace(/"/g, '\\"');
}

function serializeBody(payload) {
    return Object.keys(payload).map((key) => `${encodeURIComponent(key)}=${encodeURIComponent(String(payload[key]))}`).join('&');
}