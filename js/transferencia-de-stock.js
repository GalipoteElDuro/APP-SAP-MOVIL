document.addEventListener('DOMContentLoaded', function() {
    const codigoBarrasInput = document.getElementById('codigoBarras');
    if(codigoBarrasInput) {
        codigoBarrasInput.addEventListener('blur', handleBarcodeBlur);
    }
});

async function handleBarcodeBlur(event) {
    const barcode = event.target.value;
    const warehouse = document.getElementById('sucursalSelect').value;

    if (!barcode) return; // No hacer nada si el campo está vacío

    if (!warehouse) {
        showErrorMessage('Por favor, seleccione una sucursal primero.');
        return;
    }

    // Mostrar un indicador de carga
    const originalPlaceholder = event.target.placeholder;
    event.target.placeholder = 'Buscando...';
    event.target.disabled = true;

    try {
        const response = await fetch(`Conexiones/lookup-item.php?barcode=${barcode}&warehouse=${warehouse}`);
        const data = await response.json();

        if (data.success) {
            document.getElementById('numeroArticulo').value = data.itemCode;
            document.getElementById('descripcion').value = data.description;
            document.getElementById('en-stock').value = data.stock;
            showSuccessMessage('Artículo encontrado y cargado.');
        } else {
            showErrorMessage(data.message || 'Artículo no encontrado.');
            // Limpiar campos si no se encuentra
            document.getElementById('numeroArticulo').value = '';
            document.getElementById('descripcion').value = '';
            document.getElementById('en-stock').value = '';
        }
    } catch (error) {
        showErrorMessage('Error de red al buscar el artículo.');
    } finally {
        // Restaurar el campo de código de barras
        event.target.placeholder = originalPlaceholder;
        event.target.disabled = false;
    }
}

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
async function processTransfers() {
  if (transfersList.length === 0) {
    showErrorMessage("No hay transferencias en la lista para procesar.");
    return;
  }

  const warehouseCode = document.getElementById("sucursalSelect").value;
  if (!warehouseCode) {
    showErrorMessage("Por favor, seleccione una sucursal activa primero.");
    return;
  }

  // Mapear la lista de transferencias al formato requerido por el backend
  const transferLines = transfersList.map(item => ({
    ItemCode: item.numeroArticulo,
    ItemDescription: item.descripcion, // Enviar también la descripción
    Quantity: item.cantidad,
    FromBinAbsEntry: item.deUbicacion, // ID de la ubicación origen
    ToBinAbsEntry: item.aUbicacion,     // ID de la ubicación destino
  }));

  const payload = {
    WarehouseCode: warehouseCode,
    Lines: transferLines,
  };

  // Deshabilitar botones para evitar doble envío
  const processButton = document.getElementById("btnProcess");
  processButton.disabled = true;
  processButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';

  try {
    const response = await fetch("transferencia-de-stock.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    const result = await response.json();

    if (result.success) {
      showSuccessMessage(result.message || "Transferencia procesada con éxito.");
      transfersList = [];
      updateTable();
    } else {
      showErrorMessage(result.message || "Ocurrió un error desconocido.");
    }
  } catch (error) {
    showErrorMessage("Error de red o al conectar con el servidor.");
  } finally {
    // Reactivar botón de procesar
    processButton.disabled = false;
    processButton.innerHTML = '<i class="bi bi-check-circle me-2"></i>Procesar Todo';
  }
}

// Función para mostrar mensajes de error (similar a la de éxito)
function showErrorMessage(message) {
    const alertDiv = document.createElement("div");
    alertDiv.className = "alert alert-danger alert-dismissible fade show position-fixed";
    alertDiv.style.cssText = "top: 90px; right: 20px; z-index: 9999; min-width: 300px;";
    alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000); // Dejar el mensaje de error un poco más de tiempo
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
        { value: "44", text: "01-ALMACÉN NIVEL 3" },
        { value: "6", text: "01-AVERIAS" },
        { value: "4", text: "01-CAMION" },
        { value: "27", text: "01-COSMETICOS" },
        { value: "25", text: "01-CUARTO FRIO" },
        { value: "26", text: "01-HOGAR" },
        { value: "29", text: "01-MAGALY" },
        { value: "22", text: "01-NO PERECEDEROS" },
        { value: "8", text: "01-PRODUCCION" },
        { value: "9", text: "01-PUNTO DE VENTA" },
        { value: "10", text: "01-RECEPCION" },
        { value: "28", text: "01-SOTANO" },
        { value: "31", text: "01-SUMINISTROS" },
      ];
      break;
    case "02": // Sucursal Almacén Al Por Mayor
      ubicaciones = [
        { value: "32", text: "02-ALMACEN AL POR MAYOR NIVEL 1" },
        { value: "35", text: "02-ALMACEN AL POR MAYOR NIVEL 2" },
        { value: "36", text: "02-ALMACÉN AL POR MAYOR NIVEL 3" },
        { value: "12", text: "02-AVERIAS" },
        { value: "13", text: "02-CAMION" },
        { value: "14", text: "02-PRODUCCION" },
        { value: "43", text: "02-PUNTO DE VENTA" },
        { value: "15", text: "02-RECEPCION" },
        { value: "33", text: "02-SUMINISTROS AL POR MAYOR" },
      ];
      break;
    case "03": // Surcursal Villa Hermosa
      ubicaciones = [
        { value: "16", text: "03-ALMACEN" },
        { value: "17", text: "03-AVERIAS" },
        { value: "46", text: "03-CAMION" },
        { value: "41", text: "03-COSMETICOS" },
        { value: "39", text: "03-CUARTO FRIO CARNES Y MARISCOS" },
        { value: "38", text: "03-CUARTO FRIO VEGETALES" },
        { value: "24", text: "03-NO PERECEDEROS" },
        { value: "18", text: "03-PRODUCCION" },
        { value: "19", text: "03-PUNTO DE VENTA" },
        { value: "21", text: "03-RECEPCION" },
        { value: "40", text: "03-SUMINISTROS" },
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
