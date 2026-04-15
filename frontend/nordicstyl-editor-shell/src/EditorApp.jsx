import { useCallback, useEffect, useRef, useState } from 'react';
import { Layout, Model } from 'flexlayout-react';
import { builderState, getDeviceLabel, getInteractionStateLabel, pageState } from './lib/constants.js';
import { escapeAttributeValue, buildLayerTree } from './lib/dom.js';
import { loadLayoutModel, saveLayoutModel } from './lib/layout.js';
import { clearPreviewCss } from './lib/rules.js';
import { useEditorStore } from './store/editorStore.js';
import { Topbar } from './components/Topbar.jsx';
import { LayersPanel } from './components/LayersPanel.jsx';
import { HistoryPanel } from './components/HistoryPanel.jsx';
import { ViewportPanel } from './components/ViewportPanel.jsx';
import { InspectorPanel } from './components/InspectorPanel.jsx';

export function EditorApp() {
    const iframeRef = useRef(null);
    const workspaceRef = useRef(null);
    const modelRef = useRef(Model.fromJson(loadLayoutModel()));
    const [frameRevision, setFrameRevision] = useState(0);
    const setFrameState = useEditorStore((state) => state.setFrameState);
    const setLayerTree = useEditorStore((state) => state.setLayerTree);
    const setSelected = useEditorStore((state) => state.setSelected);
    const setRestoreBusyId = useEditorStore((state) => state.setRestoreBusyId);
    const pushSessionEvent = useEditorStore((state) => state.pushSessionEvent);

    const refreshLayers = useCallback(() => {
        const frame = iframeRef.current;

        if (!frame?.contentDocument) {
            setFrameState('loading', 'Iframe страницы еще не готов для чтения DOM.', 'muted');
            return;
        }

        try {
            const { tree, map } = buildLayerTree(frame.contentDocument);
            setLayerTree(tree, map);
            pushSessionEvent('Слои синхронизированы', `${Object.keys(map).length} узлов`);
            setFrameState(
                'ready',
                tree.length
                    ? `Слои синхронизированы: ${Object.keys(map).length} узлов из DOM страницы.`
                    : 'DOM страницы загружен, но размеченных узлов пока не найдено.',
                tree.length ? 'ok' : 'warn'
            );
        } catch (error) {
            setFrameState('error', 'Не удалось собрать layers tree из iframe.', 'warn');
        }
    }, [pushSessionEvent, setFrameState, setLayerTree]);

    const reloadViewport = useCallback(() => {
        const frame = iframeRef.current;

        if (!frame?.contentWindow) {
            return;
        }

        clearPreviewCss(frame);
        setFrameState('loading', 'Перезагружаю холст.', 'muted');
        pushSessionEvent('Холст перезагружен', pageState.uri || '/');
        frame.contentWindow.location.reload();
    }, [pushSessionEvent, setFrameState]);

    const handleFrameLoad = useCallback(() => {
        setFrameRevision((value) => value + 1);
        refreshLayers();
    }, [refreshLayers]);

    const selectLayer = useCallback((nodeId) => {
        const frame = iframeRef.current;
        const layerNode = useEditorStore.getState().layerMap[nodeId];

        if (!frame?.contentDocument || !frame.contentWindow || !nodeId) {
            return;
        }

        const element = frame.contentDocument.querySelector(`[data-nordic-id="${escapeAttributeValue(nodeId)}"]`);

        if (!element) {
            setFrameState('error', 'Выбранный слой уже отсутствует в DOM страницы.', 'warn');
            return;
        }

        setSelected({
            nodeId,
            storagePath: `node:${nodeId}`,
            title: layerNode?.label || nodeId,
            role: layerNode?.role || '',
            tag: layerNode?.tag || '',
            text: layerNode?.text || '',
            source: 'Панель слоев',
            mode: `${getDeviceLabel(pageState.device)} · ${getInteractionStateLabel('default')}`
        });
        pushSessionEvent('Узел выбран', layerNode?.label || nodeId);
        element.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'smooth' });
        element.dispatchEvent(new frame.contentWindow.MouseEvent('click', {
            bubbles: true,
            cancelable: true,
            view: frame.contentWindow
        }));
    }, [pushSessionEvent, setFrameState, setSelected]);

    const restoreRevision = useCallback(async (revisionId) => {
        if (!revisionId || !builderState.restore_url) {
            return;
        }

        setRestoreBusyId(revisionId);
        setFrameState('loading', `Восстанавливаю ревизию #${revisionId}.`, 'muted');
        pushSessionEvent('Запуск восстановления', `Ревизия #${revisionId}`);

        try {
            const payload = new URLSearchParams({
                csrf_token: builderState.csrf_token || '',
                template: pageState.template || '',
                uri: pageState.uri_raw || '/',
                source_template: pageState.layout_source || '',
                revision_id: String(revisionId)
            });
            const response = await window.fetch(builderState.restore_url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload.toString()
            });
            const result = await response.json();

            if (!result || result.error) {
                pushSessionEvent('Восстановление не выполнено', result?.message || `Ревизия #${revisionId}`);
                setFrameState('error', result?.message || 'Восстановление не выполнилось.', 'warn');
                return;
            }

            pushSessionEvent('Ревизия восстановлена', `#${revisionId}`);
            setFrameState('ready', result.message || `Ревизия #${revisionId} восстановлена.`, 'ok');
            reloadViewport();
        } catch (error) {
            pushSessionEvent('Восстановление не выполнено', `Ревизия #${revisionId}`);
            setFrameState('error', 'Восстановление сейчас недоступно.', 'warn');
        } finally {
            setRestoreBusyId(0);
        }
    }, [pushSessionEvent, reloadViewport, setFrameState, setRestoreBusyId]);

    useEffect(() => {
        const workspaceNode = workspaceRef.current;

        if (!workspaceNode || typeof MutationObserver === 'undefined') {
            return undefined;
        }

        const hideFlexLayoutSizer = () => {
            workspaceNode.querySelectorAll('.flexlayout__border_sizer').forEach((node) => {
                node.setAttribute('aria-hidden', 'true');
                node.setAttribute('role', 'presentation');

                if (node.textContent !== '\u00a0') {
                    node.textContent = '\u00a0';
                }
            });
        };

        hideFlexLayoutSizer();

        const observer = new MutationObserver(() => hideFlexLayoutSizer());
        observer.observe(workspaceNode, { childList: true, subtree: true });

        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        function handleMessage(event) {
            const frame = iframeRef.current;
            const data = event?.data || null;

            if (!frame || event.source !== frame.contentWindow || !data) {
                return;
            }

            if (!/^nordicstyl-picker/.test(String(data.type || ''))) {
                return;
            }

            const storagePath = String(data.storage_path || '').trim();
            const rawNodeId = String(data.target_key || '').trim();
            const nodeId = rawNodeId || (storagePath.startsWith('node:') ? storagePath.slice(5) : '');
            const stateLabel = getInteractionStateLabel(String(data.state || 'default'));

            setSelected({
                nodeId,
                storagePath,
                title: String(data.title || '').trim(),
                source: String(data.source_label || '').trim(),
                mode: `${getDeviceLabel(pageState.device)} · ${stateLabel}`
            });

            if (data.type === 'nordicstyl-picker' && storagePath) {
                pushSessionEvent('Цель выбрана', storagePath);
            }

            if (data.message) {
                setFrameState('ready', String(data.message), data.tone || 'ok');
            }
        }

        window.addEventListener('message', handleMessage);

        return () => window.removeEventListener('message', handleMessage);
    }, [pushSessionEvent, setFrameState, setSelected]);

    const factory = useCallback((node) => {
        const component = node.getComponent();

        if (component === 'layers') {
            return <LayersPanel onRefreshLayers={refreshLayers} onSelectLayer={selectLayer} />;
        }

        if (component === 'viewport') {
            return (
                <ViewportPanel
                    iframeRef={iframeRef}
                    onFrameLoad={handleFrameLoad}
                    onRefreshLayers={refreshLayers}
                    onReloadViewport={reloadViewport}
                />
            );
        }

        if (component === 'inspector') {
            return <InspectorPanel iframeRef={iframeRef} frameRevision={frameRevision} />;
        }

        if (component === 'history') {
            return <HistoryPanel onRestoreRevision={restoreRevision} />;
        }

        return null;
    }, [frameRevision, handleFrameLoad, refreshLayers, reloadViewport, restoreRevision, selectLayer]);

    return (
        <div className="ne-shell">
            <Topbar onRefreshLayers={refreshLayers} />

            <div className="ne-shell__workspace" ref={workspaceRef}>
                <Layout
                    model={modelRef.current}
                    factory={factory}
                    onModelChange={(model) => saveLayoutModel(model)}
                    realtimeResize
                />
            </div>
        </div>
    );
}