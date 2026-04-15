import { useDeferredValue, useEffect, useMemo, useState } from 'react';
import { filterLayerNodes } from '../lib/dom.js';
import { useEditorStore } from '../store/editorStore.js';

function collectDefaultExpanded(nodes, depth = 0, accumulator = {}) {
    nodes.forEach((node) => {
        if (node.children?.length && depth < 2) {
            accumulator[node.id] = true;
        }

        if (node.children?.length) {
            collectDefaultExpanded(node.children, depth + 1, accumulator);
        }
    });

    return accumulator;
}

function collectAncestorIds(nodes, targetId, path = []) {
    for (const node of nodes) {
        if (node.id === targetId) {
            return path;
        }

        if (node.children?.length) {
            const result = collectAncestorIds(node.children, targetId, [...path, node.id]);

            if (result.length) {
                return result;
            }
        }
    }

    return [];
}

function LayerItem({ node, selectedNodeId, onSelect, onToggle, expandedMap, forceExpand = false, depth = 0 }) {
    const isSelected = node.id === selectedNodeId;
    const hasChildren = Boolean(node.children?.length);
    const isExpanded = forceExpand || !hasChildren || expandedMap[node.id] !== false;

    return (
        <li className="ne-layers__item">
            <div className="ne-layers__row" style={{ '--layer-depth': depth }}>
                {hasChildren ? (
                    <button
                        className="ne-layers__toggle"
                        type="button"
                        onClick={() => onToggle(node.id)}
                        aria-label={isExpanded ? 'Свернуть ветку' : 'Развернуть ветку'}
                    >
                        {isExpanded ? '-' : '+'}
                    </button>
                ) : (
                    <span className="ne-layers__toggle ne-layers__toggle--placeholder" aria-hidden="true" />
                )}

                <button
                    className={`ne-layers__button${isSelected ? ' is-selected' : ''}`}
                    type="button"
                    onClick={() => onSelect(node.id)}
                >
                    <span className="ne-layers__title">{node.label || node.id}</span>
                    <span className="ne-layers__meta">{node.role || node.tag || node.id}</span>
                </button>
            </div>

            {hasChildren && isExpanded ? (
                <ul className="ne-layers__branch">
                    {node.children.map((child) => (
                        <LayerItem
                            key={child.id}
                            node={child}
                            selectedNodeId={selectedNodeId}
                            onSelect={onSelect}
                            onToggle={onToggle}
                            expandedMap={expandedMap}
                            forceExpand={forceExpand}
                            depth={depth + 1}
                        />
                    ))}
                </ul>
            ) : null}
        </li>
    );
}

export function LayersPanel({ onRefreshLayers, onSelectLayer }) {
    const layerTree = useEditorStore((state) => state.layerTree);
    const layerMap = useEditorStore((state) => state.layerMap);
    const selectedNodeId = useEditorStore((state) => state.selected.nodeId);
    const [query, setQuery] = useState('');
    const [expandedMap, setExpandedMap] = useState({});
    const deferredQuery = useDeferredValue(query.trim().toLowerCase());
    const filteredTree = useMemo(() => filterLayerNodes(layerTree, deferredQuery), [layerTree, deferredQuery]);
    const forceExpand = Boolean(deferredQuery);
    const nodeCount = Object.keys(layerMap || {}).length;

    useEffect(() => {
        setExpandedMap((current) => ({
            ...collectDefaultExpanded(layerTree),
            ...current
        }));
    }, [layerTree]);

    useEffect(() => {
        if (!selectedNodeId) {
            return;
        }

        const ancestorIds = collectAncestorIds(layerTree, selectedNodeId);

        if (!ancestorIds.length) {
            return;
        }

        setExpandedMap((current) => {
            const next = { ...current };
            ancestorIds.forEach((id) => {
                next[id] = true;
            });
            return next;
        });
    }, [layerTree, selectedNodeId]);

    function toggleNode(nodeId) {
        setExpandedMap((current) => ({
            ...current,
            [nodeId]: current[nodeId] === false ? true : false
        }));
    }

    return (
        <div className="ne-panel">
            <div className="ne-panel__header">
                <div>
                    <h2 className="ne-panel__title">Слои</h2>
                    <div className="ne-panel__subtitle">Компактное дерево узлов из iframe по data-nordic-id.</div>
                </div>

                <button className="ne-icon-button" type="button" onClick={onRefreshLayers}>↻</button>
            </div>

            <div className="ne-layers__summary">{nodeCount} узлов в DOM страницы</div>

            <label className="ne-field">
                <span className="ne-field__label">Поиск</span>
                <input
                    className="ne-input"
                    type="text"
                    value={query}
                    onChange={(event) => setQuery(event.target.value)}
                    placeholder="роль, id, подпись"
                />
            </label>

            {filteredTree.length ? (
                <ul className="ne-layers">
                    {filteredTree.map((node) => (
                        <LayerItem
                            key={node.id}
                            node={node}
                            selectedNodeId={selectedNodeId}
                            onSelect={onSelectLayer}
                            onToggle={toggleNode}
                            expandedMap={expandedMap}
                            forceExpand={forceExpand}
                        />
                    ))}
                </ul>
            ) : (
                <div className="ne-empty">После загрузки iframe здесь появится дерево живых узлов.</div>
            )}
        </div>
    );
}