const project = {
    init: () => {
        project.watch_add_document();
    },
    watch_add_document: () => {
        let modal_dom = document.querySelector('#modal-add-document');

        let modal = new bootstrap.Modal(modal_dom);

        document.querySelector('.js-add-document').addEventListener('click', () => {
            modal.show();
        });

        modal_dom.querySelector('[name="phase"]').addEventListener('change', (e) => {
            if (e.target.value) {
                app.show_loader();
                window.location = '/a/m/Documents/document/add?project_phase=' + e.target.value;
            }
        });
    },
};
document.addEventListener('DOMContentLoaded', project.init);
