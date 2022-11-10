window.importer = {
    stream: null,
    init: function() {
        let ol       = document.querySelector('ol.js-messages');
        let progress = document.querySelector('.progress-bar');
        this.stream = app.stream(
            'm/AEGIS/management/import',
            {},
            function(json) {
                if (json) {
                    if (json.message) {
                        ol.innerHTML = '<li>' + json.message + '</li>' + ol.innerHTML;
                    }
                    progress.ariaValueNow = json.percentage;
                    progress.style.width  = json.percentage + '%';
                    progress.innerText    = json.percentage + '%';
                    if (json.redirect) {
                        window.location = json.redirect;
                    }
                }
            }
        );
    }
};
document.addEventListener('DOMContentLoaded', function() {
    window.importer.init();
}, false);
