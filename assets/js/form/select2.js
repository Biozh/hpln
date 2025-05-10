import $ from 'jquery';

import select2 from 'select2';
import "select2/dist/css/select2.min.css";
import "select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css";

export const initSelect2 = (element = $(".select2")) => {
    element.select2({
        theme: "bootstrap-5",
        tags: true,
        minimumResultsForSearch: "Infinity",
        tokenSeparators: [',', ' '],
    });
}
