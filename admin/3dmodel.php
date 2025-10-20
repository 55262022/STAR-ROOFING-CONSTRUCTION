<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>3D Editor — Star Roofing</title>

  <!-- Meta -->
  <meta name="viewport" content="width=device-width,initial-scale=1" />

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Page styles (scoped) -->
  <style>
    :root {
      --bg: #f5f7fb;
      --panel: #ffffff;
      --muted: #6b7280;
      --accent: #0d6efd;
    }
    body {
      font-family: 'Montserrat', sans-serif;
      background: var(--bg);
      color: #111827;
      margin: 0;
      -webkit-font-smoothing: antialiased;
    }

    .app-shell {
      min-height: 100vh;
      display: flex;
      gap: 1.25rem;
      padding: 1.25rem;
    }

    /* Left column - tools */
    .left-col {
      width: 300px;
      max-width: 30%;
      min-width: 260px;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    /* Center canvas */
    .canvas-col {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    /* Right properties */
    .right-col {
      width: 320px;
      max-width: 32%;
      min-width: 280px;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .card-panel {
      background: var(--panel);
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(15,23,42,0.06);
      padding: 1rem;
    }

    #threeViewport {
      height: 72vh;
      border-radius: 10px;
      overflow: hidden;
      background: linear-gradient(180deg,#e9eef6 0,#cfdaf0 100%);
      border: 1px solid rgba(15,23,42,0.04);
    }

    .upload-drop {
      border: 2px dashed rgba(13,110,253,0.18);
      border-radius: 8px;
      padding: 14px;
      display:flex;
      align-items:center;
      gap:12px;
      cursor:pointer;
      transition: all .15s;
      background: linear-gradient(180deg, rgba(13,110,253,0.03), rgba(13,110,253,0.01));
    }
    .upload-drop.dragover {
      background: rgba(13,110,253,0.06);
      transform: translateY(-2px);
      border-color: rgba(13,110,253,0.6);
    }

    .muted { color: var(--muted); font-size: .95rem; }

    .small { font-size:.88rem; }

    .prop-row { display:flex; gap:.5rem; align-items:center; }

    .status-bar {
      display:flex; gap:1rem; align-items:center; justify-content:space-between;
      padding:.5rem 1rem; background: #fff; border-radius:8px; box-shadow: 0 4px 10px rgba(2,6,23,0.04);
    }

    /* responsive */
    @media (max-width: 1100px) {
      .app-shell { flex-direction: column; padding: .75rem; }
      .left-col, .right-col { width: 100%; max-width: none; min-width: auto; }
      #threeViewport { height: 56vh; }
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between py-3">
    <h3 class="mb-0">3D Model Editor</h3>
    <div>
      <a href="dashboard.php" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
  </div>

  <div class="app-shell">
    <!-- LEFT -->
    <aside class="left-col">
      <div class="card-panel">
        <h6 class="mb-2">Import</h6>

        <div id="uploadZone" class="upload-drop" tabindex="0">
          <div>
            <i class="fa fa-cloud-upload fa-2x text-primary"></i>
          </div>
          <div>
            <div class="small fw-semibold">Drag & drop images</div>
            <div class="muted small">Or click to select (jpg/png) — 3+ images recommended</div>
          </div>
        </div>

        <input type="file" id="fileInput" accept="image/*" multiple class="d-none">

        <div class="d-grid gap-2 mt-3">
          <button id="generateBtn" class="btn btn-primary btn-lg"><i class="fa fa-gear me-2"></i>Generate 3D Model</button>
          <button id="importGLBBtn" class="btn btn-outline-secondary"><i class="fa fa-file-import me-2"></i>Import .glb / .gltf</button>
          <button id="downloadBtn" class="btn btn-success"><i class="fa fa-download me-2"></i>Export .gltf</button>
        </div>

        <hr class="my-3">

        <div class="small muted">
          <strong>Tips:</strong>
          <ul class="small mb-0">
            <li>Use clear photos from different angles.</li>
            <li>Prefer high-res images (avoid heavy compression).</li>
            <li>Meshy requires a subscription for task creation.</li>
          </ul>
        </div>
      </div>

      <div class="card-panel mt-2">
        <h6 class="mb-2">Tools</h6>
        <div class="d-flex gap-2">
          <button id="selectTool" class="btn btn-outline-secondary btn-sm"><i class="fa fa-mouse-pointer"></i> Select</button>
          <button id="moveTool" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrows"></i> Move</button>
          <button id="rotateTool" class="btn btn-outline-secondary btn-sm"><i class="fa fa-rotate"></i> Rotate</button>
          <button id="scaleTool" class="btn btn-outline-secondary btn-sm"><i class="fa fa-expand"></i> Scale</button>
          <button id="wireframeBtn" class="btn btn-outline-secondary btn-sm"><i class="fa fa-border-all"></i> Wire</button>
        </div>
      </div>
    </aside>

    <!-- CENTER -->
    <main class="canvas-col">
      <div class="card-panel">
        <div id="threeViewport"></div>
      </div>

      <div class="status-bar mt-1">
        <div>
          <span id="statusText" class="muted small">Ready</span>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <span id="modelInfo" class="small muted"></span>
          <button id="resetCameraBtn" class="btn btn-sm btn-outline-primary">Reset View</button>
        </div>
      </div>
    </main>

    <!-- RIGHT -->
    <aside class="right-col">
      <div class="card-panel">
        <h6>Transform</h6>

        <label class="small">Scale <span id="scaleVal" class="ms-2 small muted">1.00</span></label>
        <input id="scaleRange" type="range" min="0.05" max="3" step="0.01" value="1" class="form-range">

        <div class="row g-2 mt-2">
          <div class="col-6">
            <label class="small">Rotate X</label>
            <input id="rotateX" type="range" min="0" max="360" step="1" value="0" class="form-range">
          </div>
          <div class="col-6">
            <label class="small">Rotate Y</label>
            <input id="rotateY" type="range" min="0" max="360" step="1" value="0" class="form-range">
          </div>
          <div class="col-12 mt-2">
            <label class="small">Rotate Z</label>
            <input id="rotateZ" type="range" min="0" max="360" step="1" value="0" class="form-range">
          </div>
        </div>
      </div>

      <div class="card-panel">
        <h6>Material</h6>
        <label class="small">Color</label>
        <input id="colorPicker" type="color" class="form-control form-control-color p-1 mb-2">

        <label class="small">Metalness <span id="metalVal" class="ms-2 small muted">0.50</span></label>
        <input id="metalness" type="range" min="0" max="1" step="0.01" value="0.5" class="form-range">

        <label class="small mt-2">Roughness <span id="roughVal" class="ms-2 small muted">0.50</span></label>
        <input id="roughness" type="range" min="0" max="1" step="0.01" value="0.5" class="form-range">

        <label class="small mt-2">Texture</label>
        <input id="textureUpload" type="file" accept="image/*" class="form-control form-control-sm">
      </div>

      <div class="card-panel">
        <h6>Console</h6>
        <div id="consoleLog" style="height:160px; overflow:auto; background:#f8fafc; border-radius:6px; padding:8px; font-family:monospace; font-size:.85rem; color:#111;">
          <div id="consoleEmpty" class="muted small">No logs yet.</div>
        </div>
      </div>
    </aside>
  </div>
</div>
<!-- Three.js (ESM) and utils (module import) -->
<script type="module">
  import * as THREE from "https://cdn.jsdelivr.net/npm/three@0.152.0/build/three.module.js";
  import { GLTFLoader } from "https://cdn.jsdelivr.net/npm/three@0.152.0/examples/jsm/loaders/GLTFLoader.js";
  import { OrbitControls } from "https://cdn.jsdelivr.net/npm/three@0.152.0/examples/jsm/controls/OrbitControls.js";
  import { GLTFExporter } from "https://cdn.jsdelivr.net/npm/three@0.152.0/examples/jsm/exporters/GLTFExporter.js";

  // Utilities
  const $ = selector => document.querySelector(selector);
  const log = (msg) => {
    const c = document.getElementById('consoleLog');
    document.getElementById('consoleEmpty')?.remove();
    const div = document.createElement('div');
    div.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
    c.prepend(div);
  };

  // Elements
  const uploadZone = $('#uploadZone');
  const fileInput = $('#fileInput');
  const generateBtn = $('#generateBtn');
  const downloadBtn = $('#downloadBtn');
  const importGLBBtn = $('#importGLBBtn');
  const statusText = $('#statusText');
  const modelInfo = $('#modelInfo');

  // Three.js scene variables
  let scene, camera, renderer, controls, loadedModel = null, textureLoader;

  function updateStatus(txt, isError=false){
    statusText.textContent = txt;
    statusText.style.color = isError ? '#d6333f' : '';
  }

  function initThree() {
    const container = document.getElementById('threeViewport');
    // clear old canvas if present
    container.innerHTML = '';

    scene = new THREE.Scene();
    scene.background = new THREE.Color(0xeaeef6);

    const w = container.clientWidth;
    const h = container.clientHeight;
    camera = new THREE.PerspectiveCamera(60, w / h, 0.1, 1000);
    camera.position.set(2.5, 2.0, 3.5);

    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: false });
    renderer.setPixelRatio(window.devicePixelRatio || 1);
    renderer.setSize(w, h);
    renderer.outputEncoding = THREE.sRGBEncoding;
    container.appendChild(renderer.domElement);

    // lights
    const dir = new THREE.DirectionalLight(0xffffff, 0.9);
    dir.position.set(5, 10, 7.5);
    scene.add(dir);
    scene.add(new THREE.AmbientLight(0xffffff, 0.55));

    // grid + ground
    const grid = new THREE.GridHelper(10, 20, 0xbfc9df, 0xe9eef6);
    scene.add(grid);

    // orbit controls
    controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;

    textureLoader = new THREE.TextureLoader();

    window.addEventListener('resize', onWindowResize);
    animate();
  }

  function onWindowResize(){
    const container = document.getElementById('threeViewport');
    if(!container) return;
    const w = container.clientWidth;
    const h = container.clientHeight;
    camera.aspect = w/h;
    camera.updateProjectionMatrix();
    renderer.setSize(w,h);
  }

  function animate(){
    requestAnimationFrame(animate);
    controls && controls.update();
    renderer && renderer.render(scene,camera);
  }

  function clearModel(){
    if(!scene) return;
    if(loadedModel){
      scene.remove(loadedModel);
      loadedModel.traverse && loadedModel.traverse(child => { if(child.geometry) child.geometry.dispose && child.geometry.dispose(); if(child.material) { 
        if(Array.isArray(child.material)) child.material.forEach(m => m.dispose && m.dispose()); else child.material.dispose && child.material.dispose();
      }});
      loadedModel = null;
      modelInfo.textContent = '';
    }
  }

  async function loadModelFromUrl(url){
    updateStatus('Loading model...');
    log('Loading model from: ' + url);
    try {
      clearModel();
      const loader = new GLTFLoader();
      await new Promise((resolve, reject) => {
        loader.load(url, gltf => {
          loadedModel = gltf.scene;
          // center model
          const box = new THREE.Box3().setFromObject(loadedModel);
          const center = box.getCenter(new THREE.Vector3());
          loadedModel.position.sub(center); // center to origin
          scene.add(loadedModel);
          modelInfo.textContent = `Model: ${Math.round(box.getSize(new THREE.Vector3()).x*100)/100} x ${Math.round(box.getSize(new THREE.Vector3()).y*100)/100} x ${Math.round(box.getSize(new THREE.Vector3()).z*100)/100} units`;
          updateStatus('Model loaded');
          resolve();
        }, xhr => {
          // progress (optional)
        }, err => {
          reject(err);
        });
      });
    } catch (err) {
      console.error(err);
      log('Error loading model: ' + (err.message || err));
      updateStatus('Failed to load model', true);
      Swal.fire('Error','Unable to load model. Check console for details.','error');
    }
  }

  // EXPORT as .gltf (JSON)
  function exportGLTF(){
    if(!loadedModel){
      Swal.fire('No model','Load or generate a model first','info');
      return;
    }
    updateStatus('Exporting glTF...');
    const exporter = new GLTFExporter();
    exporter.parse(loadedModel, result => {
      let output;
      if (result instanceof ArrayBuffer) {
        output = result;
      } else {
        output = JSON.stringify(result, null, 2);
      }
      const blob = new Blob([output], {type: result instanceof ArrayBuffer ? 'model/gltf-binary' : 'model/gltf+json'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'model.gltf';
      document.body.appendChild(a);
      a.click();
      a.remove();
      updateStatus('Export complete');
      log('GLTF exported');
    }, { binary: false });
  }

  // Apply material/color changes
  function applyMaterialSettings(){
    if(!loadedModel) return;
    const color = $('#colorPicker').value;
    const metal = parseFloat($('#metalness').value);
    const rough = parseFloat($('#roughness').value);
    loadedModel.traverse(c => {
      if(c.isMesh){
        if(c.material){
          // ensure PBR material
          if(!('metalness' in c.material)){
            c.material = new THREE.MeshStandardMaterial({ map: c.material.map, color: c.material.color || new THREE.Color(color) });
          }
          try {
            c.material.color.set(color);
            c.material.metalness = metal;
            c.material.roughness = rough;
            c.material.needsUpdate = true;
          } catch(e){ /* ignore */ }
        }
      }
    });
  }

  // Hook up UI elements
  function hookUI(){
    // drag/drop
    uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
    ['dragleave','dragend'].forEach(ev => uploadZone.addEventListener(ev, e => { uploadZone.classList.remove('dragover'); }));
    uploadZone.addEventListener('drop', e => {
      e.preventDefault();
      uploadZone.classList.remove('dragover');
      const files = Array.from(e.dataTransfer.files || []);
      handleFiles(files);
    });

    uploadZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', e => handleFiles(Array.from(e.target.files)));

    // import local .glb
    importGLBBtn.addEventListener('click', () => {
      const input = document.createElement('input');
      input.type = 'file'; input.accept = '.gltf,.glb'; input.onchange = (ev) => {
        const f = ev.target.files[0];
        if(!f) return;
        const url = URL.createObjectURL(f);
        loadModelFromUrl(url);
      };
      input.click();
    });

    // generate (meshy upload)
    generateBtn.addEventListener('click', async () => {
      // If the user already loaded local files stored in uploadFiles, use that
      if(!window.uploadFiles || window.uploadFiles.length === 0){
        Swal.fire('No images','Add at least one image to generate','warning');
        return;
      }
      updateStatus('Uploading images to server...');
      log('Uploading ' + window.uploadFiles.length + ' images to server');
      const fd = new FormData();
      for(const f of window.uploadFiles) fd.append('images[]', f);
      try {
        const resp = await fetch('meshy/meshy_upload.php', { method: 'POST', body: fd });
        const text = await resp.text();
        log('Raw response: ' + text);
        let json;
        try { json = JSON.parse(text); } catch(e) {
          updateStatus('Invalid response from API', true);
          Swal.fire('API Error','Server returned invalid JSON. Check logs.','error');
          return;
        }
        if(json.status === 'success' && json.model_url){
          await loadModelFromUrl(json.model_url);
        } else {
          const msg = json.message || 'Unexpected response from Meshy API';
          updateStatus('API: ' + msg, true);
          Swal.fire('Meshy Error', msg, 'error');
        }
      } catch (err) {
        console.error(err);
        log('Network or server error: ' + (err.message||err));
        updateStatus('Upload failed', true);
        Swal.fire('Error','Upload failed. See console.','error');
      }
    });

    // export
    downloadBtn.addEventListener('click', exportGLTF);

    // transform controls
    $('#scaleRange').addEventListener('input', e => {
      $('#scaleVal').textContent = parseFloat(e.target.value).toFixed(2);
      if(loadedModel) loadedModel.scale.setScalar(parseFloat(e.target.value));
    });
    ['rotateX','rotateY','rotateZ'].forEach(id => {
      $('#' + id).addEventListener('input', e => {
        if(!loadedModel) return;
        const v = THREE.MathUtils.degToRad(parseFloat(e.target.value));
        if(id === 'rotateX') loadedModel.rotation.x = v;
        if(id === 'rotateY') loadedModel.rotation.y = v;
        if(id === 'rotateZ') loadedModel.rotation.z = v;
      });
    });

    // material controls
    $('#colorPicker').addEventListener('input', applyMaterialSettings);
    $('#metalness').addEventListener('input', e => { $('#metalVal').textContent = parseFloat(e.target.value).toFixed(2); applyMaterialSettings(); });
    $('#roughness').addEventListener('input', e => { $('#roughVal').textContent = parseFloat(e.target.value).toFixed(2); applyMaterialSettings(); });

    // texture
    $('#textureUpload').addEventListener('change', e => {
      const f = e.target.files[0];
      if(!f || !loadedModel) return;
      const url = URL.createObjectURL(f);
      const tex = new THREE.TextureLoader().load(url, () => {
        loadedModel.traverse(c => {
          if(c.isMesh && c.material){
            c.material.map = tex;
            c.material.needsUpdate = true;
          }
        });
        log('Texture applied');
        URL.revokeObjectURL(url);
      });
    });

    // wireframe toggle
    $('#wireframeBtn').addEventListener('click', () => {
      if(!loadedModel) return;
      let current = false;
      loadedModel.traverse(c => { if(c.isMesh && c.material){ current = !!c.material.wireframe; } });
      loadedModel.traverse(c => { if(c.isMesh && c.material){ c.material.wireframe = !current; c.material.needsUpdate = true; } });
    });

    // reset view
    $('#resetCameraBtn').addEventListener('click', () => {
      if(!camera || !controls) return;
      camera.position.set(2.5,2.0,3.5);
      controls.target.set(0,0,0);
      controls.update();
    });
  }

  // file handling store
  window.uploadFiles = [];

  function handleFiles(files){
    // Accept only images
    const accepted = files.filter(f => f.type && f.type.startsWith('image/'));
    if(accepted.length === 0){
      Swal.fire('Invalid files','Please select image files only (jpg/png).','warning');
      return;
    }
    // store
    window.uploadFiles = window.uploadFiles.concat(accepted);
    updateStatus(`${window.uploadFiles.length} image(s) ready`);
    log('Added ' + accepted.length + ' images. Total: ' + window.uploadFiles.length);
  }

  // initialize everything
  initThree();
  hookUI();
  log('Editor ready');
  updateStatus('Ready');

</script>

<!-- Bootstrap JS (non-module) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>