document.addEventListener("DOMContentLoaded", function () {
  // Selecciona el botón de escanear por su clase más específica
  const scanButton = document.querySelector(".btn-scan");
  const scannerContainer = document.getElementById("scanner-container");
  const codigoBarrasInput = document.getElementById("codigoBarras");

  if (scanButton) {
    scanButton.addEventListener("click", function () {
      // Alterna la visibilidad del contenedor del escáner
      if (scannerContainer.style.display === "none") {
        scannerContainer.style.display = "block";
        startScanner();
      } else {
        scannerContainer.style.display = "none";
        if (Quagga) {
          Quagga.stop();
        }
      }
    });
  }

  function startScanner() {
    Quagga.init(
      {
        inputStream: {
          name: "Live",
          type: "LiveStream",
          target: document.querySelector("#interactive"),
          constraints: {
            facingMode: "environment", // Cámara trasera
            aspectRatio: { min: 1, max: 2 },
            width: { ideal: 1280 },
            height: { ideal: 720 }
          }
        },
        decoder: {
          readers: [
            "ean_reader",
            "ean_8_reader",
            "code_128_reader",
            "code_39_reader",
            "upc_reader",
            "upc_e_reader",
            "codabar_reader"
          ],
          locate: true
        },
        numOfWorkers: navigator.hardwareConcurrency || 4,
        locate: true
      },
      function (err) {
        if (err) {
          console.error("Error al inicializar Quagga:", err);
          alert("Error: No se pudo iniciar la cámara. Verifique permisos.");
          return;
        }
        console.log("Quagga listo para escanear.");
        Quagga.start();
      }
    );

    Quagga.onDetected(function (result) {
      if (result.codeResult && result.codeResult.code) {
        codigoBarrasInput.value = result.codeResult.code;
        codigoBarrasInput.dispatchEvent(new Event('input', { bubbles: true }));
        Quagga.stop();
        scannerContainer.style.display = "none";
      }
    });
  }
});