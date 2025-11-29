<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Organizational Hierarchy Tree' ?> - ABO-WBO Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- D3.js for tree visualization -->
    <script src="https://d3js.org/d3.v7.min.js"></script>
    
    <style>
        :root {
            --abo-primary: #dc3545;
            --abo-secondary: #28a745;
            --godina-color: #dc3545;
            --gamta-color: #28a745;
            --gurmu-color: #ffc107;
            --global-color: #343a40;
        }

        body {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            min-height: 100vh;
        }

        .tree-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 98%;
        }

        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .legend {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        #tree-svg {
            width: 100%;
            height: 800px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            background: #f8f9fa;
            cursor: grab;
        }

        #tree-svg:active {
            cursor: grabbing;
        }

        .node circle {
            fill: #fff;
            stroke-width: 3px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .node circle:hover {
            stroke-width: 5px;
            filter: brightness(1.1);
        }

        .node text {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }

        .node.global circle {
            stroke: var(--global-color);
            fill: var(--global-color);
        }

        .node.godina circle {
            stroke: var(--godina-color);
        }

        .node.gamta circle {
            stroke: var(--gamta-color);
        }

        .node.gurmu circle {
            stroke: var(--gurmu-color);
        }

        .link {
            fill: none;
            stroke: #ccc;
            stroke-width: 2px;
        }

        .node-badge {
            font-size: 10px;
            fill: #666;
        }

        .tooltip {
            position: absolute;
            text-align: left;
            padding: 12px;
            font-size: 12px;
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-width: 300px;
            z-index: 1000;
        }

        .tooltip.show {
            opacity: 1;
        }

        .tooltip h6 {
            margin: 0 0 8px 0;
            color: var(--abo-primary);
            font-weight: 600;
        }

        .tooltip p {
            margin: 4px 0;
            line-height: 1.4;
        }

        .stats-bar {
            background: linear-gradient(90deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            display: flex;
            justify-content: space-around;
            margin-bottom: 1rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .btn-zoom {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-zoom:hover {
            background: #f8f9fa;
            transform: scale(1.1);
        }

        .loading {
            text-align: center;
            padding: 3rem;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="tree-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">
                    <i class="bi bi-diagram-3 me-2" style="color: var(--abo-primary);"></i>
                    Organizational Hierarchy Tree
                </h2>
                <a href="/hierarchy" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Hierarchy
                </a>
            </div>

            <!-- Statistics Bar -->
            <div class="stats-bar" id="stats-bar">
                <div class="stat-item">
                    <span class="stat-value" id="total-godinas">-</span>
                    <span class="stat-label">Godinas</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" id="total-gamtas">-</span>
                    <span class="stat-label">Gamtas</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" id="total-gurmus">-</span>
                    <span class="stat-label">Gurmus</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" id="total-users">-</span>
                    <span class="stat-label">Total Users</span>
                </div>
            </div>

            <!-- Controls -->
            <div class="controls">
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: var(--global-color);"></div>
                        <span>Global</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: var(--godina-color);"></div>
                        <span>Godina (Regional)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: var(--gamta-color);"></div>
                        <span>Gamta (Local)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: var(--gurmu-color);"></div>
                        <span>Gurmu (Community)</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn-zoom" id="zoom-in" title="Zoom In">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                    <button class="btn-zoom" id="zoom-out" title="Zoom Out">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <button class="btn-zoom" id="reset-zoom" title="Reset View">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <button class="btn btn-outline-primary btn-sm" id="expand-all">
                        <i class="bi bi-arrows-expand me-1"></i> Expand All
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" id="collapse-all">
                        <i class="bi bi-arrows-collapse me-1"></i> Collapse All
                    </button>
                </div>
            </div>

            <!-- Tree Visualization -->
            <div id="loading" class="loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading organizational hierarchy...</p>
            </div>
            <svg id="tree-svg" style="display: none;"></svg>
        </div>
    </div>

    <!-- Tooltip -->
    <div id="tooltip" class="tooltip"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Tree visualization configuration
        const width = document.getElementById('tree-svg').clientWidth;
        const height = 800;
        const margin = { top: 40, right: 120, bottom: 40, left: 120 };

        // Create SVG
        const svg = d3.select('#tree-svg')
            .attr('width', width)
            .attr('height', height);

        const g = svg.append('g')
            .attr('transform', `translate(${margin.left},${margin.top})`);

        // Zoom behavior
        const zoom = d3.zoom()
            .scaleExtent([0.1, 3])
            .on('zoom', (event) => {
                g.attr('transform', event.transform);
            });

        svg.call(zoom);

        // Zoom controls
        document.getElementById('zoom-in').addEventListener('click', () => {
            svg.transition().duration(300).call(zoom.scaleBy, 1.3);
        });

        document.getElementById('zoom-out').addEventListener('click', () => {
            svg.transition().duration(300).call(zoom.scaleBy, 0.7);
        });

        document.getElementById('reset-zoom').addEventListener('click', () => {
            svg.transition().duration(500).call(
                zoom.transform,
                d3.zoomIdentity.translate(margin.left, margin.top)
            );
        });

        // Tooltip
        const tooltip = d3.select('#tooltip');

        // Tree layout
        const treeLayout = d3.tree()
            .size([height - margin.top - margin.bottom, width - margin.left - margin.right - 200]);

        let root;
        let nodeData;

        // Load data
        fetch('/hierarchy/api/tree-data', {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                nodeData = data;
                updateStatistics(data);
                renderTree(data);
                document.getElementById('loading').style.display = 'none';
                document.getElementById('tree-svg').style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading tree data:', error);
                document.getElementById('loading').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Failed to load hierarchy data: ${error.message}<br>
                        <small>Please make sure you are logged in and try refreshing the page.</small>
                    </div>
                `;
            });

        function updateStatistics(data) {
            let godinaCount = 0;
            let gamtaCount = 0;
            let gurmuCount = 0;
            let userCount = 0;

            function countNodes(node) {
                if (node.type === 'godina') godinaCount++;
                if (node.type === 'gamta') gamtaCount++;
                if (node.type === 'gurmu') gurmuCount++;
                if (node.userCount) userCount += node.userCount;
                
                if (node.children) {
                    node.children.forEach(countNodes);
                }
            }

            countNodes(data);

            document.getElementById('total-godinas').textContent = godinaCount;
            document.getElementById('total-gamtas').textContent = gamtaCount;
            document.getElementById('total-gurmus').textContent = gurmuCount;
            document.getElementById('total-users').textContent = userCount;
        }

        function renderTree(data) {
            root = d3.hierarchy(data);
            root.x0 = height / 2;
            root.y0 = 0;

            // Collapse all children initially except first level
            if (root.children) {
                root.children.forEach(collapseAfterLevel);
            }

            update(root);
        }

        function collapseAfterLevel(d) {
            if (d.children) {
                d._children = d.children;
                d._children.forEach(collapseAfterLevel);
                d.children = null;
            }
        }

        function expandAll(d) {
            if (d._children) {
                d.children = d._children;
                d._children = null;
            }
            if (d.children) {
                d.children.forEach(expandAll);
            }
        }

        function collapseAll(d) {
            if (d.children) {
                d._children = d.children;
                d._children.forEach(collapseAll);
                d.children = null;
            }
        }

        document.getElementById('expand-all').addEventListener('click', () => {
            expandAll(root);
            update(root);
        });

        document.getElementById('collapse-all').addEventListener('click', () => {
            if (root.children) {
                root.children.forEach(collapseAfterLevel);
            }
            update(root);
        });

        function update(source) {
            const treeData = treeLayout(root);
            const nodes = treeData.descendants();
            const links = treeData.links();

            // Normalize for fixed-depth
            nodes.forEach(d => {
                d.y = d.depth * 250;
            });

            // Update nodes
            const node = g.selectAll('.node')
                .data(nodes, d => d.id || (d.id = Math.random()));

            // Enter new nodes
            const nodeEnter = node.enter().append('g')
                .attr('class', d => `node ${d.data.type}`)
                .attr('transform', d => `translate(${source.y0},${source.x0})`)
                .on('click', click)
                .on('mouseover', showTooltip)
                .on('mouseout', hideTooltip);

            nodeEnter.append('circle')
                .attr('r', 8)
                .style('fill', d => d._children ? getNodeColor(d.data.type) : '#fff');

            nodeEnter.append('text')
                .attr('dy', '.35em')
                .attr('x', d => d.children || d._children ? -13 : 13)
                .attr('text-anchor', d => d.children || d._children ? 'end' : 'start')
                .text(d => `${d.data.name} ${d.data.code ? '(' + d.data.code + ')' : ''}`);

            // User count badge
            nodeEnter.append('text')
                .attr('class', 'node-badge')
                .attr('dy', '1.8em')
                .attr('x', d => d.children || d._children ? -13 : 13)
                .attr('text-anchor', d => d.children || d._children ? 'end' : 'start')
                .style('fill', '#666')
                .text(d => d.data.userCount ? `👤 ${d.data.userCount}` : '');

            // Merge and update
            const nodeUpdate = nodeEnter.merge(node);

            nodeUpdate.transition()
                .duration(500)
                .attr('transform', d => `translate(${d.y},${d.x})`);

            nodeUpdate.select('circle')
                .attr('r', 8)
                .style('fill', d => d._children ? getNodeColor(d.data.type) : '#fff');

            // Remove exiting nodes
            const nodeExit = node.exit().transition()
                .duration(500)
                .attr('transform', d => `translate(${source.y},${source.x})`)
                .remove();

            nodeExit.select('circle').attr('r', 0);
            nodeExit.select('text').style('fill-opacity', 0);

            // Update links
            const link = g.selectAll('.link')
                .data(links, d => d.target.id);

            const linkEnter = link.enter().insert('path', 'g')
                .attr('class', 'link')
                .attr('d', d => {
                    const o = { x: source.x0, y: source.y0 };
                    return diagonal(o, o);
                });

            const linkUpdate = linkEnter.merge(link);

            linkUpdate.transition()
                .duration(500)
                .attr('d', d => diagonal(d.source, d.target));

            link.exit().transition()
                .duration(500)
                .attr('d', d => {
                    const o = { x: source.x, y: source.y };
                    return diagonal(o, o);
                })
                .remove();

            // Store old positions
            nodes.forEach(d => {
                d.x0 = d.x;
                d.y0 = d.y;
            });
        }

        function diagonal(s, d) {
            return `M ${s.y} ${s.x}
                    C ${(s.y + d.y) / 2} ${s.x},
                      ${(s.y + d.y) / 2} ${d.x},
                      ${d.y} ${d.x}`;
        }

        function click(event, d) {
            if (d.children) {
                d._children = d.children;
                d.children = null;
            } else {
                d.children = d._children;
                d._children = null;
            }
            update(d);
        }

        function getNodeColor(type) {
            const colors = {
                'global': 'var(--global-color)',
                'godina': 'var(--godina-color)',
                'gamta': 'var(--gamta-color)',
                'gurmu': 'var(--gurmu-color)'
            };
            return colors[type] || '#999';
        }

        function showTooltip(event, d) {
            const data = d.data;
            let content = `<h6>${data.name}</h6>`;
            
            if (data.code) content += `<p><strong>Code:</strong> ${data.code}</p>`;
            if (data.type) content += `<p><strong>Type:</strong> ${data.type.charAt(0).toUpperCase() + data.type.slice(1)}</p>`;
            if (data.description) content += `<p><strong>Description:</strong> ${data.description}</p>`;
            if (data.userCount) content += `<p><strong>Users:</strong> ${data.userCount}</p>`;
            if (data.contact_email) content += `<p><strong>Email:</strong> ${data.contact_email}</p>`;
            if (data.contact_phone) content += `<p><strong>Phone:</strong> ${data.contact_phone}</p>`;
            if (data.address) content += `<p><strong>Address:</strong> ${data.address}</p>`;

            tooltip.html(content)
                .style('left', (event.pageX + 10) + 'px')
                .style('top', (event.pageY - 10) + 'px')
                .classed('show', true);
        }

        function hideTooltip() {
            tooltip.classed('show', false);
        }
    </script>
</body>
</html>
