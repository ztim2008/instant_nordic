(function (root, factory) {
    var api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }

    root.NordicblocksDesignBlockInteractionCore = api;
}(typeof globalThis !== 'undefined' ? globalThis : this, function () {
    function toNumber(value, fallback) {
        var number = Number(value);

        return Number.isFinite(number) ? number : Number(fallback || 0);
    }

    function normalizePoint(point) {
        point = point || {};

        return {
            x: toNumber(point.x, 0),
            y: toNumber(point.y, 0)
        };
    }

    function normalizeBox(box) {
        box = box || {};

        return {
            x: toNumber(box.x, 0),
            y: toNumber(box.y, 0),
            w: Math.max(1, toNumber(box.w != null ? box.w : box.width, 1)),
            h: Math.max(1, toNumber(box.h != null ? box.h : box.height, 1))
        };
    }

    function normalizeNode(node) {
        node = node || {};

        return {
            id: String(node.id || ''),
            parentId: String(node.parentId || ''),
            hidden: !!node.hidden,
            visible: node.visible !== false,
            zIndex: toNumber(node.zIndex, 1),
            box: normalizeBox(node.box)
        };
    }

    function normalizeNodes(nodes) {
        return (nodes || []).map(normalizeNode).filter(function (node) {
            return !!node.id;
        });
    }

    function uniqueSortedNumbers(values) {
        var seen = {};

        return (values || []).filter(function (value) {
            var rounded = Math.round(toNumber(value, 0) * 1000) / 1000;
            var key = String(rounded);

            if (seen[key]) {
                return false;
            }

            seen[key] = true;
            return true;
        }).sort(function (left, right) {
            return toNumber(left, 0) - toNumber(right, 0);
        });
    }

    function indexNodes(nodes) {
        var indexed = {};

        normalizeNodes(nodes).forEach(function (node) {
            indexed[node.id] = node;
        });

        return indexed;
    }

    function normalizeSelectionIds(nodes, selectionIds) {
        var known = indexNodes(nodes);

        return (selectionIds || []).map(String).filter(function (id, index, source) {
            return !!known[id] && source.indexOf(id) === index;
        });
    }

    function createSelectionState(nodes, selectionIds, primaryId) {
        var normalized = normalizeSelectionIds(nodes, selectionIds);
        var selectedPrimaryId = primaryId != null && normalized.indexOf(String(primaryId)) >= 0
            ? String(primaryId)
            : (normalized.length ? normalized[normalized.length - 1] : null);

        return {
            selectionIds: normalized,
            primaryId: selectedPrimaryId
        };
    }

    function toggleSelectionState(nodes, currentSelectionIds, toggledId) {
        var selectionIds = normalizeSelectionIds(nodes, currentSelectionIds).slice();
        var normalizedId = String(toggledId || '');
        var index = selectionIds.indexOf(normalizedId);

        if (index >= 0) {
            selectionIds.splice(index, 1);
            return createSelectionState(nodes, selectionIds, selectionIds.length ? selectionIds[selectionIds.length - 1] : null);
        }

        selectionIds.push(normalizedId);
        return createSelectionState(nodes, selectionIds, normalizedId);
    }

    function resolvePointerSelection(nodes, currentSelectionIds, hitId, options) {
        var settings = options || {};

        if (!hitId) {
            return settings.clearOnMiss === false
                ? createSelectionState(nodes, currentSelectionIds, settings.primaryId)
                : createSelectionState(nodes, [], null);
        }

        if (settings.multiSelect) {
            return toggleSelectionState(nodes, currentSelectionIds, hitId);
        }

        return createSelectionState(nodes, [String(hitId)], String(hitId));
    }

    function getRootSelectionIds(nodes, selectionIds) {
        var indexed = indexNodes(nodes);
        var normalized = normalizeSelectionIds(nodes, selectionIds);

        return normalized.filter(function (id) {
            var parentId = indexed[id] ? indexed[id].parentId : '';

            while (parentId) {
                if (normalized.indexOf(parentId) >= 0) {
                    return false;
                }

                parentId = indexed[parentId] ? indexed[parentId].parentId : '';
            }

            return true;
        });
    }

    function buildSelectionBounds(candidates) {
        var bounds = null;

        normalizeNodes(candidates).forEach(function (candidate) {
            var box = candidate.box;

            if (candidate.hidden || candidate.visible === false) {
                return;
            }

            if (!bounds) {
                bounds = {
                    x: box.x,
                    y: box.y,
                    right: box.x + box.w,
                    bottom: box.y + box.h
                };
                return;
            }

            bounds.x = Math.min(bounds.x, box.x);
            bounds.y = Math.min(bounds.y, box.y);
            bounds.right = Math.max(bounds.right, box.x + box.w);
            bounds.bottom = Math.max(bounds.bottom, box.y + box.h);
        });

        if (!bounds) {
            return null;
        }

        bounds.w = Math.max(1, bounds.right - bounds.x);
        bounds.h = Math.max(1, bounds.bottom - bounds.y);
        return bounds;
    }

    function hitTestWorldPoint(candidates, worldPoint) {
        var point = normalizePoint(worldPoint);
        var hitId = null;

        normalizeNodes(candidates).sort(function (left, right) {
            return right.zIndex - left.zIndex;
        }).forEach(function (candidate) {
            var box = candidate.box;

            if (hitId || candidate.hidden || candidate.visible === false) {
                return;
            }

            if (point.x >= box.x && point.x <= box.x + box.w && point.y >= box.y && point.y <= box.y + box.h) {
                hitId = candidate.id;
            }
        });

        return hitId;
    }

    function buildSiblingAlignmentCandidates(nodes, targetId, axis, options) {
        var indexed = indexNodes(nodes);
        var target = indexed[String(targetId || '')] || null;
        var settings = options || {};
        var excluded = {};
        var result = [];
        var normalizedAxis = String(axis || 'x') === 'y' ? 'y' : 'x';
        var parentId;

        if (!target) {
            return [];
        }

        (settings.excludeIds || []).forEach(function (id) {
            excluded[String(id)] = true;
        });

        excluded[target.id] = true;
        parentId = settings.parentId != null ? String(settings.parentId) : target.parentId;

        normalizeNodes(nodes).forEach(function (node) {
            var start;
            var size;

            if (excluded[node.id] || node.hidden || node.visible === false || node.parentId !== parentId) {
                return;
            }

            if (normalizedAxis === 'x') {
                start = node.box.x;
                size = node.box.w;
            } else {
                start = node.box.y;
                size = node.box.h;
            }

            result.push(start);
            result.push(start + size);

            if (settings.includeCenters !== false) {
                result.push(start + (size / 2));
            }
        });

        return uniqueSortedNumbers(result);
    }

    function resolveAxisAlignment(position, size, candidates, threshold, options) {
        var settings = options || {};
        var probes = [
            { anchor: 'start', value: toNumber(position, 0), offset: 0 },
            { anchor: 'end', value: toNumber(position, 0) + Math.max(1, toNumber(size, 1)), offset: Math.max(1, toNumber(size, 1)) }
        ];
        var best = null;

        if (settings.includeCenter !== false) {
            probes.push({
                anchor: 'center',
                value: toNumber(position, 0) + (Math.max(1, toNumber(size, 1)) / 2),
                offset: Math.max(1, toNumber(size, 1)) / 2
            });
        }

        uniqueSortedNumbers(candidates).forEach(function (candidate) {
            probes.forEach(function (probe) {
                var distance = Math.abs(toNumber(candidate, 0) - probe.value);

                if (distance > toNumber(threshold, 0)) {
                    return;
                }

                if (!best || distance < best.distance) {
                    best = {
                        anchor: probe.anchor,
                        distance: distance,
                        candidate: toNumber(candidate, 0),
                        offset: probe.offset
                    };
                }
            });
        });

        if (!best) {
            return {
                value: toNumber(position, 0),
                guide: null,
                anchor: null,
                distance: null
            };
        }

        return {
            value: best.candidate - best.offset,
            guide: best.candidate,
            anchor: best.anchor,
            distance: best.distance
        };
    }

    function getResizeHandles(type, options) {
        var normalizedType = String(type || 'shape');
        var orientation = String((options && options.orientation) || 'horizontal');

        if (normalizedType === 'text') {
            return ['sw', 'se'];
        }

        if (normalizedType === 'divider') {
            return orientation === 'vertical' ? ['n', 's'] : ['w', 'e'];
        }

        if (normalizedType === 'image' || normalizedType === 'svg' || normalizedType === 'video') {
            return ['nw', 'ne', 'se', 'sw'];
        }

        return ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'];
    }

    function createPointerCaptureSession(pointerId, mode, meta) {
        if (pointerId == null || pointerId === '') {
            return null;
        }

        return {
            pointerId: String(pointerId),
            mode: String(mode || ''),
            meta: meta || null
        };
    }

    function isPointerCaptureMatch(session, pointerId, mode) {
        if (!session || String(session.pointerId) !== String(pointerId)) {
            return false;
        }

        return mode ? String(session.mode) === String(mode) : true;
    }

    function releasePointerCaptureSession(session, pointerId) {
        return isPointerCaptureMatch(session, pointerId) ? null : session;
    }

    return {
        normalizeSelectionIds: normalizeSelectionIds,
        createSelectionState: createSelectionState,
        toggleSelectionState: toggleSelectionState,
        resolvePointerSelection: resolvePointerSelection,
        getRootSelectionIds: getRootSelectionIds,
        buildSelectionBounds: buildSelectionBounds,
        hitTestWorldPoint: hitTestWorldPoint,
        buildSiblingAlignmentCandidates: buildSiblingAlignmentCandidates,
        resolveAxisAlignment: resolveAxisAlignment,
        getResizeHandles: getResizeHandles,
        createPointerCaptureSession: createPointerCaptureSession,
        isPointerCaptureMatch: isPointerCaptureMatch,
        releasePointerCaptureSession: releasePointerCaptureSession
    };
}));