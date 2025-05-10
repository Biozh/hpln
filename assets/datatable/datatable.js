import $ from 'jquery';

import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import language from 'datatables.net-plugins/i18n/fr-FR.mjs';

import { initSelect2 } from "../js/form/select2";
import { initTooltip } from "../js/form/tooltip";
import { Dropdown } from 'bootstrap';

export const initDatatable = (datatables = $(".datatable[data-url]")) => {
    datatables.each(function() {
        var table = $(this);
        const tableEl = $(this);

        if ($.fn.DataTable.isDataTable(table)) {
            table.DataTable().ajax.reload();
            return;
        }

        let url = $(this).data('url');
        const totalCols = $(this).find("th").length;
        const tableSortingDefault = $(this).attr('data-sorting-default') ? JSON.parse($(this).attr('data-sorting-default')) : [[0, "asc"]];
        const notOrderable = $(this).attr('data-not-orderable') ? JSON.parse($(this).attr('data-not-orderable')) : [totalCols - 1];
        const lengthMenu = $(this).attr('data-length-menu') ? JSON.parse($(this).attr('data-length-menu')) : [[50, 100, 150], [50, 100, 150]];
        const defaultLength = $(this).attr('data-default-length') ? JSON.parse($(this).attr('data-default-length')) : 50;

        var aoColumns = [];


        $(this).find('thead th').each(function(i) {
            if($(this).attr('data-not-orderable') !== undefined && !notOrderable.includes(i)) {
                notOrderable.push(i);
            }

            aoColumns.push({
                data: $(this).data('name'),
            });
        });

        aoColumns.map((column, index) => {
            if(notOrderable.includes(index)) {
                column.orderable = false;
            }
        });

        table = $(this).on('draw.dt', function () {
            initSelect2($(this).find('.select2'));
            initSelect2($(this).parent().find('.dt-length select'));
            initTooltip();
        }).DataTable({
            autoWidth: false,
            aLengthMenu: lengthMenu,
            responsive: true,
            ajax: url,
            bServerSide: true,
            iDisplayLength: defaultLength,
            // layout
            sDom: 'rt<"row flex-between mt-3 px-3"<"col-lg-6 flex-center justify-content-start"l><"col-lg-6 flex-center justify-content-start justify-content-lg-end"p>>',
            aaSorting: tableSortingDefault,
            aoColumns: aoColumns,
            columnDefs: [{
                targets: notOrderable,
                orderable: false
            }],
            pagingType: 'numbers',
            language: {
                ...language
            }
        });

        $(window).on("resize", function() {
            console.log("ok")
            table.columns.adjust().responsive.recalc();
        })
        
        // filters
        tableEl.parent().parent().find("[data-bs-dismiss='dropdown']").on("click", function(e) {
            const dropdownElement = $(this).closest('.dropdown').find('[data-bs-toggle="dropdown"]')[0];
            const dropdownInstance = Dropdown.getOrCreateInstance(dropdownElement);
            dropdownInstance.hide();
        });
        tableEl.parent().parent().find(".dropdown.filter form").on("reset", function(e) {
            setTimeout(() => {
                $(this).trigger("submit");
            }, 1)
        })
        tableEl.parent().parent().find(".dropdown.filter form").on("submit", function(e) {
            e.preventDefault();
            $(this).find('[data-filter]').each(function() {
                var columnIdx = $(this).data('filter');
                var value = $(this).val();
                table.column(columnIdx).search(value);
                console.log(value)
            });
            
            table.columns.adjust().responsive.recalc();
            table.draw();

            // Close the dropdown after applying filters
            const dropdownElement = $(this).closest('.dropdown').find('[data-bs-toggle="dropdown"]')[0];
            const dropdownInstance = Dropdown.getOrCreateInstance(dropdownElement);
            dropdownInstance.hide();
        })

        // search
        $(this).find(".filter .dt-orderable-asc").on('keydown', function(e) { 
            if(e.key === "Enter") {
                e.preventDefault();
                e.stopPropagation();
            }
         });
        $(this).find('.table-filter').on('keyup change', function() {
            var i = $(this).data('filter');
            table.columns.adjust().responsive.recalc();
            table.column(i).search($(this).val()).draw();
        });
        $(this).find(".filter .dt-orderable-asc").on('click', function(e) { e.preventDefault(); e.stopPropagation(); });
    });
};

