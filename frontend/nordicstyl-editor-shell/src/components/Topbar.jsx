import { builderState, pageState, getDeviceLabel, getDeviceModeTitle } from '../lib/constants.js';
import { useEditorStore } from '../store/editorStore.js';

export function Topbar({ onRefreshLayers }) {
    const frameMessage = useEditorStore((state) => state.frameMessage);
    const frameTone = useEditorStore((state) => state.frameTone);
    const selected = useEditorStore((state) => state.selected);
    const activePageUrl = (builderState.page_targets || []).find((target) => target.is_active)?.url || '';

    return (
        <header className="ne-shell__topbar">
            <div className="ne-shell__headline">
                <div className="ne-shell__eyebrow">Рабочее пространство</div>
                <h1 className="ne-shell__title">Nordic Editor</h1>
                <div className="ne-shell__subtitle">
                    <span>Шаблон: <strong>{pageState.template || 'modern'}</strong></span>
                    <span>Страница: <strong>{pageState.uri || '/'}</strong></span>
                    <span>Экран: <strong>{getDeviceLabel(pageState.device)}</strong></span>
                </div>
            </div>

            <div className="ne-shell__controls">
                <label className="ne-field ne-field--wide">
                    <span className="ne-field__label">Страница</span>
                    <select
                        className="ne-input ne-input--select"
                        defaultValue={activePageUrl}
                        onChange={(event) => {
                            if (event.target.value) {
                                window.location.assign(event.target.value);
                            }
                        }}
                    >
                        {(builderState.page_targets || []).map((target) => (
                            <option key={target.url} value={target.url}>
                                {target.title}{target.uri ? ` · ${target.uri}` : ''}
                            </option>
                        ))}
                    </select>
                </label>

                <div className="ne-field">
                    <span className="ne-field__label">Экран</span>
                    <div className="ne-segmented">
                        {(builderState.device_modes || []).map((deviceMode) => (
                            <button
                                key={deviceMode.key}
                                className={`ne-segmented__button${deviceMode.is_active ? ' is-active' : ''}`}
                                type="button"
                                onClick={() => window.location.assign(deviceMode.url)}
                            >
                                {getDeviceModeTitle(deviceMode.key)}
                            </button>
                        ))}
                    </div>
                </div>
            </div>

            <div className="ne-shell__actions">
                <button className="ne-button ne-button--ghost" type="button" onClick={onRefreshLayers}>Обновить слои</button>
                {builderState.schema_url ? <a className="ne-button ne-button--ghost" href={builderState.schema_url}>Схема</a> : null}
                {builderState.design_url ? <a className="ne-button ne-button--ghost" href={builderState.design_url}>Дизайн</a> : null}
                {builderState.rules_url ? <a className="ne-button ne-button--ghost" href={builderState.rules_url}>Правила</a> : null}
            </div>

            <div className={`ne-shell__status ne-shell__status--${frameTone}`}>
                <div className="ne-shell__status-line">{frameMessage}</div>
                <div className="ne-shell__status-meta">{selected.storagePath || 'узел еще не выбран'}</div>
            </div>
        </header>
    );
}