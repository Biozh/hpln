/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import 'material-symbols';
import './styles/app.scss';

import $ from 'jquery';
import * as bootstrap from 'bootstrap';

 
import 'jquery-ui/themes/base/core.css'
import 'jquery-ui/themes/base/sortable.css'
import 'jquery-ui/themes/base/theme.css'
import 'jquery-ui/ui/widgets/sortable'
import 'jquery-ui-bootstrap/jquery.ui.theme.css'
import 'jquery-ui-bootstrap/jquery.ui.theme.font-awesome.css'


import select2 from 'select2';
import "select2/dist/css/select2.min.css"
import "select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css"

import './js/sidebar.js';
import './js/datepicker.js';

import { initDatatable } from './datatable/datatable.js';
import './datatable/datatable.scss';
initDatatable();

import { initSelect2 } from './js/form/select2.js';
initSelect2(); 



import './js/ajax.js';


$(".alert-flash").each(function() {
    setTimeout(() => {
        $(this).fadeOut(500)
    }, 2000)
});