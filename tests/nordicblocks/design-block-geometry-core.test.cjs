const test = require('node:test');
const assert = require('node:assert/strict');

const geometry = require('../../templates/admincoreui/controllers/nordicblocks/backend/design-block-geometry-core.js');

function point(x, y) {
    return { x, y };
}

function box(x, y, w, h) {
    return { x, y, w, h };
}

function dragAtZoom(zoom, screenDx, screenDy) {
    const viewport = { zoom, offsetX: 0, offsetY: 0, width: 1200, height: 640 };
    const startBox = box(100, 80, 200, 120);
    const pointerWorld = point(140, 110);
    const startScreen = geometry.projectWorldPoint(viewport, pointerWorld);
    const currentWorld = geometry.screenToWorldPoint(viewport, point(startScreen.x + screenDx, startScreen.y + screenDy));

    return geometry.applyDragSession(
        geometry.createDragSession(startBox, pointerWorld),
        currentWorld,
        { bounds: { width: 1200, height: 640 } }
    );
}

test('world -> screen projects through viewport', function () {
    const viewport = { zoom: 2, offsetX: 100, offsetY: 50, width: 1200, height: 640 };
    const projected = geometry.projectWorldPoint(viewport, point(150, 80));

    assert.deepEqual(projected, point(100, 60));
});

test('screen -> world restores original world point', function () {
    const viewport = { zoom: 1.5, offsetX: 200, offsetY: 80, width: 1200, height: 640 };
    const world = point(360, 180);
    const screen = geometry.projectWorldPoint(viewport, world);
    const restored = geometry.screenToWorldPoint(viewport, screen);

    assert.ok(geometry.pointsAlmostEqual(restored, world));
});

test('drag at zoom = 1 uses 1:1 world delta', function () {
    const moved = dragAtZoom(1, 50, 30);

    assert.equal(moved.x, 150);
    assert.equal(moved.y, 110);
});

test('drag at zoom = 0.5 expands screen delta into world delta', function () {
    const moved = dragAtZoom(0.5, 50, 30);

    assert.equal(moved.x, 200);
    assert.equal(moved.y, 140);
});

test('drag at zoom = 2 compresses screen delta into world delta', function () {
    const moved = dragAtZoom(2, 50, 30);

    assert.equal(moved.x, 125);
    assert.equal(moved.y, 95);
});

test('resize at zoom != 1 depends on world delta, not raw screen pixels', function () {
    const viewport = { zoom: 1.5, offsetX: 0, offsetY: 0, width: 1200, height: 640 };
    const startBox = box(200, 100, 300, 120);
    const eastHandleWorld = point(startBox.x + startBox.w, startBox.y + (startBox.h / 2));
    const eastHandleScreen = geometry.projectWorldPoint(viewport, eastHandleWorld);
    const currentWorld = geometry.screenToWorldPoint(viewport, point(eastHandleScreen.x + 75, eastHandleScreen.y));
    const resized = geometry.applyResizeSession(
        geometry.createResizeSession(startBox, 'e', eastHandleWorld),
        currentWorld,
        {
            bounds: { width: 1200, height: 640 },
            minWidth: 120,
            minHeight: 40
        }
    );

    assert.equal(resized.x, 200);
    assert.equal(resized.w, 350);
    assert.equal(resized.h, 120);
});

test('pointer stays on the same world point after zoom change', function () {
    const viewport = { zoom: 1, offsetX: 100, offsetY: 40, width: 1200, height: 640 };
    const screenPoint = point(320, 180);

    assert.equal(geometry.isPointerWorldPointStable(viewport, screenPoint, 2), true);
    assert.equal(geometry.isPointerWorldPointStable(viewport, screenPoint, 0.5), true);
});

test('element center does not drift when zoom is anchored to its screen point', function () {
    const viewport = { zoom: 1, offsetX: 80, offsetY: 20, width: 1200, height: 640 };
    const elementCenter = point(460, 210);
    const anchorScreen = geometry.projectWorldPoint(viewport, elementCenter);

    assert.equal(geometry.isWorldPointPinnedToScreen(viewport, elementCenter, anchorScreen, 2), true);
    assert.equal(geometry.isWorldPointPinnedToScreen(viewport, elementCenter, anchorScreen, 0.5), true);
});

test('dragged world coordinates do not jump after viewport zoom changes', function () {
    const viewport = { zoom: 2, offsetX: 0, offsetY: 0, width: 1200, height: 640 };
    const dragged = dragAtZoom(2, 80, 0);
    const zoomedViewport = geometry.zoomViewportAtScreenPoint(viewport, point(400, 220), 0.5, {
        minZoom: 0.25,
        maxZoom: 4
    });
    const topLeftScreen = geometry.projectWorldPoint(zoomedViewport, point(dragged.x, dragged.y));
    const topLeftWorld = geometry.screenToWorldPoint(zoomedViewport, topLeftScreen);

    assert.equal(dragged.x, 140);
    assert.equal(dragged.y, 80);
    assert.ok(geometry.pointsAlmostEqual(topLeftWorld, point(140, 80)));
});