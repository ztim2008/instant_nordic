import { workspaceStorageKey } from './constants.js';

export function defaultLayoutModel() {
    return {
        global: {
            tabEnableClose: false,
            tabSetEnableClose: false,
            tabSetEnableMaximize: false,
            tabSetEnableTabStrip: true,
            tabSetHeaderHeight: 34,
            tabSetTabStripHeight: 36,
            splitterSize: 8,
            enableEdgeDock: false,
            rootOrientationVertical: false
        },
        layout: {
            type: 'row',
            weight: 100,
            children: [
                {
                    type: 'tabset',
                    id: 'editor-layers',
                    weight: 20,
                    children: [
                        { type: 'tab', id: 'layers-tab', name: 'Слои', component: 'layers', enableClose: false }
                    ]
                },
                {
                    type: 'tabset',
                    id: 'editor-viewport',
                    weight: 54,
                    children: [
                        { type: 'tab', id: 'viewport-tab', name: 'Холст', component: 'viewport', enableClose: false }
                    ]
                },
                {
                    type: 'row',
                    id: 'editor-right-column',
                    weight: 26,
                    children: [
                        {
                            type: 'tabset',
                            id: 'editor-inspector',
                            weight: 62,
                            children: [
                                { type: 'tab', id: 'inspector-tab', name: 'Инспектор', component: 'inspector', enableClose: false }
                            ]
                        },
                        {
                            type: 'tabset',
                            id: 'editor-history',
                            weight: 38,
                            children: [
                                { type: 'tab', id: 'history-tab', name: 'История', component: 'history', enableClose: false }
                            ]
                        }
                    ]
                }
            ]
        }
    };
}

export function loadLayoutModel() {
    try {
        const raw = window.localStorage.getItem(workspaceStorageKey);

        if (!raw) {
            return defaultLayoutModel();
        }

        return JSON.parse(raw);
    } catch (error) {
        return defaultLayoutModel();
    }
}

export function saveLayoutModel(model) {
    try {
        window.localStorage.setItem(workspaceStorageKey, JSON.stringify(model.toJson()));
    } catch (error) {
        return;
    }
}