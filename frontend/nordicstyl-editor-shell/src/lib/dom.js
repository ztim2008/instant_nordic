function normalizeText(value, limit = 120) {
    return String(value || '').replace(/\s+/g, ' ').trim().slice(0, limit);
}

export function escapeAttributeValue(value) {
    return String(value || '').replace(/\\/g, '\\\\').replace(/"/g, '\\"');
}

export function buildLayerTree(doc) {
    const elements = Array.from(doc.querySelectorAll('[data-nordic-id]'));
    const nodeMap = {};
    const roots = [];

    elements.forEach((element, index) => {
        const id = String(element.getAttribute('data-nordic-id') || '').trim();

        if (!id) {
            return;
        }

        nodeMap[id] = {
            id,
            label: normalizeText(element.getAttribute('data-nordic-label') || element.getAttribute('aria-label') || id, 80),
            role: String(element.getAttribute('data-nordic-role') || '').trim(),
            tag: String(element.tagName || '').toLowerCase(),
            text: normalizeText(element.textContent || ''),
            order: index,
            children: []
        };
    });

    elements.forEach((element) => {
        const id = String(element.getAttribute('data-nordic-id') || '').trim();
        const parent = element.parentElement ? element.parentElement.closest('[data-nordic-id]') : null;
        const parentId = parent ? String(parent.getAttribute('data-nordic-id') || '').trim() : '';

        if (!id || !nodeMap[id]) {
            return;
        }

        if (parentId && nodeMap[parentId]) {
            nodeMap[parentId].children.push(nodeMap[id]);
            return;
        }

        roots.push(nodeMap[id]);
    });

    return {
        tree: roots.sort((left, right) => left.order - right.order),
        map: nodeMap
    };
}

export function filterLayerNodes(nodes, query) {
    if (!query) {
        return nodes;
    }

    return nodes.reduce((accumulator, node) => {
        const children = filterLayerNodes(node.children || [], query);
        const haystack = [node.label, node.role, node.id, node.text].join(' ').toLowerCase();

        if (haystack.includes(query) || children.length > 0) {
            accumulator.push({ ...node, children });
        }

        return accumulator;
    }, []);
}