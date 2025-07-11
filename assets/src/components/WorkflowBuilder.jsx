import React, { useState, useCallback, useEffect } from 'react';
import ReactFlow, {
    ReactFlowProvider,
    addEdge,
    useNodesState,
    useEdgesState,
    Controls,
    Background,
    Panel,
    MiniMap
} from 'reactflow';
import 'reactflow/dist/style.css';

// Node types would be imported here
// import WebhookNode from './nodes/WebhookNode';
// import EmailNode from './nodes/EmailNode';

const WorkflowBuilder = ({ workflowId }) => {
    const [nodes, setNodes, onNodesChange] = useNodesState([]);
    const [edges, setEdges, onEdgesChange] = useEdgesState([]);
    const [selectedNode, setSelectedNode] = useState(null);
    const [isSaving, setIsSaving] = useState(false);
    const [lastSaved, setLastSaved] = useState(null);

    // Load workflow data
    useEffect(() => {
        if (workflowId) {
            loadWorkflow();
        }
    }, [workflowId]);

    const loadWorkflow = async () => {
        try {
            const response = await wp.apiRequest({
                path: `wa/v1/workflows/${workflowId}`,
                method: 'GET'
            });

            if (response.flow_data) {
                setNodes(response.flow_data.nodes || []);
                setEdges(response.flow_data.edges || []);
            }
        } catch (error) {
            console.error('Failed to load workflow:', error);
        }
    };

    const onConnect = useCallback((params) => {
        setEdges((eds) => addEdge(params, eds));
    }, []);

    const onNodeClick = useCallback((event, node) => {
        setSelectedNode(node);
    }, []);

    const saveWorkflow = async () => {
        if (!workflowId) return;

        setIsSaving(true);

        try {
            await wp.apiRequest({
                path: `wa/v1/workflows/${workflowId}`,
                method: 'PUT',
                data: {
                    flow_data: {
                        nodes,
                        edges
                    }
                }
            });

            setLastSaved(new Date());
            
            // Show success message
            const saveStatus = document.querySelector('.save-status');
            if (saveStatus) {
                saveStatus.textContent = wa_builder.i18n.saved;
                saveStatus.classList.add('success');
                setTimeout(() => {
                    saveStatus.textContent = '';
                    saveStatus.classList.remove('success');
                }, 3000);
            }
        } catch (error) {
            console.error('Failed to save workflow:', error);
            
            // Show error message
            const saveStatus = document.querySelector('.save-status');
            if (saveStatus) {
                saveStatus.textContent = 'Save failed';
                saveStatus.classList.add('error');
            }
        } finally {
            setIsSaving(false);
        }
    };

    // Auto-save
    useEffect(() => {
        const timer = setTimeout(() => {
            if (nodes.length > 0 && !isSaving) {
                saveWorkflow();
            }
        }, 2000);

        return () => clearTimeout(timer);
    }, [nodes, edges]);

    const onDrop = useCallback(
        (event) => {
            event.preventDefault();

            const reactFlowBounds = event.target.getBoundingClientRect();
            const type = event.dataTransfer.getData('application/reactflow');

            if (typeof type === 'undefined' || !type) {
                return;
            }

            const position = {
                x: event.clientX - reactFlowBounds.left,
                y: event.clientY - reactFlowBounds.top,
            };

            const newNode = {
                id: `${type}_${Date.now()}`,
                type,
                position,
                data: { label: `${type} node` },
            };

            setNodes((nds) => nds.concat(newNode));
        },
        []
    );

    const onDragOver = useCallback((event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
    }, []);

    return (
        <div style={{ height: '600px' }}>
            <ReactFlow
                nodes={nodes}
                edges={edges}
                onNodesChange={onNodesChange}
                onEdgesChange={onEdgesChange}
                onConnect={onConnect}
                onNodeClick={onNodeClick}
                onDrop={onDrop}
                onDragOver={onDragOver}
                fitView
            >
                <Background />
                <Controls />
                <MiniMap />
                <Panel position="top-left">
                    <NodePalette />
                </Panel>
            </ReactFlow>
        </div>
    );
};

const NodePalette = () => {
    const nodeTypes = wa_builder.node_types || {};

    const onDragStart = (event, nodeType) => {
        event.dataTransfer.setData('application/reactflow', nodeType);
        event.dataTransfer.effectAllowed = 'move';
    };

    return (
        <div className="node-palette">
            <h3>Nodes</h3>
            {Object.entries(nodeTypes).map(([category, nodes]) => (
                <div key={category} className="node-category">
                    <h4>{category}</h4>
                    {nodes.map((node) => (
                        <div
                            key={node.type}
                            className="node-item"
                            onDragStart={(event) => onDragStart(event, node.type)}
                            draggable
                        >
                            <span className={`dashicons ${node.icon}`}></span>
                            {node.label}
                        </div>
                    ))}
                </div>
            ))}
        </div>
    );
};

// Mount the app
const root = document.getElementById('workflow-builder-root');
if (root) {
    const workflowId = wa_builder.workflow_id || 0;
    
    ReactDOM.createRoot(root).render(
        <ReactFlowProvider>
            <WorkflowBuilder workflowId={workflowId} />
        </ReactFlowProvider>
    );
}