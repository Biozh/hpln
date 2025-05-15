import $ from 'jquery';
import { initSelect2 } from './form/select2';
import { initDatatable } from '../datatable/datatable.js';

import * as bootstrap from 'bootstrap';
import { disableForm, showAlert } from './utils';
import { initTooltip } from './form/tooltip.js';

$("form.needs-validation").on('submit', function (e) {
    const form = $(this);
    if (!form[0].checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    form.addClass('was-validated');
});

export const onModalHidden = function () {
    console.log("removing ", $(this))
    $(this).remove();
    $(".tooltip").fadeOut();
};
// Fonction pour initialiser un modal avec un formulaire
export const openModalForm = (url, type = 'edit', cb = () => { }) => {
    return new Promise((resolve, reject) => {
        $.get(url)
            .done(function (form) {
                let modal = createModal();
                $("body").append(modal);
                modal.find('.modal-content').html(form);

                if (type === 'see') {
                    disableForm(modal);
                }

                const bsModal = new bootstrap.Modal(modal.get(0));

                modal.get(0).addEventListener('hidden.bs.modal', onModalHidden);

                bsModal.show();
                if (cb) cb();

                initSelect2(modal.find(".select2"));
                initTooltip();
                handleFormSubmission(modal, url, bsModal, cb);

                // file input previews
                modal.find("[data-ajax-preview]").each(function () {
                    const input = $("#" + $(this).data('ajax-preview'));
                    if (input.length > 0) {
                        const preview = $(this);
                        input.on('change', function (e) {
                            if (e.target.files.length > 0) {
                                const file = e.target.files[0];
                                const reader = new FileReader();
                                reader.onload = function (e) {
                                    if (preview.is('img')) {
                                        preview.attr('src', e.target.result);
                                    } else {
                                        preview.css('background-image', 'url(' + e.target.result + ')');
                                    }
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                    }
                });

                resolve(bsModal);
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                reject(new Error(`Erreur lors du chargement du formulaire : ${textStatus}`));
            });
    });
};


// Fonction pour créer un modal
const createModal = () => {
    return $(`
        <div class="modal fade" tabindex="-1" id="${Date.now()}">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 overflow-auto">
                </div>
            </div>
        </div>
    `);
};


// Fonction pour gérer la soumission d'un formulaire en Ajax
const handleFormSubmission = (modal, url, bsModal, cb = () => { }) => {
    modal.find('form').off('submit').on('submit', function (e) {
        handleAjaxForm($(this), e, url, (res) => {
            initDatatable();
            if (bsModal) bsModal.hide();
            $('[data-ajax-reload]').trigger('click');
            if (cb) cb(res);
        });
    });
};

// Fonction pour traiter les formulaires en Ajax
export const handleAjaxForm = (form, e, url, onSuccess = () => { }, onError = () => { }) => {
    const formEl = form[0];
    if (!formEl.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        form.addClass('was-validated');
    } else {
        form.addClass('was-validated');
        e.preventDefault();
        let formData = new FormData(formEl);

        disableSubmitButton(form);

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                enableSubmitButton(form);
                handleAjaxSuccess(response, onSuccess);
            },
            error: function (xhr) {
                enableSubmitButton(form);
                handleAjaxError(form, xhr, onError);
            },
        });
    }
};

// Fonction pour désactiver le bouton de soumission du formulaire
const disableSubmitButton = (form) => {
    const submitBtn = form.find('[type="submit"]');
    submitBtn.prop('disabled', true).addClass('disabled');
    submitBtn.prepend('<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>');

    // also add the spinner to the outside button
    // (submit button that is not inside the form, containing form="formId")
    const outSideSubmitBtn = $("[form='" + form.attr('id') + "']");
    if (outSideSubmitBtn.length > 0) {
        outSideSubmitBtn.prop('disabled', true).addClass('disabled');
        outSideSubmitBtn.prepend('<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>');
    }
};

// Fonction pour réactiver le bouton de soumission du formulaire
const enableSubmitButton = (form) => {
    const submitBtn = form.find('[type="submit"]');
    submitBtn.prop('disabled', false).removeClass('disabled');
    submitBtn.find('.spinner-border').remove();

    // also remove the spinner from the outside button
    // (submit button that is not inside the form, containing form="formId")
    const outSideSubmitBtn = $("[form='" + form.attr('id') + "']");
    if (outSideSubmitBtn.length > 0) {
        outSideSubmitBtn.prop('disabled', false).removeClass('disabled');
        outSideSubmitBtn.find('.spinner-border').remove();
    }
};

// Fonction pour traiter une réponse Ajax réussie
const handleAjaxSuccess = (response, onSuccess) => {
    if (response.success) {
        onSuccess(response);
        if (response.redirect) {
            location.href = response.redirect;
            return;
        }
        if (response.message) {
            showAlert('success', response.message);
        }
    }
};

// Fonction pour gérer les erreurs dans une requête Ajax
const handleAjaxError = (form, xhr, onError) => {
    if (xhr.responseJSON && xhr.responseJSON.errors) {
        onError(xhr.responseJSON);

        // Nettoie les anciennes erreurs
        form.find('.invalid-feedback').remove();
        form.find('.is-invalid').removeClass('is-invalid');

        // Affiche les nouvelles erreurs
        xhr.responseJSON.errors.forEach(function (error) {
            let field = form.find('[name="' + error.field + '"]');
            if (field) {
                field.addClass('is-invalid');
                let feedback = $('<div class="invalid-feedback">' + error.message + '</div>');
                field.after(feedback);
            } else {
                showAlert('danger', error.message);
            }
        });
    }
};

// Gestion des événements document
$(document).on('click', '.openForm', function () {
    const button = $(this);

    // Empêche double clic
    if (button.hasClass('loading')) return;

    const originalContent = button.html();
    const originalWidth = button.outerWidth();
    const originalHeight = button.outerHeight();

    // Appliquer taille fixe
    button
        .addClass('loading')
        .prop('disabled', true)
        .css({
            position: 'relative',
            width: originalWidth,
            height: originalHeight,
        });

    // Masquer le contenu sans le retirer
    button.html(`
        <span class="btn-content" style="opacity: 0;">${originalContent}</span>
        <div class="flex-center" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
            </span>
        </div>
    `);

    const url = button.data('url');
    const type = button.data('type') ?? 'edit';

    openModalForm(url, type, () => {
        button
            .removeClass('loading')
            .prop('disabled', false)
            .css({ width: '', height: '' }) // on remet à l’état auto
            .html(originalContent);
        $(".tooltip").fadeOut();
    });
});


$(".ajaxForm").off('submit').on('submit', function (e) {
    console.log("sub")
    let url = $(this).data('url');
    e.preventDefault();
    handleAjaxForm($(this), e, url);
});

$(document).on('click', '[data-redirect]', function () {
    let url = $(this).data('redirect');
    location.href = url;
});

$(document).on('click', '[data-ajax-post]', function () {
    let url = $(this).data('ajax-post');
    const form = $("#" + $(this).data('ajax-form'));
    let formData = null
    if ($(this).data('ajax-form') && form.length > 0) {
        if (form.length > 0) {
            formData = new FormData(form[0]);
        }
    }

    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            handleAjaxSuccess(response, () => { });
        },
        error: function (xhr) {
            handleAjaxError(form, xhr, () => { });
        },
    });
});

// file input previews
$("[data-ajax-preview]").each(function () {
    const input = $("#" + $(this).data('ajax-preview'));
    if (input.length > 0) {
        const preview = $(this);
        input.on('change', function (e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const reader = new FileReader();
                reader.onload = function (e) {
                    // take of the user initials
                    preview.html("");
                    if (preview.is('img')) {
                        preview.attr('src', e.target.result);
                    } else {
                        preview.css('background-image', 'url(' + e.target.result + ')');
                    }
                };
                reader.readAsDataURL(file);

            }
        });
    }
})