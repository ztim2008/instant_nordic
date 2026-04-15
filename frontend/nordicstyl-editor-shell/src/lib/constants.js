export const builderState = window.NORDIC_EDITOR_STATE || window.NORDIC_BUILDER_STATE || {};
export const pageState = builderState.page || {};

export const workspaceStorageKey = [
    'nordic-editor-shell-layout:v3',
    pageState.template || 'template',
    pageState.uri_raw || '/'
].join(':');

export const DEVICE_LABELS = {
    desktop: 'Desktop',
    tablet: 'Tablet',
    mobile: 'Mobile'
};

export const INTERACTION_STATE_LABELS = {
    default: 'Обычный',
    hover: 'Наведение'
};

export const FONT_PRESETS = [
    { value: '', label: 'Как в шаблоне' },
    { value: '"IBM Plex Sans", "Segoe UI", sans-serif', label: 'IBM Plex Sans' },
    { value: '"Manrope", "Segoe UI", sans-serif', label: 'Manrope' },
    { value: '"Rubik", "Segoe UI", sans-serif', label: 'Rubik' },
    { value: '"Noto Sans", "Segoe UI", sans-serif', label: 'Noto Sans' },
    { value: '"PT Sans", "Segoe UI", sans-serif', label: 'PT Sans' },
    { value: '"PT Serif", Georgia, serif', label: 'PT Serif' },
    { value: '"JetBrains Mono", monospace', label: 'JetBrains Mono' },
    { value: 'inherit', label: 'Наследовать' }
];

export const LOCAL_FONT_STYLESHEET_URL = '/templates/default/controllers/nordicstyl/fonts/fonts.css';

export const FONT_IMPORTS = {
    '"IBM Plex Sans", "Segoe UI", sans-serif': LOCAL_FONT_STYLESHEET_URL,
    '"Manrope", "Segoe UI", sans-serif': LOCAL_FONT_STYLESHEET_URL,
    '"Rubik", "Segoe UI", sans-serif': LOCAL_FONT_STYLESHEET_URL,
    '"Noto Sans", "Segoe UI", sans-serif': LOCAL_FONT_STYLESHEET_URL,
    '"PT Sans", "Segoe UI", sans-serif': LOCAL_FONT_STYLESHEET_URL,
    '"PT Serif", Georgia, serif': LOCAL_FONT_STYLESHEET_URL,
    '"JetBrains Mono", monospace': LOCAL_FONT_STYLESHEET_URL
};

export const FONT_WEIGHT_PRESETS = [
    { value: '', label: 'Как в шаблоне' },
    { value: '400', label: '400 Regular' },
    { value: '500', label: '500 Medium' },
    { value: '600', label: '600 Semibold' },
    { value: '700', label: '700 Bold' }
];

export const TEXT_ALIGN_PRESETS = [
    { value: '', label: 'Как в шаблоне' },
    { value: 'left', label: 'Слева' },
    { value: 'center', label: 'По центру' },
    { value: 'right', label: 'Справа' }
];

export const TEXT_TRANSFORM_PRESETS = [
    { value: '', label: 'Как в шаблоне' },
    { value: 'none', label: 'Без преобразования' },
    { value: 'uppercase', label: 'ВСЕ ПРОПИСНЫЕ' },
    { value: 'lowercase', label: 'все строчные' },
    { value: 'capitalize', label: 'Каждое Слово' }
];

export const DISPLAY_PRESETS = [
    { value: '', label: 'Как в шаблоне' },
    { value: 'block', label: 'block' },
    { value: 'inline-block', label: 'inline-block' },
    { value: 'flex', label: 'flex' },
    { value: 'grid', label: 'grid' },
    { value: 'none', label: 'none' }
];

export const BORDER_STYLE_PRESETS = [
    { value: '', label: 'Как в шаблоне' },
    { value: 'solid', label: 'solid' },
    { value: 'dashed', label: 'dashed' },
    { value: 'dotted', label: 'dotted' },
    { value: 'none', label: 'none' }
];

export const STYLE_FIELD_GROUPS = [
    {
        title: 'Текст',
        fields: [
            { key: 'color', label: 'Цвет текста', control: 'color', placeholder: '#173042' },
            { key: 'font-family', label: 'Шрифт', control: 'select', options: FONT_PRESETS },
            { key: 'font-size', label: 'Размер', control: 'text', placeholder: '18px' },
            { key: 'font-weight', label: 'Насыщенность', control: 'select', options: FONT_WEIGHT_PRESETS },
            { key: 'line-height', label: 'Межстрочный', control: 'text', placeholder: '1.4' },
            { key: 'letter-spacing', label: 'Трекинг', control: 'text', placeholder: '0.02em' },
            { key: 'text-align', label: 'Выравнивание', control: 'select', options: TEXT_ALIGN_PRESETS },
            { key: 'text-transform', label: 'Регистр', control: 'select', options: TEXT_TRANSFORM_PRESETS },
            { key: 'opacity', label: 'Прозрачность', control: 'text', placeholder: '1' }
        ]
    },
    {
        title: 'Блок',
        fields: [
            { key: 'background-color', label: 'Фон', control: 'color', placeholder: '#ffffff' },
            { key: 'border-radius', label: 'Скругление', control: 'text', placeholder: '4px' },
            { key: 'max-width', label: 'Макс. ширина', control: 'text', placeholder: '1200px' },
            { key: 'min-height', label: 'Мин. высота', control: 'text', placeholder: '56px' }
        ]
    },
    {
        title: 'Раскладка',
        fields: [
            { key: 'display', label: 'Отображение', control: 'select', options: DISPLAY_PRESETS },
            { key: 'gap', label: 'Интервал', control: 'text', placeholder: '12px' },
            { key: 'align-items', label: 'Выравнивание по оси', control: 'text', placeholder: 'center' },
            { key: 'justify-content', label: 'Распределение', control: 'text', placeholder: 'space-between' }
        ]
    },
    {
        title: 'Рамка',
        fields: [
            { key: 'box-shadow', label: 'Тень', control: 'text', placeholder: '0 8px 24px rgba(0,0,0,0.12)' }
        ]
    }
];

export function getDeviceLabel(key) {
    return DEVICE_LABELS[key] || DEVICE_LABELS.desktop;
}

export function getInteractionStateLabel(key) {
    return INTERACTION_STATE_LABELS[key] || INTERACTION_STATE_LABELS.default;
}

export function getDeviceModeTitle(deviceKey) {
    return getDeviceLabel(typeof deviceKey === 'string' ? deviceKey : (deviceKey?.key || 'desktop'));
}

export function getStyleBranchKey(deviceKey) {
    return deviceKey === 'tablet' || deviceKey === 'mobile' ? deviceKey : 'desktop';
}

export function getViewportFrameMode(deviceKey) {
    if (deviceKey === 'tablet' || deviceKey === 'mobile') {
        return deviceKey;
    }

    return 'desktop';
}

export function buildEditorFrameUrl(sourceUrl) {
    if (!sourceUrl) {
        return '';
    }

    try {
        const url = new URL(sourceUrl, window.location.origin);
        url.searchParams.set('nordicstyl_editor', '1');
        return url.toString();
    } catch (error) {
        return sourceUrl;
    }
}