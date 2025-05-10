import $ from 'jquery';

import datepickerFactory from 'jquery-datepicker';
import datepickerFRFactory from 'jquery-datepicker/i18n/jquery.ui.datepicker-fr';

datepickerFactory($);
datepickerFRFactory($);
  
$(function() {
    $('.datepicker').datepicker();
    $.datepicker.regional['fr'];
  });