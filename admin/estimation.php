<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Roof Estimation</title>
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Three.js -->
  <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/build/three.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/examples/js/controls/OrbitControls.js"></script>
  <!-- CSS -->
  <link rel="stylesheet" href="../css/admin_main.css">
  <style>
    #threeContainer {
      width: 100%;
      height: 400px;
      background: #e9ecef;
      border-radius: 10px;
      margin-top: 20px;
    }
  </style>
</head>

<body class="bg-light">
<div class="main-container">

  <div class="main-content">

    <div class="container py-5">
      <h2 class="text-center mb-4">Roof Estimation Tool</h2>

      <div class="card shadow p-4">
        <form id="estimationForm">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="length" class="form-label">Length (m)</label>
              <input type="number" class="form-control" id="length" name="length" step="0.01" required>
            </div>
            <div class="col-md-6">
              <label for="width" class="form-label">Width (m)</label>
              <input type="number" class="form-control" id="width" name="width" step="0.01" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="roofType" class="form-label">Roof Type</label>
            <select id="roofType" name="roofType" class="form-select" required>
              <option value="">Select Roof Type</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="material" class="form-label">Material</label>
            <select id="material" name="material" class="form-select" required>
              <option value="">Select Material</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary w-100">Estimate</button>
        </form>
      </div>

      <!-- Results -->
      <div id="resultContainer" class="mt-4" style="display:none;">
        <div class="card shadow p-4">
          <h4>Estimation Result</h4>
          <p><strong>Total Area:</strong> <span id="totalArea"></span> sqm</p>
          <p><strong>Steel Required:</strong> <span id="steelReq"></span> kg</p>
          <p><strong>Screw Required:</strong> <span id="screwReq"></span> pcs</p>
          <p><strong>Paint Required:</strong> <span id="paintReq"></span> L</p>
          <hr>
          <p><strong>Material:</strong> <span id="materialName"></span></p>
          <p><strong>Material Price:</strong> ₱<span id="materialPrice"></span> / <span id="materialUnit"></span></p>
          <h3 class="text-success mt-3">Total Estimated Cost: ₱<span id="totalCost"></span></h3>
        </div>
      </div>

      <!-- 3D Viewer -->
      <div id="threeContainer"></div>
    </div>
  </div>
</div>

<script>
let scene, camera, renderer, controls, roofMesh;

function initThreeJS() {
  scene = new THREE.Scene();
  camera = new THREE.PerspectiveCamera(60, window.innerWidth / 400, 0.1, 1000);
  camera.position.set(5, 5, 5);

  renderer = new THREE.WebGLRenderer({ antialias: true });
  renderer.setSize(window.innerWidth, 400);
  document.getElementById('threeContainer').appendChild(renderer.domElement);

  controls = new THREE.OrbitControls(camera, renderer.domElement);

  const light = new THREE.DirectionalLight(0xffffff, 1);
  light.position.set(10, 10, 10);
  scene.add(light);
  scene.add(new THREE.AmbientLight(0x888888));

  const grid = new THREE.GridHelper(10, 10);
  scene.add(grid);

  animate();
}

function animate() {
  requestAnimationFrame(animate);
  controls.update();
  renderer.render(scene, camera);
}

function createRoof(type, length, width) {
  if (roofMesh) scene.remove(roofMesh);
  const material = new THREE.MeshPhongMaterial({ color: 0x7d5a50 });
  let geometry;

  switch (type.toLowerCase()) {
    case 'gable roof':
      geometry = new THREE.ConeGeometry(Math.max(length, width) / 2, Math.min(length, width), 4);
      break;
    case 'hip roof':
      geometry = new THREE.ConeGeometry(Math.max(length, width) / 2, Math.min(length, width), 8);
      break;
    case 'mansard roof':
      geometry = new THREE.CylinderGeometry(0, Math.max(length, width) / 2, Math.min(length, width), 6);
      break;
    case 'flat roof':
      geometry = new THREE.BoxGeometry(length, 0.2, width);
      break;
    case 'gambrel roof':
      geometry = new THREE.ConeGeometry(Math.max(length, width) / 2, Math.min(length, width), 6);
      break;
    case 'butterfly roof':
      geometry = new THREE.PlaneGeometry(length, width);
      geometry.rotateX(-Math.PI / 4);
      break;
    case 'skillion roof':
      geometry = new THREE.BoxGeometry(length, 0.2, width);
      geometry.rotateX(-Math.PI / 12);
      break;
    case 'a-frame roof':
      geometry = new THREE.ConeGeometry(Math.max(length, width) / 2, Math.min(length, width), 3);
      break;
    case 'saltbox roof':
      geometry = new THREE.BoxGeometry(length, 0.2, width);
      geometry.rotateX(-Math.PI / 8);
      break;
    case 'sawtooth roof':
      geometry = new THREE.BoxGeometry(length, 0.2, width);
      geometry.rotateY(Math.PI / 8);
      break;
    case 'dutch gable roof':
      geometry = new THREE.ConeGeometry(Math.max(length, width) / 2, Math.min(length, width), 5);
      break;
    case 'hexagonal roof':
      geometry = new THREE.ConeGeometry(Math.max(length, width) / 2, Math.min(length, width), 6);
      break;
    case 'conical roof':
      geometry = new THREE.ConeGeometry(Math.max(length, width) / 2, Math.min(length, width), 32);
      break;
    default:
      geometry = new THREE.BoxGeometry(length, 0.2, width);
  }

  roofMesh = new THREE.Mesh(geometry, material);
  scene.add(roofMesh);
}

$(document).ready(function() {
  initThreeJS();

  // Load roof types
  $.getJSON('crud/get_roof_type.php', function(response) {
    if (response.status === 'success') {
      let options = '<option value="" disabled selected>Select Roof Type</option>';
      response.data.forEach(type => {
        options += `<option value="${type.type_name}" 
                      data-steel="${type.steel_per_sqm}" 
                      data-screw="${type.screw_per_sqm}" 
                      data-paint="${type.paint_per_sqm}">
                      ${type.type_name}
                    </option>`;
      });
      $('#roofType').html(options);
    }
  });

  // Load materials (inventory)
  $.getJSON('crud/get_materials.php', function(response) {
    if (response.status === 'success') {
      let options = '<option value="" disabled selected>Select Material</option>';
      response.data.forEach(material => {
        options += `<option value="${material.product_id}" 
                      data-price="${material.price}" 
                      data-unit="${material.unit}">
                      ${material.name} - ₱${material.price}/${material.unit}
                    </option>`;
      });
      $('#material').html(options);
    }
  });

  // Handle estimation
  $('#estimationForm').on('submit', function(e) {
    e.preventDefault();

    const length = parseFloat($('#length').val());
    const width = parseFloat($('#width').val());
    const selectedRoof = $('#roofType option:selected');
    const selectedMaterial = $('#material option:selected');

    if (!selectedRoof.val() || !selectedMaterial.val()) {
      Swal.fire('Incomplete Input', 'Please select both roof type and material.', 'warning');
      return;
    }

    const area = length * width;
    const steelPerSqm = parseFloat(selectedRoof.data('steel'));
    const screwPerSqm = parseFloat(selectedRoof.data('screw'));
    const paintPerSqm = parseFloat(selectedRoof.data('paint'));

    const materialPrice = parseFloat(selectedMaterial.data('price'));
    const materialUnit = selectedMaterial.data('unit');
    const materialName = selectedMaterial.text();

    const totalSteel = (steelPerSqm * area).toFixed(2);
    const totalScrew = (screwPerSqm * area).toFixed(2);
    const totalPaint = (paintPerSqm * area).toFixed(2);
    const totalCost = (materialPrice * area).toFixed(2);

    $('#totalArea').text(area.toFixed(2));
    $('#steelReq').text(totalSteel);
    $('#screwReq').text(totalScrew);
    $('#paintReq').text(totalPaint);
    $('#materialName').text(materialName);
    $('#materialPrice').text(materialPrice);
    $('#materialUnit').text(materialUnit);
    $('#totalCost').text(totalCost);

    $('#resultContainer').slideDown();
    createRoof(selectedRoof.val(), length, width);
  });
});
</script>
</body>
</html>
