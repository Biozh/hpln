import $ from 'jquery';
window.$ = $;
window.jQuery = $;

require('select2/dist/js/i18n/fr');
import "select2/dist/css/select2.min.css";
// import 'select2/dist/js/i18n/fr.js';

import { onModalHidden, openModalForm } from '../ajax';
import * as bootstrap from 'bootstrap';

export const initSelect2 = (elements = $(".select2")) => {
    elements.each(function () {
        const select = $(this)
        $(this).select2({
            language: "fr",
            theme: 'bootstrap-5',
            dropdownParent: $(this).closest(".modal").length ? $(this).closest(".modal") : $(this).parent(),
            tags: select.data("tags") || false,
            templateResult: (data) => formatOptionWithDelete(data, $(this)),
            escapeMarkup: markup => markup,
            createTag: function (params) {
                if (select.data("allow-create") != true) {
                    return null;
                }

                return {
                    id: params.term,
                    text: params.term
                }
            },
        })
    });
}

function formatOptionWithDelete(data, select) {
    if (!data.id || !data.element || !data?.element.dataset?.url) {
        return data.text;
    }

    var $option = $(`<div class="d-flex align-items-center justify-content-between"></div>`);
    var $button = $(`
        <button 
            type="button"
            class="bg-transparent border-0 p-0 d-flex align-items-center justify-content-center" 
            style="color: inherit"
            data-bs-dismiss="modal"
            data-type="delete" 
            data-url="${data?.element.dataset.url}"
        >
            <span class="material-symbols-rounded fs-6">close</span>
        </button>
    `    );
    $button.on('mouseup', function (evt) {
        // Select2 will remove the dropdown on `mouseup`, which will prevent any `click` events from being triggered
        // So we need to block the propagation of the `mouseup` event
        evt.stopPropagation();
    });

    $button.off("click").on('click', async function (evt) {
        const bsModal = await openModalForm(data?.element.dataset.url, 'delete', function (res) {
            setTimeout(() => {
                select.find(`option[value="${res.category}"]`).remove();
                select.trigger('change');
            }, 500)
        });

        const currentModal = select.closest(".modal")
        const currentBsModal = bootstrap.Modal.getInstance(currentModal[0])

        const modal = $(bsModal._element);

        currentModal.get(0).removeEventListener('hidden.bs.modal', onModalHidden);

        modal.get(0).addEventListener('hidden.bs.modal', function (e) {
            currentBsModal.show();

            currentBsModal._element.addEventListener('shown.bs.modal', function () {
                console.log("shown.bs.modal")
                initSelect2($(currentBsModal._element).find("select.select2"))
                $(currentBsModal._element).find("select.select2").each(function () {
                    select.select2('open')
                })
            }, { once: true })
        });
    });

    $option.text(data.text);
    $option.append($button);

    return $option;
}