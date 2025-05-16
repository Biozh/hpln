import $ from 'jquery';

export const disableForm = (modal) => {
    modal.find(":input").prop("disabled", true)
    modal.find(".btn").addClass("disabled");
};

export const showAlert = (type, message, callback = () => { }) => {
    const baseAlert = $('#alert-' + type);
    if (!baseAlert.length) return;

    const uniqueId = 'alert-' + type + '-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
    const alert = baseAlert.clone();

    alert
        .attr('id', uniqueId)
        .fadeIn(500)
        .find('.alert-content').html(message);

    // Gérer la fermeture manuelle
    alert.find('.close').on('click', () => {
        alert.fadeOut(300, () => {
            alert.remove();
            callback();
        });
    });

    // Fermeture automatique après 8s
    setTimeout(() => {
        alert.fadeOut(300, () => {
            alert.remove();
            callback();
        });
    }, 8000);

    // Injecter dans un conteneur dédié (si présent), sinon dans le body
    const container = $('#alert-container');
    if (container.length) {
        container.append(alert);
    } else {
        $('body').append(alert);
    }
};
