@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('imageCropper', () => ({
            open: false,
            cropper: null,
            
            init() {
                // When the photo is selected via Livewire
                window.addEventListener('livewire:initialized', () => {
                    this.$wire.on('photo-selected', () => {
                        console.log('Photo selected event received');
                        this.openCropper();
                    });
                });
            },
            
            openCropper() {
                this.open = true;
                
                // Initialize the cropper on the next tick after the modal is shown
                setTimeout(() => {
                    const image = document.getElementById('cropper-image');
                    if (image && image.src) {
                        console.log('Initializing cropper');
                        if (this.cropper) {
                            this.cropper.destroy();
                        }
                        
                        this.cropper = new Cropper(image, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 1,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            guides: true,
                            center: true,
                            highlight: false,
                            background: true,
                            responsive: true,
                        });
                    } else {
                        console.error('Cropper image element not found or has no source');
                    }
                }, 300);
            },
            
            closeCropper() {
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
                this.open = false;
            },
            
            saveCropped() {
                console.log('Save crop button clicked');
                
                if (!this.cropper) {
                    console.error('Cropper not initialized');
                    alert('Error: Cropper not initialized. Please try again.');
                    return;
                }
                
                try {
                    const canvas = this.cropper.getCroppedCanvas({
                        width: 400,
                        height: 400
                    });
                    
                    if (!canvas) {
                        console.error('Failed to create canvas');
                        alert('Error: Failed to create canvas. Please try again.');
                        return;
                    }
                    
                    // Update preview image
                    const previewImage = document.getElementById('preview-image');
                    if (previewImage) {
                        previewImage.src = canvas.toDataURL('image/webp');
                    }
                    
                    // Send data to Livewire component
                    console.log('Sending cropped image to Livewire');
                    this.$wire.cropPhoto(canvas.toDataURL('image/webp'));
                    
                    // Close modal
                    this.closeCropper();
                    
                    console.log('Crop saved successfully');
                } catch (error) {
                    console.error('Error in saveCropped:', error);
                    alert('An error occurred while saving the cropped image. Please try again.');
                }
            }
        }));
    });
</script>
@endpush 