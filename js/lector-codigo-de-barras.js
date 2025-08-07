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
            width: 480,
            height: 320,
            facingMode: "environment", // 'environment' para la cámara trasera
          },
        },
        decoder: {
          readers: [
            "code_128_reader",
            "ean_reader",
            "ean_8_reader",
            "code_39_reader",
            "codabar_reader",
            "upc_reader",
            "upc_e_reader",
          ],
        },
      },
      function (err) {
        if (err) {
          console.error("Error al inicializar Quagga:", err);
          // Informar al usuario que no se pudo acceder a la cámara
          alert("Error: No se pudo iniciar la cámara. Verifique los permisos.");
          return;
        }
        console.log("Inicialización de Quagga finalizada. Listo para escanear.");
        Quagga.start();
      }
    );

    Quagga.onProcessed(function (result) {
      const drawingCtx = Quagga.canvas.ctx.overlay;
      const drawingCanvas = Quagga.canvas.dom.overlay;

      if (result) {
        // Dibuja el cuadro delimitador
        if (result.boxes) {
          drawingCtx.clearRect(
            0,
            0,
            parseInt(drawingCanvas.getAttribute("width")),
            parseInt(drawingCanvas.getAttribute("height"))
          );
          result.boxes
            .filter(box => box !== result.box)
            .forEach(box => {
              Quagga.ImageDebug.drawPath(box, { x: 0, y: 1 }, drawingCtx, {
                color: "green",
                lineWidth: 2,
              });
            });
        }

        if (result.box) {
          Quagga.ImageDebug.drawPath(result.box, { x: 0, y: 1 }, drawingCtx, {
            color: "#00F",
            lineWidth: 2,
          });
        }

        if (result.codeResult && result.codeResult.code) {
          Quagga.ImageDebug.drawPath(
            result.line,
            { x: "x", y: "y" },
            drawingCtx,
            { color: "red", lineWidth: 3 }
          );
        }
      }
    });

    Quagga.onDetected(function (result) {
      console.log(
        "Código de barras detectado y procesado: [" +
          result.codeResult.code +
          "]",
        result
      );

      // Asigna el código de barras al campo de entrada
      if (result.codeResult.code && codigoBarrasInput) {
        codigoBarrasInput.value = result.codeResult.code;
        
        // Opcional: disparar un evento de cambio si otra lógica depende de ello
        const event = new Event('input', { bubbles: true });
        codigoBarrasInput.dispatchEvent(event);
      }

      // Detiene el escáner y oculta el contenedor
      Quagga.stop();
      scannerContainer.style.display = "none";
    });
  }
});