document.addEventListener("DOMContentLoaded", function () {
  // Selecciona el botón de escanear por su clase
  const scanButton = document.querySelector(".bi-upc-scan");
  const scannerContainer = document.getElementById("scanner-container");

  if (scanButton) {
    scanButton.addEventListener("click", function () {
      // Muestra el contenedor del escaner
      if (scannerContainer.style.display === "none") {
        scannerContainer.style.display = "block";
        startScanner();
      } else {
        scannerContainer.style.display = "none";
        Quagga.stop();
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
            facingMode: "environment", // "environment" para la cámara trasera, "user" para la frontal,
          },
        },
        decoder: {
          readers: [
            "code_128_reader",
            "ean_reader",
            "ean_8_reader",
            "code_39_reader",
            "code_39_vin_reader",
            "codabar_reader",
            "upc_reader",
            "upc_e_reader",
            "i2of5_reader",
          ],
          debug: {
            showCanvas: true,
            showPatches: true,
            showFoundPatches: true,
            showSkeleton: true,
            showLabels: true,
            showPatchLabels: true,
            showRemainingPatchLabels: true,
            boxFromPatches: {
              showTransformed: true,
              showTransformedBox: true,
              showBB: true,
            },
          },
        },
      },
      function (err) {
        if (err) {
          console.log(err);
          return;
        }

        console.log("Initialization finished. Ready to start");
        Quagga.start();
      }
    );

    Quagga.onProcessed(function (result) {
      var drawingCtx = Quagga.canvas.ctx.overlay,
        drawingCanvas = Quagga.canvas.dom.overlay;

      if (result) {
        if (result.boxes) {
          drawingCtx.clearRect(
            0,
            0,
            parseInt(drawingCanvas.getAttribute("width")),
            parseInt(drawingCanvas.getAttribute("height"))
          );
          result.boxes
            .filter(function (box) {
              return box !== result.box;
            })
            .forEach(function (box) {
              Quagga.ImageDebug.drawPath(
                box,
                {
                  x: 0,
                  y: 1,
                },
                drawingCtx,
                {
                  color: "green",
                  lineWidth: 2,
                }
              );
            });
        }

        if (result.box) {
          Quagga.ImageDebug.drawPath(
            result.box,
            {
              x: 0,
              y: 1,
            },
            drawingCtx,
            {
              color: "#00F",
              lineWidth: 2,
            }
          );
        }

        if (result.codeResult && result.codeResult.code) {
          Quagga.ImageDebug.drawPath(
            result.line,
            {
              x: "x",
              y: "y",
            },
            drawingCtx,
            {
              color: "red",
              lineWidth: 3,
            }
          );
        }
      }
    });

    Quagga.onDetected(function (result) {
      console.log(
        "Barcode detected and processed : [" + result.codeResult.code + "]",
        result
      );

      // --- ¡ACCIÓN IMPORTANTE! ---
      // Aquí es donde obtienes el código.
      // Asigna el código a un campo de tu formulario.
      // Por ejemplo, si tienes un input con id="codigo_producto":
      // document.getElementById('codigo_producto').value = result.codeResult.code;

      // Detiene el escaner y oculta el contenedor
      Quagga.stop();
      scannerContainer.style.display = "none";

      // Opcional: Muestra una alerta con el código
      alert("Código de barras detectado: " + result.codeResult.code);
    });
  }
});
