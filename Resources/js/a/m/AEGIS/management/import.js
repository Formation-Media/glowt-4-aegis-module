
const importer = {
    ol:       null,
    progress: null,
    stream:   null,
    init: function() {
        importer.ol       = document.querySelector('ol.js-messages');
        importer.progress = document.querySelector('.progress-bar');
        importer.check_for_updates();
        importer.start_stream();
    },
    check_for_updates: () => {
        setInterval(() => {
            app.ajax(
                'm/AEGIS/management/check-import-data',
                {},
                function(json) {
                    if (json.status) {
                        console.log(json.data);
                        importer.set_percentage(json.data.percentage);
                        if (json.data.messages !== null) {
                            for (let i = 0; i < json.data.messages.length; i++) {
                                importer.set_message(json.data.messages[i]);
                            }
                        }
                        if (json.data.redirect) {
                            window.location = json.data.redirect;
                        }
                    }
                }
            );
        }, 1000);
    },
    set_message: (message) => {
        importer.ol.innerHTML = '<li>' + message + '</li>' + importer.ol.innerHTML;
    },
    set_percentage: (percentage) => {
        importer.progress.ariaValueNow = percentage;
        importer.progress.style.width  = percentage + '%';
        importer.progress.innerText    = percentage + '%';
    },
    start_stream: () => {
        importer.stream = app.stream(
            'm/AEGIS/management/import',
            {},
            function(json) {
                if (json) {
                    if (json.message) {
                        importer.set_message(json.message);
                    }
                    importer.set_percentage(json.percentage);
                }
            }
        );
    }
};
document.addEventListener('DOMContentLoaded', importer.init);
