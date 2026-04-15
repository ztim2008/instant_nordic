import { create } from 'zustand';
import { builderState } from '../lib/constants.js';

export const useEditorStore = create((set) => ({
    frameState: 'loading',
    frameMessage: builderState.status_message || 'Подготавливаю живой экран.',
    frameTone: 'muted',
    layerTree: [],
    layerMap: {},
    sessionEvents: [],
    selected: {
        nodeId: '',
        storagePath: '',
        title: 'Еще не выбран',
        role: '',
        tag: '',
        source: 'Ждем выбор узла',
        mode: 'Компьютер · Обычный',
        text: ''
    },
    restoreBusyId: 0,
    setFrameState: (frameState, frameMessage, frameTone = 'muted') => set({ frameState, frameMessage, frameTone }),
    pushSessionEvent: (label, detail = '') => set((state) => ({
        sessionEvents: [
            {
                id: Date.now() + Math.random(),
                label: String(label || '').trim(),
                detail: String(detail || '').trim(),
                createdAt: new Date().toISOString()
            }
        ].concat(state.sessionEvents).slice(0, 24)
    })),
    setLayerTree: (layerTree, layerMap) => set((state) => ({
        layerTree,
        layerMap,
        selected: state.selected.nodeId && layerMap[state.selected.nodeId]
            ? {
                ...state.selected,
                role: layerMap[state.selected.nodeId].role || state.selected.role,
                tag: layerMap[state.selected.nodeId].tag || state.selected.tag,
                text: layerMap[state.selected.nodeId].text || state.selected.text,
                title: state.selected.title === 'Еще не выбран'
                    ? (layerMap[state.selected.nodeId].label || state.selected.title)
                    : state.selected.title
            }
            : state.selected
    })),
    setSelected: (payload) => set((state) => {
        const nodeId = String(payload.nodeId || state.selected.nodeId || '').trim();
        const layerNode = nodeId ? state.layerMap[nodeId] : null;

        return {
            selected: {
                nodeId,
                storagePath: String(payload.storagePath || (nodeId ? `node:${nodeId}` : '')).trim(),
                title: String(payload.title || layerNode?.label || 'Еще не выбран').trim() || 'Еще не выбран',
                role: String(payload.role || layerNode?.role || '').trim(),
                tag: String(payload.tag || layerNode?.tag || '').trim(),
                source: String(payload.source || state.selected.source || 'Ждем выбор узла').trim(),
                mode: String(payload.mode || state.selected.mode || 'Компьютер · Обычный').trim(),
                text: String(payload.text || layerNode?.text || '').trim()
            }
        };
    }),
    setRestoreBusyId: (restoreBusyId) => set({ restoreBusyId })
}));