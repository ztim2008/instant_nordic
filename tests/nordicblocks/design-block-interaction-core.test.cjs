const test = require('node:test');
const assert = require('node:assert/strict');

const interaction = require('../../templates/admincoreui/controllers/nordicblocks/backend/design-block-interaction-core.js');

function node(id, parentId, box, extra) {
    return Object.assign({
        id,
        parentId: parentId || '',
        box,
        zIndex: 1,
        visible: true,
        hidden: false
    }, extra || {});
}

test('selection state removes duplicates and unknown ids', function () {
    const nodes = [
        node('root', '', { x: 0, y: 0, w: 400, h: 300 }),
        node('child', 'root', { x: 40, y: 40, w: 80, h: 80 })
    ];
    const selection = interaction.createSelectionState(nodes, ['child', 'child', 'ghost', 'root'], 'ghost');

    assert.deepEqual(selection.selectionIds, ['child', 'root']);
    assert.equal(selection.primaryId, 'root');
});

test('pointer selection clears on miss and toggles on shift-like multi select', function () {
    const nodes = [
        node('a', '', { x: 0, y: 0, w: 100, h: 100 }),
        node('b', '', { x: 120, y: 0, w: 100, h: 100 })
    ];
    const first = interaction.resolvePointerSelection(nodes, [], 'a', { multiSelect: false, clearOnMiss: true });
    const second = interaction.resolvePointerSelection(nodes, first.selectionIds, 'b', { multiSelect: true, clearOnMiss: true });
    const cleared = interaction.resolvePointerSelection(nodes, second.selectionIds, '', { multiSelect: false, clearOnMiss: true });

    assert.deepEqual(first.selectionIds, ['a']);
    assert.deepEqual(second.selectionIds, ['a', 'b']);
    assert.deepEqual(cleared.selectionIds, []);
});

test('root selection strips descendants when parent is selected', function () {
    const nodes = [
        node('group', '', { x: 0, y: 0, w: 300, h: 200 }),
        node('title', 'group', { x: 20, y: 20, w: 140, h: 40 }),
        node('image', 'group', { x: 20, y: 80, w: 140, h: 80 })
    ];

    assert.deepEqual(interaction.getRootSelectionIds(nodes, ['group', 'title', 'image']), ['group']);
});

test('selection bounds merge multiple world boxes', function () {
    const bounds = interaction.buildSelectionBounds([
        node('left', '', { x: 40, y: 60, w: 80, h: 100 }),
        node('right', '', { x: 180, y: 20, w: 120, h: 60 })
    ]);

    assert.deepEqual(bounds, {
        x: 40,
        y: 20,
        right: 300,
        bottom: 160,
        w: 260,
        h: 140
    });
});

test('hit test returns topmost visible world node', function () {
    const nodes = [
        node('back', '', { x: 0, y: 0, w: 220, h: 220 }, { zIndex: 1 }),
        node('front', '', { x: 40, y: 40, w: 120, h: 120 }, { zIndex: 5 }),
        node('hidden', '', { x: 60, y: 60, w: 80, h: 80 }, { zIndex: 9, hidden: true })
    ];

    assert.equal(interaction.hitTestWorldPoint(nodes, { x: 70, y: 70 }), 'front');
    assert.equal(interaction.hitTestWorldPoint(nodes, { x: 10, y: 10 }), 'back');
});

test('resize handles follow node type rules', function () {
    assert.deepEqual(interaction.getResizeHandles('text'), ['w', 'e']);
    assert.deepEqual(interaction.getResizeHandles('image'), ['nw', 'ne', 'se', 'sw']);
    assert.deepEqual(interaction.getResizeHandles('divider', { orientation: 'vertical' }), ['n', 's']);
});

test('pointer capture session matches active pointer and releases cleanly', function () {
    const session = interaction.createPointerCaptureSession(17, 'drag', { elementId: 'hero_1' });

    assert.equal(interaction.isPointerCaptureMatch(session, 17), true);
    assert.equal(interaction.isPointerCaptureMatch(session, 18), false);
    assert.equal(interaction.isPointerCaptureMatch(session, 17, 'drag'), true);
    assert.equal(interaction.releasePointerCaptureSession(session, 17), null);
});