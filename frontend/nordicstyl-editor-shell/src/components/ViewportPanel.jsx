import { buildEditorFrameUrl, getDeviceLabel, getViewportFrameMode, pageState, builderState } from '../lib/constants.js';
import { useEditorStore } from '../store/editorStore.js';

export function ViewportPanel({ iframeRef, onFrameLoad, onRefreshLayers, onReloadViewport }) {
    const selected = useEditorStore((state) => state.selected);
    const frameState = useEditorStore((state) => state.frameState);
    const viewportMode = getViewportFrameMode(pageState.device);

    return (
        <div className="ne-viewport-panel">
            <div className="ne-viewport-panel__bar">
                <div>
                    <div className="ne-viewport-panel__title">Живой экран</div>
                    <div className="ne-viewport-panel__subtitle">
                        Реальная страница внутри iframe. Клики по узлам синхронизируются со слоями и инспектором.
                    </div>
                </div>

                <div className="ne-viewport-panel__chips">
                    <span className="ne-chip">{pageState.uri || '/'}</span>
                    <span className="ne-chip">{getDeviceLabel(pageState.device)}</span>
                    <span className="ne-chip ne-chip--strong">{selected.storagePath || 'узел не выбран'}</span>
                    <button className="ne-icon-button" type="button" onClick={onRefreshLayers}>Слои</button>
                    <button className="ne-icon-button" type="button" onClick={onReloadViewport}>Обновить</button>
                </div>
            </div>

            <div className={`ne-viewport-frame-shell is-${frameState}`} data-device={viewportMode}>
                <div className="ne-viewport-frame-wrap">
                    <iframe
                        ref={iframeRef}
                        className="ne-viewport-frame"
                        src={buildEditorFrameUrl(builderState.picker_frame_url || '')}
                        title="Nordic editor холст"
                        referrerPolicy="no-referrer"
                        onLoad={onFrameLoad}
                    />
                </div>
            </div>
        </div>
    );
}