import $ from 'jquery';
import {Tooltip} from 'bootstrap';

export const initTooltip = () => {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        if (!Tooltip.getInstance(tooltipTriggerEl)) {
            new Tooltip(tooltipTriggerEl);
        }
    });
}

initTooltip();