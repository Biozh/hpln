import * as bootstrap from 'bootstrap';
import $ from 'jquery';

const sidebar = $("#sidebar");

$(".sidebar-toggler").on("click", function() {
    $(this).find(".material-symbols-rounded").html(sidebar.hasClass("mini") ? "menu_open" : "menu");
    localStorage.setItem("sidebar", sidebar.hasClass("mini") ? "opened" : "mini");
    
    sidebar.toggleClass("opened mini");
})
