let transfersList = [];
let itemCounter = 1;

// Función para agregar transferencia
document
  .getElementById("transferForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = {
      id: itemCounter++,
      numeroArticulo: document.getElementById("numeroArticulo").value,
      codigoBarras: document.getElementById("codigoBarras").value,
      descripcion: document.getElementById("descripcion").value,
      cantidad: parseInt(document.getElementById("cantidad").value),
      deUbicacion: document.getElementById("deUbicacion").value,
      aUbicacion: document.getElementById("aUbicacion").value,
    };

    // Validar que las ubicaciones sean diferentes
    if (formData.deUbicacion === formData.aUbicacion) {
      alert("La ubicación de origen y destino no pueden ser iguales");
      return;
    }

    transfersList.push(formData);
    updateTable();
    clearForm();

    // Mostrar mensaje de éxito
    showSuccessMessage("Artículo agregado correctamente");
  });

// Función para actualizar la tabla
function updateTable() {
  const tbody = document.getElementById("transfersTableBody");

  if (transfersList.length === 0) {
    tbody.innerHTML = `
                    <tr class="empty-state">
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">No hay transferencias pendientes</p>
                            <small class="text-muted">Agregue artículos usando el formulario</small>
                        </td>
                    </tr>
                `;
    document.getElementById("btnProcess").disabled = true;
  } else {
    tbody.innerHTML = transfersList
      .map(
        (item, index) => `
                    <tr>
                        <td><span class="badge bg-secondary">${index + 1
          }</span></td>
                        <td><strong>${item.numeroArticulo}</strong></td>
                        <td><code>${item.codigoBarras}</code></td>
                        <td>${item.descripcion}</td>
                        <td><span class="badge bg-info">${item.cantidad
          }</span></td>
                        <td><small class="text-muted">${item.deUbicacion
          }</small></td>
                        <td><small class="text-success">${item.aUbicacion
          }</small></td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${item.id
          })" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `
      )
      .join("");
    document.getElementById("btnProcess").disabled = false;
  }

  updateCounters();
}

// Función para actualizar contadores
function updateCounters() {
  document.getElementById("totalItems").textContent = transfersList.length;
  document.getElementById("totalQuantity").textContent = transfersList.reduce(
    (sum, item) => sum + item.cantidad,
    0
  );
}

// Función para eliminar item
function removeItem(id) {
  transfersList = transfersList.filter((item) => item.id !== id);
  updateTable();
  showSuccessMessage("Artículo eliminado de la lista");
}

// Función para limpiar formulario
function clearForm() {
  document.getElementById("transferForm").reset();
}

// Función para limpiar tabla
function clearTable() {
  if (transfersList.length > 0) {
    if (confirm("¿Está seguro de que desea limpiar toda la lista?")) {
      transfersList = [];
      updateTable();
      showSuccessMessage("Lista limpiada correctamente");
    }
  }
}

// Función para procesar transferencias
function processTransfers() {
  if (transfersList.length === 0) return;

  if (confirm(`¿Procesar ${transfersList.length} transferencias?`)) {
    // Aquí iría la lógica para enviar al servidor
    console.log("Procesando transferencias:", transfersList);

    // Simular procesamiento
    showSuccessMessage(
      `${transfersList.length} transferencias procesadas correctamente`
    );
    transfersList = [];
    updateTable();
  }
}

// Función para mostrar mensajes
function showSuccessMessage(message) {
  // Crear toast o alerta temporal
  const alertDiv = document.createElement("div");
  alertDiv.className =
    "alert alert-success alert-dismissible fade show position-fixed";
  alertDiv.style.cssText =
    "top: 90px; right: 20px; z-index: 9999; min-width: 300px;";
  alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
  document.body.appendChild(alertDiv);

  // Auto eliminar después de 3 segundos
  setTimeout(() => {
    if (alertDiv.parentNode) {
      alertDiv.remove();
    }
  }, 3000);
}

// Función para cambiar sucursal
function changeSucursal() {
  const sucursalSelect = document.getElementById("sucursalSelect");
  const selectedSucursal = sucursalSelect.value;

  if (selectedSucursal) {
    // Actualizar las ubicaciones basadas en la sucursal seleccionada
    updateUbicaciones(selectedSucursal);

    // Mostrar mensaje de cambio
    const sucursalText =
      sucursalSelect.options[sucursalSelect.selectedIndex].text;
    showSuccessMessage(`Sucursal cambiada a: ${sucursalText}`);

    // Limpiar formulario si hay datos
    if (document.getElementById("numeroArticulo").value) {
      if (
        confirm("¿Desea limpiar el formulario actual al cambiar de sucursal?")
      ) {
        clearForm();
      }
    }
  }
}

// Función para actualizar ubicaciones según la sucursal
function updateUbicaciones(sucursal) {
  const deUbicacion = document.getElementById("deUbicacion");
  const aUbicacion = document.getElementById("aUbicacion");

  // Limpiar opciones actuales
  deUbicacion.innerHTML =
    '<option value="">Seleccionar ubicación origen</option>';
  aUbicacion.innerHTML =
    '<option value="">Seleccionar ubicación destino</option>';

  let ubicaciones = [];

  // Definir ubicaciones por sucursal
  switch (sucursal) {
    case "01": // Sucursal 01 - Principal
      ubicaciones = [
        { value: "45", text: "01-ALMACÉN NIVEL 2" },
        { value: "C-A02", text: "Centro - Almacén A - Estante 02" },
        { value: "C-A03", text: "Centro - Almacén A - Estante 03" },
        { value: "C-B01", text: "Centro - Almacén B - Estante 01" },
        { value: "C-B02", text: "Centro - Almacén B - Estante 02" },
      ];
      break;
    case "02": // Sucursal Almacén Al Por Mayor
      ubicaciones = [
        { value: "N-A01", text: "Norte - Almacén A - Estante 01" },
        { value: "N-A02", text: "Norte - Almacén A - Estante 02" },
        { value: "N-B01", text: "Norte - Almacén B - Estante 01" },
        { value: "N-C01", text: "Norte - Almacén C - Estante 01" },
      ];
      break;
    case "03": // Surcursal Villa Hermosa
      ubicaciones = [
        { value: "S-A01", text: "Sur - Almacén A - Estante 01" },
        { value: "S-A02", text: "Sur - Almacén A - Estante 02" },
        { value: "S-B01", text: "Sur - Almacén B - Estante 01" },
      ];
      break;
  }

  // Agregar las opciones a ambos selects
  ubicaciones.forEach((ubicacion) => {
    const optionDe = new Option(ubicacion.text, ubicacion.value);
    const optionA = new Option(ubicacion.text, ubicacion.value);
    deUbicacion.add(optionDe);
    aUbicacion.add(optionA);
  });
}

// Validar sucursal al enviar formulario
document
  .getElementById("transferForm")
  .addEventListener("submit", function (e) {
    const sucursalSelect = document.getElementById("sucursalSelect");
    if (!sucursalSelect.value) {
      e.preventDefault();
      alert(
        "Por favor seleccione una sucursal antes de agregar transferencias"
      );
      sucursalSelect.focus();
      return;
    }
  });
