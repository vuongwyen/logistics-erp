<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-navy text-white border-0 p-3">
                <h5 class="modal-title fw-bold">
                    <i class="fa fa-file-alt me-2"></i>Xem trước chứng từ: <span id="previewDocCategory"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light text-center position-relative" style="min-height: 500px;">
                <!-- Loading spinner -->
                <div id="previewLoading" class="position-absolute top-50 start-50 translate-middle">
                    <div class="spinner-border text-navy" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <!-- PDF Viewer -->
                <iframe id="pdfViewer" class="w-100 d-none" style="height: 80vh; border: none;"></iframe>

                <!-- Image Viewer -->
                <img id="imageViewer" class="img-fluid d-none" style="max-height: 80vh; object-fit: contain;">

                <!-- Other file types (Word, Excel) -->
                <div id="otherViewer" class="position-absolute top-50 start-50 translate-middle d-none text-center w-100">
                    <i class="fa fa-file-word text-primary mb-3" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mb-3">Không thể xem trước định dạng này</h5>
                    <a id="downloadBtn" href="#" target="_blank" class="btn btn-navy">
                        <i class="fa fa-download me-2"></i>Tải xuống để xem
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewDocument(url, category, extension) {
        document.getElementById('previewDocCategory').innerText = category || 'Tài liệu';
        
        let pdfViewer = document.getElementById('pdfViewer');
        let imgViewer = document.getElementById('imageViewer');
        let otherViewer = document.getElementById('otherViewer');
        let loading = document.getElementById('previewLoading');
        let downloadBtn = document.getElementById('downloadBtn');

        // Reset
        pdfViewer.classList.add('d-none');
        imgViewer.classList.add('d-none');
        otherViewer.classList.add('d-none');
        loading.classList.remove('d-none');

        ext = extension ? extension.toLowerCase() : '';

        if (ext === 'pdf') {
            pdfViewer.src = url;
            pdfViewer.onload = () => loading.classList.add('d-none');
            pdfViewer.classList.remove('d-none');
        } else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
            imgViewer.src = url;
            imgViewer.onload = () => loading.classList.add('d-none');
            imgViewer.classList.remove('d-none');
        } else {
            loading.classList.add('d-none');
            otherViewer.classList.remove('d-none');
            downloadBtn.href = url;
        }
    }
</script>
