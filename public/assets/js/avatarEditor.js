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
  let lastPreviewSrc = preview.src; // Save the current preview source
  let isNewUpload = false; // Tracks if a new image was uploaded

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

  const saveCroppedImage = () => {
    const outputCanvas = document.createElement("canvas");
    outputCanvas.width = 512; // Final cropped image size
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

    // Update the preview and hidden input with the cropped image
    const croppedDataUrl = outputCanvas.toDataURL();
    preview.src = croppedDataUrl;
    hiddenInput.value = croppedDataUrl;
  };

  upload.addEventListener("change", (event) => {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        lastPreviewSrc = preview.src; // Save the current preview before changing
        image.src = e.target.result;
        isNewUpload = true; // Mark this as a new upload
        removeButton.removeAttribute("disabled"); // Enable the remove button
      };
      reader.readAsDataURL(file);
    }
  });

  image.onload = () => {
    scale = 1;
    offsetX = canvas.width / 2 - image.width / 2;
    offsetY = canvas.height / 2 - image.height / 2;
    drawImage();

    // Automatically save the cropped image
    saveCroppedImage();
  };

  avatarContainer.addEventListener("click", () => {
    if (!image.src || isNewUpload) {
      // Allow file upload only if no image or this is a new upload
      upload.click();
    } else {
      // Open the modal for editing
      let modalInstance = bootstrap.Modal.getInstance(modal);
      if (!modalInstance) {
        modalInstance = new bootstrap.Modal(modal);
      }
      modalInstance.show();
    }
  });

  removeButton.addEventListener("click", () => {
    if (image.src) {
      removeButton.setAttribute("disabled", true); // Disable the button after removal
      preview.src = lastPreviewSrc; // Restore the previous image
      hiddenInput.value = lastPreviewSrc; // Update the hidden input
      upload.value = ""; // Clear the file input
      image = new Image(); // Reset the image object
      isNewUpload = false; // Reset new upload flag
      ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear the canvas
    }
  });

  saveButton.addEventListener("click", () => {
    saveCroppedImage();

    // Close the modal
    let modalInstance = bootstrap.Modal.getInstance(modal);
    if (!modalInstance) {
      modalInstance = new bootstrap.Modal(modal);
    }
    modalInstance.hide();

    isNewUpload = false; // Mark upload as handled
  });

  slider.addEventListener("input", (e) => {
    const newScale = parseFloat(e.target.value);
    const scaleChange = newScale / scale;

    // Adjust offsets to keep zoom focused on canvas center
    offsetX = canvas.width / 2 - (canvas.width / 2 - offsetX) * scaleChange;
    offsetY = canvas.height / 2 - (canvas.height / 2 - offsetY) * scaleChange;

    scale = newScale;
    drawImage();
  });

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

  canvas.addEventListener("mouseleave", () => {
    isDragging = false;
    canvas.style.cursor = "grab";
  });
});
