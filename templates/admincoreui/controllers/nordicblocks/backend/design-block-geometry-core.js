(function (root, factory) {
    var api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }

    root.NordicblocksDesignBlockGeometryCore = api;
}(typeof globalThis !== 'undefined' ? globalThis : this, function () {
    function toNumber(value, fallback) {
        var number = Number(value);

        return Number.isFinite(number) ? number : Number(fallback || 0);
    }

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
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

    function normalizeBounds(bounds) {
        if (!bounds) {
            return null;
        }

        return {
            width: Math.max(1, toNumber(bounds.width, 1)),
            height: Math.max(1, toNumber(bounds.height, 1)),
            minX: toNumber(bounds.minX, 0),
            minY: toNumber(bounds.minY, 0)
        };
    }

    function normalizeViewport(viewport) {
        viewport = viewport || {};

        return {
            zoom: Math.max(0.01, toNumber(viewport.zoom, 1)),
            offsetX: toNumber(viewport.offsetX, 0),
            offsetY: toNumber(viewport.offsetY, 0),
            width: Math.max(0, toNumber(viewport.width, 0)),
            height: Math.max(0, toNumber(viewport.height, 0))
        };
    }

    function projectWorldPoint(viewport, worldPoint) {
        viewport = normalizeViewport(viewport);
        worldPoint = normalizePoint(worldPoint);

        return {
            x: (worldPoint.x - viewport.offsetX) * viewport.zoom,
            y: (worldPoint.y - viewport.offsetY) * viewport.zoom
        };
    }

    function screenToWorldPoint(viewport, screenPoint) {
        viewport = normalizeViewport(viewport);
        screenPoint = normalizePoint(screenPoint);

        return {
            x: (screenPoint.x / viewport.zoom) + viewport.offsetX,
            y: (screenPoint.y / viewport.zoom) + viewport.offsetY
        };
    }

    function projectWorldRect(viewport, rect) {
        var box = normalizeBox(rect);
        var topLeft = projectWorldPoint(viewport, { x: box.x, y: box.y });
        var bottomRight = projectWorldPoint(viewport, { x: box.x + box.w, y: box.y + box.h });

        return {
            x: topLeft.x,
            y: topLeft.y,
            w: Math.max(1, bottomRight.x - topLeft.x),
            h: Math.max(1, bottomRight.y - topLeft.y)
        };
    }

    function worldDeltaFromScreenDelta(viewport, screenDelta) {
        viewport = normalizeViewport(viewport);
        screenDelta = normalizePoint(screenDelta);

        return {
            x: screenDelta.x / viewport.zoom,
            y: screenDelta.y / viewport.zoom
        };
    }

    function zoomViewportAtScreenPoint(viewport, screenPoint, nextZoom, options) {
        var normalized = normalizeViewport(viewport);
        var settings = options || {};
        var minZoom = settings.minZoom == null ? 0.25 : Number(settings.minZoom);
        var maxZoom = settings.maxZoom == null ? 2 : Number(settings.maxZoom);
        var focusPoint = screenPoint ? normalizePoint(screenPoint) : {
            x: normalized.width / 2,
            y: normalized.height / 2
        };
        var safeZoom = clamp(toNumber(nextZoom, normalized.zoom), minZoom, maxZoom);
        var anchorWorld = screenToWorldPoint(normalized, focusPoint);

        return {
            zoom: safeZoom,
            width: normalized.width,
            height: normalized.height,
            offsetX: anchorWorld.x - (focusPoint.x / safeZoom),
            offsetY: anchorWorld.y - (focusPoint.y / safeZoom)
        };
    }

    function createDragSession(box, pointerWorld) {
        return {
            startBox: normalizeBox(box),
            startPointerWorld: normalizePoint(pointerWorld)
        };
    }

    function clampBoxPosition(box, bounds) {
        var normalizedBounds = normalizeBounds(bounds);

        if (!normalizedBounds) {
            return box;
        }

        return {
            x: clamp(box.x, normalizedBounds.minX, Math.max(normalizedBounds.minX, normalizedBounds.width - box.w)),
            y: clamp(box.y, normalizedBounds.minY, Math.max(normalizedBounds.minY, normalizedBounds.height - box.h)),
            w: box.w,
            h: box.h
        };
    }

    function applyDragSession(session, pointerWorld, options) {
        var normalizedSession = createDragSession(session && session.startBox, session && session.startPointerWorld);
        var currentPointer = normalizePoint(pointerWorld);
        var nextBox = {
            x: normalizedSession.startBox.x + (currentPointer.x - normalizedSession.startPointerWorld.x),
            y: normalizedSession.startBox.y + (currentPointer.y - normalizedSession.startPointerWorld.y),
            w: normalizedSession.startBox.w,
            h: normalizedSession.startBox.h
        };

        return clampBoxPosition(nextBox, options && options.bounds);
    }

    function createResizeSession(box, handle, pointerWorld) {
        return {
            startBox: normalizeBox(box),
            handle: String(handle || ''),
            startPointerWorld: normalizePoint(pointerWorld)
        };
    }

    function applyResizeSession(session, pointerWorld, options) {
        var normalizedSession = createResizeSession(session && session.startBox, session && session.handle, session && session.startPointerWorld);
        var currentPointer = normalizePoint(pointerWorld);
        var settings = options || {};
        var bounds = normalizeBounds(settings.bounds);
        var minWidth = Math.max(1, toNumber(settings.minWidth, 1));
        var minHeight = Math.max(1, toNumber(settings.minHeight, 1));
        var keepAspectRatio = !!settings.keepAspectRatio;
        var startLeft = normalizedSession.startBox.x;
        var startTop = normalizedSession.startBox.y;
        var startRight = normalizedSession.startBox.x + normalizedSession.startBox.w;
        var startBottom = normalizedSession.startBox.y + normalizedSession.startBox.h;
        var dx = currentPointer.x - normalizedSession.startPointerWorld.x;
        var dy = currentPointer.y - normalizedSession.startPointerWorld.y;
        var nextLeft = startLeft;
        var nextTop = startTop;
        var nextRight = startRight;
        var nextBottom = startBottom;
        var ratio;
        var widthByDx;
        var widthByDy;
        var nextWidth;
        var nextHeight;
        var handle = normalizedSession.handle;

        if (keepAspectRatio && handle.length === 2) {
            ratio = normalizedSession.startBox.w / Math.max(1, normalizedSession.startBox.h);
            widthByDx = normalizedSession.startBox.w + ((handle.indexOf('w') >= 0 ? -1 : 1) * dx);
            widthByDy = (normalizedSession.startBox.h + ((handle.indexOf('n') >= 0 ? -1 : 1) * dy)) * ratio;
            nextWidth = Math.max(minWidth, Math.abs(widthByDx) >= Math.abs(widthByDy) ? widthByDx : widthByDy);
            nextHeight = Math.max(minHeight, nextWidth / ratio);

            if (handle.indexOf('w') >= 0) {
                nextLeft = startRight - nextWidth;
            } else {
                nextRight = startLeft + nextWidth;
            }

            if (handle.indexOf('n') >= 0) {
                nextTop = startBottom - nextHeight;
            } else {
                nextBottom = startTop + nextHeight;
            }
        } else {
            if (handle.indexOf('w') >= 0) {
                nextLeft += dx;
            }
            if (handle.indexOf('e') >= 0) {
                nextRight += dx;
            }
            if (handle.indexOf('n') >= 0) {
                nextTop += dy;
            }
            if (handle.indexOf('s') >= 0) {
                nextBottom += dy;
            }
        }

        if (bounds) {
            nextLeft = clamp(nextLeft, bounds.minX, Math.max(bounds.minX, bounds.width - minWidth));
            nextTop = clamp(nextTop, bounds.minY, Math.max(bounds.minY, bounds.height - minHeight));
            nextRight = clamp(nextRight, nextLeft + minWidth, bounds.width);
            nextBottom = clamp(nextBottom, nextTop + minHeight, bounds.height);
        }

        return {
            x: Math.round(nextLeft),
            y: Math.round(nextTop),
            w: Math.max(minWidth, Math.round(nextRight - nextLeft)),
            h: Math.max(minHeight, Math.round(nextBottom - nextTop))
        };
    }

    function almostEqual(left, right, epsilon) {
        return Math.abs(toNumber(left, 0) - toNumber(right, 0)) <= toNumber(epsilon, 0.000001);
    }

    function pointsAlmostEqual(left, right, epsilon) {
        return almostEqual(left && left.x, right && right.x, epsilon) && almostEqual(left && left.y, right && right.y, epsilon);
    }

    function isPointerWorldPointStable(viewport, screenPoint, nextZoom, epsilon) {
        var before = screenToWorldPoint(viewport, screenPoint);
        var afterViewport = zoomViewportAtScreenPoint(viewport, screenPoint, nextZoom, {
            minZoom: 0.01,
            maxZoom: 100
        });
        var after = screenToWorldPoint(afterViewport, screenPoint);

        return pointsAlmostEqual(before, after, epsilon);
    }

    function isWorldPointPinnedToScreen(viewport, worldPoint, screenPoint, nextZoom, epsilon) {
        var afterViewport = zoomViewportAtScreenPoint(viewport, screenPoint, nextZoom, {
            minZoom: 0.01,
            maxZoom: 100
        });
        var projected = projectWorldPoint(afterViewport, worldPoint);

        return pointsAlmostEqual(projected, screenPoint, epsilon);
    }

    return {
        clamp: clamp,
        normalizeViewport: normalizeViewport,
        projectWorldPoint: projectWorldPoint,
        screenToWorldPoint: screenToWorldPoint,
        projectWorldRect: projectWorldRect,
        worldDeltaFromScreenDelta: worldDeltaFromScreenDelta,
        zoomViewportAtScreenPoint: zoomViewportAtScreenPoint,
        createDragSession: createDragSession,
        applyDragSession: applyDragSession,
        createResizeSession: createResizeSession,
        applyResizeSession: applyResizeSession,
        almostEqual: almostEqual,
        pointsAlmostEqual: pointsAlmostEqual,
        isPointerWorldPointStable: isPointerWorldPointStable,
        isWorldPointPinnedToScreen: isWorldPointPinnedToScreen
    };
}));