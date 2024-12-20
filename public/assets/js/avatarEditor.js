document.addEventListener("DOMContentLoaded", () => {
  const upload = document.getElementById("upload");
  const removeButton = document.getElementById("removeButton");
  const modal = document.getElementById("modal");
  const saveButton = document.getElementById("saveButton");
  const slider = document.getElementById("slider");
  const canvas = document.getElementById("modalCanvas");
  const preview = document.getElementById("preview");
  const avatarContainer = document.getElementById("avatar-container");
  const ctx = canvas.getContext("2d");
  const hiddenInput = document.getElementById("hiddenAvatar");

  let image = new Image();
  let scale = 1;
  let offsetX = 0;
  let offsetY = 0;
  let isDragging = false;
  let startX, startY;
  let lastPreviewSrc = preview.src;

  const drawImage = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Draw the circular mask
    ctx.save();
    ctx.beginPath();
    ctx.arc(
      canvas.width / 2,
      canvas.height / 2,
      canvas.width / 2,
      0,
      Math.PI * 2
    );
    ctx.closePath();
    ctx.clip();

    // Keep the image within the circle
    const scaledWidth = image.width * scale;
    const scaledHeight = image.height * scale;

    const minOffsetX = canvas.width / 2 - scaledWidth;
    const maxOffsetX = canvas.width / 2;
    const minOffsetY = canvas.height / 2 - scaledHeight;
    const maxOffsetY = canvas.height / 2;

    offsetX = Math.min(Math.max(offsetX, minOffsetX), maxOffsetX);
    offsetY = Math.min(Math.max(offsetY, minOffsetY), maxOffsetY);

    ctx.drawImage(image, offsetX, offsetY, scaledWidth, scaledHeight);

    ctx.restore();
  };

  upload.addEventListener("change", (event) => {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        image.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  });

  image.onload = () => {
    scale = 1;
    offsetX = canvas.width / 2 - image.width / 2;
    offsetY = canvas.height / 2 - image.height / 2;
    drawImage();
  };

  // Open modal for editing
  removeButton.addEventListener("click", () => {
    if (image.src) {
      removeButton.setAttribute("disabled", true);
      image = new Image();
      hiddenInput.value = "";
      upload.value = "";
      preview.src = lastPreviewSrc;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
  });

  avatarContainer.addEventListener("click", () => {
    if (!image.src) {
      upload.click();
    } else {
      // Use Bootstrap's Modal API to hide it
      let modalInstance = bootstrap.Modal.getInstance(modal); // Get the modal instance
      if (!modalInstance) {
        modalInstance = new bootstrap.Modal(modal); // Create a new instance if needed
      }
      modalInstance.show(); // Dismiss the modal
    }
  });

  upload.addEventListener("change", function () {
    const file = this.files[0]; // Get the first selected file
    if (file) {
      const reader = new FileReader();

      reader.onload = function (event) {
        preview.src = event.target.result; // Set the image source to the file data
        hiddenInput.value = preview.src;
      };

      reader.readAsDataURL(file); // Read the file as a data URL
    }
    removeButton.removeAttribute("disabled");
  });

  // Save cropped avatar to preview
  saveButton.addEventListener("click", () => {
    const outputCanvas = document.createElement("canvas");
    outputCanvas.width = 512;
    outputCanvas.height = 512;
    const outputCtx = outputCanvas.getContext("2d");

    // Apply circular crop
    outputCtx.save();
    outputCtx.beginPath();
    outputCtx.arc(
      outputCanvas.width / 2,
      outputCanvas.height / 2,
      outputCanvas.width / 2,
      0,
      Math.PI * 2
    );
    outputCtx.closePath();
    outputCtx.clip();

    outputCtx.drawImage(
      canvas,
      0,
      0,
      canvas.width,
      canvas.height,
      0,
      0,
      outputCanvas.width,
      outputCanvas.height
    );
    outputCtx.restore();

    preview.src = outputCanvas.toDataURL();

    hiddenInput.value = preview.src;

    // Use Bootstrap's Modal API to hide it
    let modalInstance = bootstrap.Modal.getInstance(modal); // Get the modal instance
    if (!modalInstance) {
      modalInstance = new bootstrap.Modal(modal); // Create a new instance if needed
    }
    modalInstance.hide(); // Dismiss the modal
  });

  // Drag and drop functionality
  canvas.addEventListener("mousedown", (e) => {
    isDragging = true;
    startX = e.offsetX;
    startY = e.offsetY;
    canvas.style.cursor = "grabbing";
  });

  canvas.addEventListener("mousemove", (e) => {
    if (isDragging) {
      const dx = e.offsetX - startX;
      const dy = e.offsetY - startY;
      offsetX += dx;
      offsetY += dy;
      startX = e.offsetX;
      startY = e.offsetY;
      drawImage();
    }
  });

  canvas.addEventListener("mouseup", () => {
    isDragging = false;
    canvas.style.cursor = "grab";
  });

  // Slider for zoom
  slider.addEventListener("input", (e) => {
    const newScale = parseFloat(e.target.value);
    const scaleChange = newScale / scale;

    // Adjust offsets to keep the zoom focused on the center of the canvas
    offsetX = canvas.width / 2 - (canvas.width / 2 - offsetX) * scaleChange;
    offsetY = canvas.height / 2 - (canvas.height / 2 - offsetY) * scaleChange;

    scale = newScale;
    drawImage();
  });

  // Convert dataURL to Blob for upload
  const dataURLToBlob = (dataURL) => {
    const [header, base64] = dataURL.split(",");
    const mime = header.match(/:(.*?);/)[1];
    const binary = atob(base64);
    const array = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) {
      array[i] = binary.charCodeAt(i);
    }
    return new Blob([array], { type: mime });
  };
});
