import $ from 'jquery';
import 'popper.js';
import 'bootstrap/dist/js/bootstrap.bundle';
import 'admin-lte/dist/js/adminlte';
import tinymce from 'tinymce/tinymce';
import 'tinymce/icons/default';
import 'tinymce/models/dom';
import 'tinymce/themes/silver';
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/media';
import 'tinymce/plugins/table';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/wordcount';
import 'tinymce/skins/ui/oxide/skin.min.css';
import 'tinymce/skins/content/default/content.min.css';

window.$ = window.jQuery = $;

$(function () {
    $('[data-toggle="dropdown"]').dropdown();
    $('[data-widget="treeview"]').Treeview('init');
    $('[data-widget="pushmenu"]').PushMenu();

    const pageEditor = document.querySelector('.page-content-editor');

    if (pageEditor) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const libraryModal = $('#page-media-library-modal');
        const libraryGrid = document.getElementById('page-media-library-grid');
        const libraryEmpty = document.getElementById('page-media-library-empty');
        let activeEditor = null;

        const openMediaLibrary = async (editor) => {
            activeEditor = editor;
            libraryGrid.replaceChildren();
            libraryEmpty.classList.add('d-none');

            const response = await fetch(pageEditor.dataset.imageListUrl, {
                headers: { Accept: 'application/json' },
            });
            const images = await response.json();

            if (!images.length) libraryEmpty.classList.remove('d-none');

            images.forEach((image) => {
                const column = document.createElement('div');
                const button = document.createElement('button');
                const preview = document.createElement('img');
                const name = document.createElement('span');

                column.className = 'col-xl-3 col-lg-4 col-md-6 mb-3';
                button.type = 'button';
                button.className = 'page-media-library-item btn btn-light border p-2 text-left w-100';
                preview.src = image.url;
                preview.alt = image.alt;
                preview.className = 'page-media-library-image';
                name.textContent = image.name;
                name.className = 'd-block text-truncate small mt-2';
                button.append(preview, name);
                button.addEventListener('click', () => {
                    activeEditor.insertContent(activeEditor.dom.createHTML('img', {
                        src: image.url,
                        alt: image.alt,
                        class: 'img-fluid',
                    }));
                    libraryModal.modal('hide');
                });
                column.appendChild(button);
                libraryGrid.appendChild(column);
            });

            libraryModal.modal('show');
        };

        tinymce.init({
            selector: '.page-content-editor',
            license_key: 'gpl',
            skin: false,
            content_css: false,
            height: 520,
            menubar: 'edit view insert format tools table',
            plugins: 'advlist autolink link image lists media table code fullscreen wordcount',
            toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image rimisMedia | table blockquote | removeformat code fullscreen',
            image_caption: false,
            automatic_uploads: true,
            convert_urls: false,
            images_upload_handler: async (blobInfo, progress) => {
                const body = new FormData();
                body.append('file', blobInfo.blob(), blobInfo.filename());

                const response = await fetch(pageEditor.dataset.imageUploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                    body,
                });

                if (!response.ok) throw new Error('No se pudo subir la imagen.');
                progress(100);

                return (await response.json()).location;
            },
            setup: (editor) => {
                editor.ui.registry.addButton('rimisMedia', {
                    text: 'Biblioteca RIMIS',
                    icon: 'image',
                    tooltip: 'Insertar imagen desde la biblioteca multimedia',
                    onAction: () => openMediaLibrary(editor),
                });

                editor.on('change input undo redo', () => editor.save());
                editor.on('init', () => {
                    editor.getElement().closest('form').addEventListener('submit', () => tinymce.triggerSave());
                });
            },
            content_style: 'body { font-family: Arial, sans-serif; font-size: 16px; line-height: 1.6; } img { max-width: 100%; height: auto; }',
        });
    }

    document.querySelectorAll('.custom-file-input').forEach((input) => {
        input.addEventListener('change', () => {
            const label = input.nextElementSibling;
            if (label && input.files.length) label.textContent = input.files[0].name;
        });
    });

    document.querySelectorAll('.edit-menu-item').forEach((button) => {
        button.addEventListener('click', () => {
            const form = document.getElementById('edit-menu-item-form');
            form.action = button.dataset.action;
            form.elements.label.value = button.dataset.label;
            form.elements.url.value = button.dataset.url;
            form.elements.icon.value = button.dataset.icon;
            form.elements.target.value = button.dataset.targetValue;
            form.elements.parent_id.value = button.dataset.parent;
            form.elements.is_active.value = button.dataset.active;
            form.elements.icon.dispatchEvent(new Event('input'));
        });
    });

    document.querySelectorAll('.menu-icon-input').forEach((input) => {
        const preview = input.closest('.input-group').querySelector('.menu-icon-preview');
        const updatePreview = () => {
            const iconClass = input.value.trim() || 'fa-solid fa-icons';
            preview.replaceChildren(Object.assign(document.createElement('i'), { className: iconClass }));
        };

        input.addEventListener('input', updatePreview);
        updatePreview();
    });

    document.querySelectorAll('.menu-page-url').forEach((select) => {
        select.addEventListener('change', () => {
            if (select.value) select.closest('form').elements.url.value = select.value;
        });
    });

    document.querySelectorAll('.page-block-modal').forEach((modal) => {
        const typeSelect = modal.querySelector('.page-block-type');
        if (!typeSelect) return;

        const updateBlockFields = () => {
            modal.querySelectorAll('.page-block-field').forEach((field) => {
                const types = field.dataset.blockTypes.split(',');
                field.classList.toggle('d-none', !types.includes(typeSelect.value));
            });
        };

        typeSelect.addEventListener('change', updateBlockFields);
        updateBlockFields();
    });

    document.querySelectorAll('.seo-editor').forEach((editor) => {
        const title = editor.querySelector('.seo-title-input');
        const description = editor.querySelector('.seo-description-input');
        const keywords = editor.querySelector('.seo-keywords-input');
        const canonical = editor.querySelector('.seo-canonical-input');
        const previewTitle = editor.querySelector('.seo-preview-title');
        const previewDescription = editor.querySelector('.seo-preview-description');
        const previewUrl = editor.querySelector('.seo-preview-url');
        const titleCount = editor.querySelector('.seo-title-count');
        const descriptionCount = editor.querySelector('.seo-description-count');

        const updateSeoPreview = () => {
            const shownTitle = title.value || editor.dataset.suggestedTitle || 'Título de la página';
            const shownDescription = description.value || editor.dataset.suggestedDescription || 'Descripción de la página para buscadores.';
            const shownCanonical = canonical.value || editor.dataset.suggestedCanonical || window.location.origin;
            previewTitle.textContent = shownTitle;
            previewDescription.textContent = shownDescription;
            previewUrl.textContent = shownCanonical;
            titleCount.textContent = `${title.value.length}/60`;
            descriptionCount.textContent = `${description.value.length}/160`;
            titleCount.classList.toggle('text-danger', title.value.length > 60);
            descriptionCount.classList.toggle('text-danger', description.value.length > 160);
        };

        [title, description, canonical].forEach((input) => input.addEventListener('input', updateSeoPreview));
        editor.querySelector('.apply-seo-suggestions')?.addEventListener('click', () => {
            title.value = editor.dataset.suggestedTitle;
            description.value = editor.dataset.suggestedDescription;
            keywords.value = editor.dataset.suggestedKeywords;
            canonical.value = editor.dataset.suggestedCanonical;
            updateSeoPreview();
        });
        updateSeoPreview();
    });

    const dropzone = document.getElementById('media-dropzone-form');
    const fileInput = document.getElementById('media-dropzone-input');

    if (dropzone && fileInput) {
        const browseButton = document.getElementById('media-browse-button');
        const uploadFiles = (files) => {
            if (!files.length) return;

            const transfer = new DataTransfer();
            Array.from(files).slice(0, 20).forEach((file) => transfer.items.add(file));
            fileInput.files = transfer.files;
            dropzone.querySelector('.media-dropzone-content').classList.add('d-none');
            dropzone.querySelector('.media-upload-progress').classList.remove('d-none');
            dropzone.submit();
        };

        browseButton.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', () => uploadFiles(fileInput.files));

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.add('is-dragging');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.remove('is-dragging');
            });
        });

        dropzone.addEventListener('drop', (event) => uploadFiles(event.dataTransfer.files));
    }

    const checkboxes = Array.from(document.querySelectorAll('.media-checkbox'));
    const selectAll = document.getElementById('select-all-media');
    const counter = document.getElementById('selection-counter');
    const bulkButton = document.getElementById('bulk-delete-button');
    const bulkInputs = document.getElementById('bulk-delete-inputs');

    const updateSelection = () => {
        const selected = checkboxes.filter((checkbox) => checkbox.checked);

        checkboxes.forEach((checkbox) => {
            checkbox.closest('.media-card').classList.toggle('is-selected', checkbox.checked);
        });

        if (counter) counter.textContent = `${selected.length} seleccionados`;
        if (bulkButton) bulkButton.disabled = selected.length === 0;
        if (selectAll) selectAll.checked = selected.length > 0 && selected.length === checkboxes.length;

        if (bulkInputs) {
            bulkInputs.innerHTML = selected
                .map((checkbox) => `<input type="hidden" name="media_ids[]" value="${checkbox.value}">`)
                .join('');
        }
    };

    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSelection));
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes.forEach((checkbox) => { checkbox.checked = selectAll.checked; });
            updateSelection();
        });
    }

    const toast = document.getElementById('media-toast');
    const copyUrl = async (url) => {
        const absoluteUrl = new URL(url, window.location.origin).href;

        try {
            await navigator.clipboard.writeText(absoluteUrl);
        } catch (error) {
            const temporaryInput = document.createElement('input');
            temporaryInput.value = absoluteUrl;
            document.body.appendChild(temporaryInput);
            temporaryInput.select();
            document.execCommand('copy');
            temporaryInput.remove();
        }

        if (toast) {
            toast.classList.add('show');
            window.setTimeout(() => toast.classList.remove('show'), 2200);
        }
    };

    document.querySelectorAll('.copy-media-url').forEach((button) => {
        button.addEventListener('click', () => copyUrl(button.dataset.url));
    });

    const previewModal = $('#media-preview-modal');
    const previewContent = document.getElementById('media-preview-content');
    const previewTitle = document.getElementById('media-preview-title');
    const previewMeta = document.getElementById('media-preview-meta');
    const modalOpenFile = document.getElementById('modal-open-file');
    const modalCopyUrl = document.getElementById('modal-copy-url');

    document.querySelectorAll('.media-preview-trigger').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const card = trigger.closest('.media-card');
            const isImage = card.dataset.type === 'image';

            previewTitle.textContent = card.dataset.name;
            previewMeta.textContent = `${card.dataset.mime} · ${card.dataset.size}`;
            modalOpenFile.href = card.dataset.url;
            modalCopyUrl.dataset.url = card.dataset.url;
            previewContent.replaceChildren();

            if (isImage) {
                const image = document.createElement('img');
                image.src = card.dataset.url;
                image.alt = card.dataset.name;
                image.className = 'img-fluid media-modal-image';
                previewContent.appendChild(image);
            } else {
                const emptyPreview = document.createElement('div');
                const icon = document.createElement('i');
                const name = document.createElement('h5');
                const message = document.createElement('p');

                emptyPreview.className = 'py-5';
                icon.className = 'fas fa-file-alt fa-5x text-muted mb-3';
                name.textContent = card.dataset.name;
                message.className = 'text-muted';
                message.textContent = 'La vista previa no está disponible para este tipo de archivo.';
                emptyPreview.append(icon, name, message);
                previewContent.appendChild(emptyPreview);
            }

            previewModal.modal('show');
        });
    });

    if (modalCopyUrl) {
        modalCopyUrl.addEventListener('click', () => copyUrl(modalCopyUrl.dataset.url));
    }
});
