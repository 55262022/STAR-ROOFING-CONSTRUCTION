<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

$welcome_message = '';
if (isset($_SESSION['success'])) {
    $welcome_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Construction Model Configurator - Star Roofing & Construction</title>
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Three.js -->
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/examples/js/controls/OrbitControls.js"></script>
    <!-- CSS2DRenderer for labels -->
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/examples/js/renderers/CSS2DRenderer.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS style -->
    <link rel="stylesheet" href="../css/admin_main.css">
    <link rel="stylesheet" href="../css/admin_3dmodel.css">
    <style>
        .truss-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            padding: 4px 6px;
            background: rgba(0,0,0,0.7);
            color: #fff;
            border-radius: 4px;
            white-space: nowrap;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }
        #configurator-viewer { width:100%; height:560px; }
        .truss-controls { margin-left: 16px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
        .truss-controls label { display:flex; gap:6px; align-items:center; }
        .material-swatch.selected { outline: 2px solid #333; }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <?php include '../includes/admin_navbar.php'; ?>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">3D Construction Model Configurator</h1>
                        <p class="page-description">Design and configure your construction project in 3D</p>
                    </div>
                    <button class="btn btn-primary" id="saveConfigBtn">
                        <i class="fas fa-save"></i> Save Configuration
                    </button>
                </div>
                
                <!-- 3D Configurator Section -->
                <div class="card">
                    <div class="card-body">
                        <div class="main-layout">
                            <!-- Sidebar: Materials & Bill of Materials -->
                            <div class="sidebar">
                                <h2><i class="fa fa-cubes"></i> Construction Materials</h2>
                                <div class="materials-list" id="materials-list">
                                    <!-- JS will populate materials here -->
                                </div>
                                <div class="bill-section">
                                    <h3><i class="fa fa-file-invoice-dollar"></i> Bill of Materials</h3>
                                    <table class="bill-table" id="bill-table">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Qty</th>
                                                <th>Cost</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- JS will populate bill here -->
                                        </tbody>
                                    </table>
                                    <div class="bill-total" id="bill-total">Total: ₱0.00</div>
                                </div>
                            </div>
                            <!-- 3D Viewer -->
                            <div class="viewer-container">
                                <div class="viewer-header">
                                    <i class="fa fa-hard-hat"></i> 3D Construction Model Configurator
                                </div>
                                <div class="controls-bar">
                                    <label>Roof Type:
                                    <select id="roof-type">
                                        <option value="gable">Gable</option>
                                        <option value="hip">Hip</option>
                                        <option value="flat">Flat</option>
                                        <option value="shed">Shed</option>
                                        <option value="mansard">Mansard</option>
                                        <option value="gambrel">Gambrel</option>
                                        <option value="butterfly">Butterfly</option>
                                        <option value="dome">Dome</option>
                                        <option value="skillion">Skillion</option>
                                        <option value="curved">Curved / Arch</option>
                                    </select>
                                    </label>
                                    <label>Pitch:
                                        <input type="range" id="roof-pitch" min="0" max="60" step="1" value="30">
                                        <span id="pitch-value">30°</span>
                                    </label>
                                    <label>Width:
                                        <input type="range" id="roof-width" min="5" max="20" step="1" value="10">
                                        <span id="width-value">10m</span>
                                    </label>
                                    <label>Length:
                                        <input type="range" id="roof-length" min="5" max="20" step="1" value="15">
                                        <span id="length-value">15m</span>
                                    </label>

                                    <!-- TRUSS CONTROLS (ADDED) -->
                                    <div class="truss-controls">
                                        <label>Truss Type:
                                            <select id="truss-type-select">
                                                <option value="queen_post">Queen Post Truss</option>
                                                <option value="king_post">King Post Truss</option>
                                                <!-- extend later or load from DB -->
                                            </select>
                                        </label>

                                        <label>Span (m):
                                            <input type="number" id="truss-span" min="4.9" max="12.2" step="0.1" value="8.0" style="width:80px;">
                                        </label>

                                        <label>Height (m):
                                            <input type="number" id="truss-height" min="0.81" max="6.1" step="0.05" value="1.2" style="width:80px;">
                                        </label>

                                        <label>Thickness (mm):
                                            <input type="number" id="truss-thickness" min="38" max="114" step="1" value="38" style="width:90px;">
                                        </label>

                                        <button id="add-truss-btn" class="btn btn-secondary"><i class="fa fa-plus"></i> Add Truss</button>
                                    </div>

                                    <div class="material-select">
                                        <span>Material:</span>
                                        <span class="material-swatch selected" style="background:#8B4513" data-color="#8B4513" title="Wood"></span>
                                        <span class="material-swatch" style="background:#A9A9A9" data-color="#A9A9A9" title="Steel"></span>
                                        <span class="material-swatch" style="background:#FFD700" data-color="#FFD700" title="Aluminum"></span>
                                        <span class="material-swatch" style="background:#654321" data-color="#654321" title="Dark Wood"></span>
                                        <span class="material-swatch" style="background:#C0C0C0" data-color="#C0C0C0" title="Metal"></span>
                                        <span class="material-swatch" style="background:#8B7355" data-color="#8B7355" title="Copper"></span>
                                    </div>
                                </div>
                                <div id="configurator-viewer"></div>
                                <div class="selected-element-info" id="selected-element-info">
                                    <h4>Selected Element</h4>
                                    <div><b>Type:</b> <span id="selected-type"></span></div>
                                    <div><b>Material:</b> <span id="selected-material"></span></div>
                                    
                                    <button id="enable-move" class="toggle-move"><i class="fa fa-arrows-alt"></i> Move</button>
                                    <button id="remove-selected"><i class="fa fa-trash"></i> Remove</button>
                                    
                                    <div class="rotate-controls">
                                        <label>Rotation:</label>
                                        <div class="rotate-inputs">
                                            <div class="rotate-input-group">
                                                <input type="number" id="rotate-x" value="0" step="5" min="-180" max="180">
                                                <span>X°</span>
                                            </div>
                                            <div class="rotate-input-group">
                                                <input type="number" id="rotate-y" value="0" step="5" min="-180" max="180">
                                                <span>Y°</span>
                                            </div>
                                            <div class="rotate-input-group">
                                                <input type="number" id="rotate-z" value="0" step="5" min="-180" max="180">
                                                <span>Z°</span>
                                            </div>
                                        </div>
                                        <button id="apply-rotation"><i class="fa fa-sync"></i> Apply Rotation</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 3D Configurator Code (UPDATED to include truss creation + measurements)
        /* --- MATERIALS/COMPONENTS DATABASE --- */
        const MATERIALS = [
            {
                id: 'truss',
                name: 'Truss',
                icon: 'fa-project-diagram',
                color: '#8B4513',
                cost: 1200,
                geometry: () => {
                    const shape = new THREE.Shape();
                    shape.moveTo(-1, 0);
                    shape.lineTo(0, 0.8);
                    shape.lineTo(1, 0);
                    shape.lineTo(-1, 0);
                    return new THREE.ExtrudeGeometry(shape, { depth: 0.15, bevelEnabled: false });
                }
            },
            {
                id: 'beam',
                name: 'Beam',
                icon: 'fa-grip-lines',
                color: '#4a9eff',
                cost: 800,
                geometry: () => new THREE.BoxGeometry(2, 0.15, 0.15)
            },
            {
                id: 'rafter',
                name: 'Rafter',
                icon: 'fa-slash',
                color: '#6bff91',
                cost: 600,
                geometry: () => new THREE.BoxGeometry(0.15, 0.15, 2)
            },
            {
                id: 'purlin',
                name: 'Purlin',
                icon: 'fa-grip-lines-vertical',
                color: '#ffa500',
                cost: 400,
                geometry: () => new THREE.BoxGeometry(0.15, 0.15, 1.5)
            },
            {
                id: 'column',
                name: 'Column',
                icon: 'fa-columns',
                color: '#9b59b6',
                cost: 1000,
                geometry: () => new THREE.CylinderGeometry(0.12, 0.12, 2, 12)
            },
            {
                id: 'bracing',
                name: 'Bracing',
                icon: 'fa-xmark',
                color: '#1abc9c',
                cost: 350,
                geometry: () => new THREE.BoxGeometry(0.1, 0.1, 1.2)
            }
        ];

        /* --- TRUSS TYPE DEFAULTS (can be loaded from DB later) --- */
        const TRUSS_TYPES = {
            queen_post: {
                displayName: 'Queen Post Truss',
                spanMin: 4.9, // meters
                spanMax: 12.2,
                heightMin: 0.81,
                heightMax: 6.1,
                // thickness options in mm (converted to meters when used)
                thicknessOptions: [38, 76, 114],
                notes: 'Typical spans 4.9–12.2 m; heights 0.81–6.1 m; thickness 38/76/114 mm'
            },
            king_post: {
                displayName: 'King Post Truss',
                spanMin: 3.0,
                spanMax: 9.0,
                heightMin: 0.6,
                heightMax: 4.0,
                thicknessOptions: [38, 76],
                notes: 'Simpler single central post'
            }
        };

        /* --- BILL OF MATERIALS STATE --- */
        let bill = {}; // { id: { qty, meshIds: [] } }
        let meshIdCounter = 1;

        /* --- 3D SCENE SETUP --- */
        let scene, camera, renderer, controls, labelRenderer;
        let meshes = {}; 
        let roofBase, roofType = 'gable', roofPitch = 30, roofWidth = 10, roofLength = 15;
        let currentMaterial = '#8B4513';
        let currentMaterialName = 'Wood';
        let selectedMeshId = null;

        /* --- Interaction globals --- */
        const raycaster = new THREE.Raycaster();
        const mouse = new THREE.Vector2();
        let isMoveMode = false;
        let isDragging = false;
        let activeMesh = null;
        let dragPlane = new THREE.Plane();
        let dragOffset = new THREE.Vector3();

        /* --- Highlight globals --- */
        let highlightedMesh = null;
        let originalMaterial = null;

        function init3D() {
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0xecf0f1);

            const container = document.getElementById('configurator-viewer');
            camera = new THREE.PerspectiveCamera(60, container.clientWidth / container.clientHeight, 0.1, 1000);
            camera.position.set(6, 6, 10);

            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(container.clientWidth, container.clientHeight);
            container.appendChild(renderer.domElement);

            // CSS2D renderer for labels
            labelRenderer = new THREE.CSS2DRenderer();
            labelRenderer.setSize(container.clientWidth, container.clientHeight);
            labelRenderer.domElement.style.position = 'absolute';
            labelRenderer.domElement.style.top = '0';
            labelRenderer.domElement.style.pointerEvents = 'none';
            container.appendChild(labelRenderer.domElement);

            controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;

            scene.add(new THREE.AmbientLight(0xffffff, 0.7));
            const dirLight = new THREE.DirectionalLight(0xffffff, 0.7);
            dirLight.position.set(10, 10, 10);
            scene.add(dirLight);

            scene.add(new THREE.GridHelper(20, 20));
            scene.add(new THREE.AxesHelper(2));

            createRoof();

            // Add event listeners for interaction
            renderer.domElement.addEventListener('pointerdown', onPointerDown, false);
            renderer.domElement.addEventListener('pointermove', onPointerMove, false);
            renderer.domElement.addEventListener('pointerup', onPointerUp, false);

            animate();
            window.addEventListener('resize', onResize);
        }

        function animate() {
            requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
            labelRenderer.render(scene, camera);
        }

        /* --- ROOF GENERATION --- */
        function createRoof() {
            // Remove previous roof (keep other scene children)
            if (roofBase) scene.remove(roofBase);

            // Foundation
            const baseGeometry = new THREE.BoxGeometry(roofWidth, 0.2, roofLength);
            const baseMaterial = new THREE.MeshPhongMaterial({ color: 0x666666 });
            roofBase = new THREE.Mesh(baseGeometry, baseMaterial);
            roofBase.position.y = -0.1;
            scene.add(roofBase);

            // Remove previous roof meshes (safe removal)
            const toRemove = [];
            scene.traverse(obj => {
                if (obj.userData && obj.userData.roofMesh) toRemove.push(obj);
            });
            toRemove.forEach(o => scene.remove(o));

            // Add roof mesh (existing code)
            let roofMesh = null;
            const roofMat = new THREE.MeshPhongMaterial({ color: currentMaterial, transparent: true, opacity: 0.7 });
            const pitchRad = roofPitch * Math.PI / 180;

            if (roofType === 'gable') {
                const roofShape = new THREE.Shape();
                const roofH = (roofWidth / 2) * Math.tan(pitchRad);
                roofShape.moveTo(-roofWidth/2, 0);
                roofShape.lineTo(0, roofH);
                roofShape.lineTo(roofWidth/2, 0);
                const extrudeSettings = { depth: roofLength, bevelEnabled: false };
                const roofGeometry = new THREE.ExtrudeGeometry(roofShape, extrudeSettings);
                roofMesh = new THREE.Mesh(roofGeometry, roofMat);
                roofMesh.rotation.z = Math.PI / 2;
                roofMesh.position.y = 0.2;
            } else if (roofType === 'hip') {
                const roofH = (roofWidth / 2) * Math.tan(pitchRad);
                const roofGeometry = new THREE.ConeGeometry(roofWidth / 2 * 1.2, roofH, 4);
                roofMesh = new THREE.Mesh(roofGeometry, roofMat);
                roofMesh.position.y = roofH / 2 + 0.2;
            } else if (roofType === 'flat') {
                const roofGeometry = new THREE.BoxGeometry(roofWidth, 0.2, roofLength);
                roofMesh = new THREE.Mesh(roofGeometry, roofMat);
                roofMesh.position.y = 0.2;
            } else if (roofType === 'shed') {
                const roofH = roofWidth * Math.tan(pitchRad);
                const roofShape = new THREE.Shape();
                roofShape.moveTo(-roofWidth/2, 0);
                roofShape.lineTo(-roofWidth/2, roofH);
                roofShape.lineTo(roofWidth/2, 0);
                const extrudeSettings = { depth: roofLength, bevelEnabled: false };
                const roofGeometry = new THREE.ExtrudeGeometry(roofShape, extrudeSettings);
                roofMesh = new THREE.Mesh(roofGeometry, roofMat);
                roofMesh.rotation.z = Math.PI / 2;
                roofMesh.position.y = 0.2;
            } else if (roofType === 'mansard') {
                const roofH = (roofWidth / 2) * Math.tan(pitchRad);
                const geometry = new THREE.CylinderGeometry(roofWidth / 1.2, roofWidth / 2, roofH, 4, 1, true);
                roofMesh = new THREE.Mesh(geometry, roofMat);
                roofMesh.rotation.y = Math.PI / 4;
                roofMesh.position.y = roofH / 2 + 0.2;
            } else if (roofType === 'gambrel') {
                const roofShape = new THREE.Shape();
                const h1 = (roofWidth / 4) * Math.tan(pitchRad / 2);
                const h2 = (roofWidth / 4) * Math.tan(pitchRad);
                roofShape.moveTo(-roofWidth/2, 0);
                roofShape.lineTo(-roofWidth/4, h1);
                roofShape.lineTo(roofWidth/4, h2);
                roofShape.lineTo(roofWidth/2, 0);
                const extrudeSettings = { depth: roofLength, bevelEnabled: false };
                const roofGeometry = new THREE.ExtrudeGeometry(roofShape, extrudeSettings);
                roofMesh = new THREE.Mesh(roofGeometry, roofMat);
                roofMesh.rotation.z = Math.PI / 2;
                roofMesh.position.y = 0.2;
            } else if (roofType === 'butterfly') {
                const roofShape = new THREE.Shape();
                const roofH = (roofWidth / 2) * Math.tan(pitchRad);
                roofShape.moveTo(-roofWidth/2, roofH);
                roofShape.lineTo(0, 0);
                roofShape.lineTo(roofWidth/2, roofH);
                const extrudeSettings = { depth: roofLength, bevelEnabled: false };
                const roofGeometry = new THREE.ExtrudeGeometry(roofShape, extrudeSettings);
                roofMesh = new THREE.Mesh(roofGeometry, roofMat);
                roofMesh.rotation.z = Math.PI / 2;
                roofMesh.position.y = 0.2;
            } else if (roofType === 'dome') {
                const roofGeometry = new THREE.SphereGeometry(roofWidth / 2, 32, 16, 0, Math.PI * 2, 0, Math.PI / 2);
                roofMesh = new THREE.Mesh(roofGeometry, roofMat);
                roofMesh.position.y = roofWidth / 4 + 0.2;
            } else if (roofType === 'skillion') {
                const roofH = (roofWidth / 1.5) * Math.tan(pitchRad);
                const roofShape = new THREE.Shape();
                roofShape.moveTo(-roofWidth/2, 0);
                roofShape.lineTo(-roofWidth/2, roofH);
                roofShape.lineTo(roofWidth/2, 0);
                const extrudeSettings = { depth: roofLength, bevelEnabled: false };
                const roofGeometry = new THREE.ExtrudeGeometry(roofShape, extrudeSettings);
                roofMesh = new THREE.Mesh(roofGeometry, roofMat);
                roofMesh.rotation.z = Math.PI / 2;
                roofMesh.position.y = 0.2;
            } else if (roofType === 'curved') {
                const curve = new THREE.QuadraticBezierCurve3(
                    new THREE.Vector3(-roofWidth / 2, 0, 0),
                    new THREE.Vector3(0, roofWidth / 4, 0),
                    new THREE.Vector3(roofWidth / 2, 0, 0)
                );
                const points = curve.getPoints(50);
                const shape = new THREE.Shape(points.map(p => new THREE.Vector2(p.x, p.y)));
                const extrudeSettings = { depth: roofLength, bevelEnabled: false };
                const geometry = new THREE.ExtrudeGeometry(shape, extrudeSettings);
                roofMesh = new THREE.Mesh(geometry, roofMat);
                roofMesh.rotation.z = Math.PI / 2;
                roofMesh.position.y = 0.2;
            }

            if (roofMesh) {
                roofMesh.userData.roofMesh = true;
                scene.add(roofMesh);
            }
        }

        /* --- MATERIALS SIDEBAR --- */
        function renderMaterialsSidebar() {
            const list = document.getElementById('materials-list');
            list.innerHTML = '';
            MATERIALS.forEach(mat => {
                const div = document.createElement('div');
                div.className = 'material-item';
                div.innerHTML = `
                    <span class="material-icon"><i class="fa ${mat.icon}"></i></span>
                    <div class="material-info">
                        <div class="material-title">${mat.name}</div>
                        <div class="material-cost">₱${mat.cost}</div>
                    </div>
                    <button class="add-btn" data-id="${mat.id}"><i class="fa fa-plus"></i> Add</button>
                `;
                list.appendChild(div);
            });
            // Add event listeners for Add buttons
            list.querySelectorAll('.add-btn').forEach(btn => {
                btn.onclick = () => addMaterialToScene(btn.getAttribute('data-id'));
            });
        }

        /* --- ADD MATERIAL TO 3D SCENE --- */
        function addMaterialToScene(materialId) {
            const mat = MATERIALS.find(m => m.id === materialId);
            if (!mat) return;
            // Create mesh
            const geometry = mat.geometry();
            const material = new THREE.MeshPhongMaterial({ color: currentMaterial });
            const mesh = new THREE.Mesh(geometry, material);
            mesh.position.set((Math.random() - 0.5) * (roofWidth-2), 1, (Math.random() - 0.5) * (roofLength-2));
            mesh.userData = { materialId, meshId: meshIdCounter, type: mat.name, color: currentMaterialName };
            scene.add(mesh);
            meshes[meshIdCounter] = { mesh, materialId };
            // Update bill
            if (!bill[materialId]) bill[materialId] = { qty: 0, meshIds: [] };
            bill[materialId].qty += 1;
            bill[materialId].meshIds.push(meshIdCounter);
            meshIdCounter++;
            renderBill();
        }

        /* --- ADD TRUSS PROCEDURALLY (NEW) --- */
        function addTrussToScene(trussData) {
            // trussData: { type, span (m), height (m), thickness_mm, positionZ }
            const span = parseFloat(trussData.span);
            const height = parseFloat(trussData.height);
            const thickness = parseFloat(trussData.thickness_mm) / 1000.0; // mm -> m

            const group = new THREE.Group();
            group.position.y = 0.1; // slightly above ground
            group.position.x = 0; // center
            group.position.z = trussData.positionZ || 0;

            // bottom chord
            const bottomGeom = new THREE.BoxGeometry(span, thickness, 0.06);
            const bottomMat = new THREE.MeshPhongMaterial({ color: currentMaterial });
            const bottom = new THREE.Mesh(bottomGeom, bottomMat);
            bottom.position.set(0, thickness / 2, 0);
            group.add(bottom);

            // top chords (two sloped members)
            const halfSpan = span / 2;
            const topLeftGeom = new THREE.BoxGeometry(Math.sqrt(halfSpan*halfSpan + height*height), thickness, 0.06);
            const topRightGeom = new THREE.BoxGeometry(Math.sqrt(halfSpan*halfSpan + height*height), thickness, 0.06);

            const topLeft = new THREE.Mesh(topLeftGeom, bottomMat.clone());
            const topRight = new THREE.Mesh(topRightGeom, bottomMat.clone());

            // pivot positions and rotations
            topLeft.position.set(-halfSpan/2, height/2 + thickness/2, 0);
            topRight.position.set(halfSpan/2, height/2 + thickness/2, 0);

            const angle = Math.atan2(height, halfSpan);
            topLeft.rotation.z = angle;
            topRight.rotation.z = -angle;

            group.add(topLeft);
            group.add(topRight);

            // two queen posts (verticals) - for queen post truss
            if (trussData.type === 'queen_post') {
                const postGeom = new THREE.BoxGeometry(thickness, height, 0.06);
                const leftPost = new THREE.Mesh(postGeom, bottomMat.clone());
                const rightPost = new THREE.Mesh(postGeom, bottomMat.clone());
                leftPost.position.set(-halfSpan/2, height/2 + thickness/2, 0);
                rightPost.position.set(halfSpan/2, height/2 + thickness/2, 0);
                group.add(leftPost);
                group.add(rightPost);
            } else if (trussData.type === 'king_post') {
                // center post
                const postGeom = new THREE.BoxGeometry(thickness, height, 0.06);
                const centerPost = new THREE.Mesh(postGeom, bottomMat.clone());
                centerPost.position.set(0, height/2 + thickness/2, 0);
                group.add(centerPost);
            }

            // add dimension lines (span and height)
            const dimMaterial = new THREE.LineBasicMaterial({ color: 0x000000 });
            // Span line
            const spanPoints = [
                new THREE.Vector3(-halfSpan, 0.05 + thickness, 0.5),
                new THREE.Vector3(halfSpan, 0.05 + thickness, 0.5)
            ];
            const spanGeom = new THREE.BufferGeometry().setFromPoints(spanPoints);
            const spanLine = new THREE.Line(spanGeom, dimMaterial);
            group.add(spanLine);

            // Height line (left side)
            const heightPoints = [
                new THREE.Vector3(-halfSpan, 0.05 + thickness, 0.6),
                new THREE.Vector3(-halfSpan, height + thickness + 0.05, 0.6)
            ];
            const heightGeom = new THREE.BufferGeometry().setFromPoints(heightPoints);
            const heightLine = new THREE.Line(heightGeom, dimMaterial);
            group.add(heightLine);

            // Label creation using CSS2D
            const spanLabelDiv = document.createElement('div');
            spanLabelDiv.className = 'truss-label';
            spanLabelDiv.textContent = `Span: ${span.toFixed(2)} m`;
            const spanLabel = new THREE.CSS2DObject(spanLabelDiv);
            spanLabel.position.set(0, 0.05 + thickness + 0.2, 0.5);
            group.add(spanLabel);

            const heightLabelDiv = document.createElement('div');
            heightLabelDiv.className = 'truss-label';
            heightLabelDiv.textContent = `Height: ${height.toFixed(2)} m`;
            const heightLabel = new THREE.CSS2DObject(heightLabelDiv);
            heightLabel.position.set(-halfSpan, height / 2 + thickness / 2, 0.6);
            group.add(heightLabel);

            const thickLabelDiv = document.createElement('div');
            thickLabelDiv.className = 'truss-label';
            thickLabelDiv.textContent = `Thickness: ${Math.round(trussData.thickness_mm)} mm`;
            const thickLabel = new THREE.CSS2DObject(thickLabelDiv);
            thickLabel.position.set(halfSpan * 0.8, thickness + 0.05, 0.5);
            group.add(thickLabel);

            // finalize group
            group.userData = { materialId: 'truss', meshId: meshIdCounter, type: TRUSS_TYPES[trussData.type].displayName, color: currentMaterialName };

            scene.add(group);
            meshes[meshIdCounter] = { mesh: group, materialId: 'truss' };

            // Update bill (count trusses)
            if (!bill['truss']) bill['truss'] = { qty: 0, meshIds: [] };
            bill['truss'].qty += 1;
            bill['truss'].meshIds.push(meshIdCounter);
            meshIdCounter++;
            renderBill();
        }

        /* --- REMOVE MATERIAL FROM 3D SCENE --- */
        function removeMaterialFromScene(materialId, meshId) {
            if (meshes[meshId]) {
                // if it's a group, also remove any associated CSS2D objects
                scene.remove(meshes[meshId].mesh);
                delete meshes[meshId];
            }
            if (bill[materialId]) {
                bill[materialId].qty -= 1;
                bill[materialId].meshIds = bill[materialId].meshIds.filter(id => id !== meshId);
                if (bill[materialId].qty <= 0) delete bill[materialId];
            }
            renderBill();
            hideSelectedElementInfo();
        }

        /* --- BILL OF MATERIALS RENDER --- */
        function renderBill() {
            const tbody = document.querySelector('#bill-table tbody');
            tbody.innerHTML = '';
            let total = 0;
            Object.keys(bill).forEach(materialId => {
                const mat = MATERIALS.find(m => m.id === materialId) || { name: 'Truss', cost: 1200 };
                const qty = bill[materialId].qty;
                const cost = (mat.cost || 1200) * qty;
                total += cost;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${mat.name}</td>
                    <td>${qty}</td>
                    <td>₱${cost}</td>
                    <td>
                        <button class="remove-btn" title="Remove one" onclick="removeMaterialFromScene('${materialId}', ${bill[materialId].meshIds[0]})">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            document.getElementById('bill-total').textContent = `Total: ₱${total.toLocaleString(undefined, {minimumFractionDigits:2})}`;
        }

        /* --- MATERIAL SWATCHES --- */
        function setupMaterialSwatches() {
            document.querySelectorAll('.material-swatch').forEach(swatch => {
                swatch.addEventListener('click', function() {
                    document.querySelectorAll('.material-swatch').forEach(s => s.classList.remove('selected'));
                    this.classList.add('selected');
                    currentMaterial = this.getAttribute('data-color');
                    currentMaterialName = this.title;
                    createRoof();
                });
            });
        }

        /* --- ROOF CONTROLS --- */
        function setupRoofControls() {
            document.getElementById('roof-type').addEventListener('change', function() {
                roofType = this.value;
                createRoof();
            });
            document.getElementById('roof-pitch').addEventListener('input', function() {
                roofPitch = parseInt(this.value);
                document.getElementById('pitch-value').textContent = roofPitch + '°';
                createRoof();
            });
            document.getElementById('roof-width').addEventListener('input', function() {
                roofWidth = parseInt(this.value);
                document.getElementById('width-value').textContent = roofWidth + 'm';
                createRoof();
            });
            document.getElementById('roof-length').addEventListener('input', function() {
                roofLength = parseInt(this.value);
                document.getElementById('length-value').textContent = roofLength + 'm';
                createRoof();
            });

            // Truss add button
            document.getElementById('add-truss-btn').addEventListener('click', function() {
                const type = document.getElementById('truss-type-select').value;
                const span = parseFloat(document.getElementById('truss-span').value);
                const height = parseFloat(document.getElementById('truss-height').value);
                const thickness = parseFloat(document.getElementById('truss-thickness').value);

                // Basic range enforcement using TRUSS_TYPES
                const def = TRUSS_TYPES[type];
                if (span < def.spanMin || span > def.spanMax) {
                    Swal.fire('Invalid span', `${def.displayName} span must be between ${def.spanMin} and ${def.spanMax} m.`, 'warning');
                    return;
                }
                if (height < def.heightMin || height > def.heightMax) {
                    Swal.fire('Invalid height', `${def.displayName} height must be between ${def.heightMin} and ${def.heightMax} m.`, 'warning');
                    return;
                }
                // Add truss at Z=0 (you can adjust or allow multiple)
                addTrussToScene({ type, span, height, thickness_mm: thickness, positionZ: 0 });
            });
        }

        /* --- 3D SELECTION + MOVE (CORRECTED) --- */
        function onPointerDown(event) {
            event.preventDefault();
            
            const rect = renderer.domElement.getBoundingClientRect();
            mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
            mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

            raycaster.setFromCamera(mouse, camera);

            // Only raycast against your added meshes (not grid / roof)
            const objects = Object.values(meshes).map(m => m.mesh);
            const intersects = raycaster.intersectObjects(objects, true);

            if (intersects.length > 0) {
                // Find the top-level mesh that exists in meshes[]
                let picked = intersects[0].object;
                while (picked && !Object.values(meshes).some(m => m.mesh === picked)) {
                    picked = picked.parent;
                }
                if (!picked) return;

                if (isMoveMode) {
                    // Move mode: start dragging
                    activeMesh = picked;
                    selectedMeshId = activeMesh.userData.meshId;
                    selectElement(activeMesh);

                    // Create drag plane facing camera
                    const camDir = new THREE.Vector3();
                    camera.getWorldDirection(camDir);
                    dragPlane.setFromNormalAndCoplanarPoint(camDir.negate().normalize(), activeMesh.position);

                    // Get intersection point for offset calculation
                    const intersectPoint = raycaster.ray.intersectPlane(dragPlane, new THREE.Vector3());
                    if (intersectPoint) {
                        dragOffset.copy(intersectPoint).sub(activeMesh.position);
                        isDragging = true;
                        controls.enabled = false;
                    }
                } else {
                    // Selection mode: just select the element
                    selectElement(picked);
                }
            } else {
                // Clicked empty space
                if (!isMoveMode) {
                    hideSelectedElementInfo();
                }
            }
        }

        function onPointerMove(event) {
            if (!isDragging || !activeMesh) return;

            const rect = renderer.domElement.getBoundingClientRect();
            mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
            mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
            
            raycaster.setFromCamera(mouse, camera);
            
            const intersectPoint = raycaster.ray.intersectPlane(dragPlane, new THREE.Vector3());
            if (intersectPoint) {
                activeMesh.position.copy(intersectPoint.sub(dragOffset));
            }
        }

        function onPointerUp(event) {
            if (isDragging) {
                isDragging = false;
                activeMesh = null;
                // Only re-enable orbit controls if we're not in move mode
                if (!isMoveMode) {
                    controls.enabled = true;
                }
            }
        }

        /* --- SELECT / HIDE PANEL + HIGHLIGHT --- */
        function selectElement(mesh) {
            // Remove highlight from previous selection
            if (highlightedMesh && originalMaterial) {
                highlightedMesh.material = originalMaterial;
            }

            // If group, pick group root for userData
            let root = mesh;
            while (root && !root.userData.meshId && root.parent) root = root.parent;

            selectedMeshId = root.userData.meshId;
            highlightedMesh = root;
            // store originalMaterial only if mesh has material (groups may not)
            originalMaterial = root.material || null;

            // Apply temporary highlight if object has material
            if (root.material) {
                const highlightMaterial = new THREE.MeshPhongMaterial({
                    color: root.material.color,
                    emissive: 0xffff00,
                    emissiveIntensity: 0.6
                });
                root.material = highlightMaterial;
            }

            // Show element info panel
            const info = document.getElementById('selected-element-info');
            info.classList.add('active');
            document.getElementById('selected-type').textContent = root.userData.type || '—';
            document.getElementById('selected-material').textContent = root.userData.color || currentMaterialName;
        }

        function hideSelectedElementInfo() {
            selectedMeshId = null;
            const info = document.getElementById('selected-element-info');
            info.classList.remove('active');

            // Reset highlight
            if (highlightedMesh && originalMaterial) {
                highlightedMesh.material = originalMaterial;
                highlightedMesh = null;
                originalMaterial = null;
            }
        }

        /* --- Rotate Material Function --- */
        function rotateSelectedMaterial(angles) {
            if (!selectedMeshId || !meshes[selectedMeshId]) return;
            const mesh = meshes[selectedMeshId].mesh;

            if (angles.x) mesh.rotation.x += THREE.MathUtils.degToRad(angles.x);
            if (angles.y) mesh.rotation.y += THREE.MathUtils.degToRad(angles.y);
            if (angles.z) mesh.rotation.z += THREE.MathUtils.degToRad(angles.z);
        }

        /* --- UI EVENT HANDLERS --- */
        function setupUIHandlers() {
            // Remove selected element
            document.getElementById('remove-selected').addEventListener('click', function() {
                if (selectedMeshId && meshes[selectedMeshId]) {
                    const meshData = meshes[selectedMeshId];
                    removeMaterialFromScene(meshData.materialId, selectedMeshId);
                }
            });

            // Move button toggle
            const moveBtn = document.getElementById('enable-move');
            moveBtn.addEventListener('click', function() {
                isMoveMode = !isMoveMode;
                this.classList.toggle('active', isMoveMode);
                controls.enabled = !isMoveMode;
                
                if (!isMoveMode) {
                    // Exit move mode
                    isDragging = false;
                    activeMesh = null;
                }
                
                // Update button text
                this.innerHTML = isMoveMode ? 
                    '<i class="fa fa-arrows-alt"></i> Exit Move' : 
                    '<i class="fa fa-arrows-alt"></i> Move';
            });

            // Rotation apply button
            const rotateBtn = document.getElementById('apply-rotation');
            if (rotateBtn) {
                rotateBtn.addEventListener('click', function() {
                    if (selectedMeshId && meshes[selectedMeshId]) {
                        const angles = {
                            x: parseFloat(document.getElementById('rotate-x').value) || 0,
                            y: parseFloat(document.getElementById('rotate-y').value) || 0,
                            z: parseFloat(document.getElementById('rotate-z').value) || 0
                        };
                        rotateSelectedMaterial(angles);
                    }
                });
            }

            // Save configuration button
            document.getElementById('saveConfigBtn').addEventListener('click', function() {
                saveConfiguration();
            });
        }

        /* --- SAVE CONFIGURATION --- */
        function saveConfiguration() {
            const config = {
                roofType: roofType,
                roofPitch: roofPitch,
                roofWidth: roofWidth,
                roofLength: roofLength,
                material: currentMaterial,
                materials: bill,
                meshes: Object.keys(meshes).map(id => {
                    const mesh = meshes[id];
                    return {
                        materialId: mesh.materialId,
                        position: mesh.mesh.position.toArray(),
                        rotation: mesh.mesh.rotation.toArray(),
                        scale: mesh.mesh.scale ? mesh.mesh.scale.toArray() : [1,1,1]
                    };
                })
            };

            // Send to server
            fetch('../includes/save_configuration.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(config)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', 'Configuration saved successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to save configuration', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Failed to save configuration', 'error');
            });
        }

        /* --- INIT --- */
        document.addEventListener('DOMContentLoaded', function() {
            renderMaterialsSidebar();
            setupMaterialSwatches();
            setupRoofControls();
            setupUIHandlers();
            
            // Initialize 3D (no delay necessary)
            init3D();
            
            // Make remove function globally available
            window.removeMaterialFromScene = removeMaterialFromScene;
        });

        function onResize() {
            const container = document.getElementById('configurator-viewer');
            camera.aspect = container.clientWidth / container.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(container.clientWidth, container.clientHeight);
            labelRenderer.setSize(container.clientWidth, container.clientHeight);
        }
    </script>
</body>
</html>
