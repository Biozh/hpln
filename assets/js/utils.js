import $ from 'jquery';

export const disableForm = (modal) => {
    modal.find(":input").prop("disabled", true)
    modal.find(".btn").addClass("disabled");
};

export const showAlert = (type, message, callback = () => { }) => {
    $('#alert-' + type).find(".alert-content").html(message)
    $('#alert-' + type).find(".close").on('click', function () {
        $('#alert-' + type).fadeOut(500, callback)
    })
    $('#alert-' + type).fadeIn(500, () => {
        setTimeout(() => {
            $('#alert-' + type).fadeOut(500, callback)
        }, 8000)
    })
}