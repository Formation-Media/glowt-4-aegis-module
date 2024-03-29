var project_types = {
    init:function() {
        this.watch_modal();
        this.watch_modal_show();
        project_types.add_type_modal = new bootstrap.Modal(document.querySelector('#modal-add-type'));
    },
    watch_modal_show:function() {
        var add_type = document.querySelector('.js-add-type');
        if (add_type) {
            add_type.addEventListener('click', function() {
                project_types.add_type_modal.show();
            });
        }
    },
    watch_modal:function() {
        var add_type = document.getElementById('modal-add-type');
        document.querySelector('#modal-add-type .modal-save').addEventListener('click', function(e) {
            var form = add_type.querySelector('form');
            var data = window.form.validate(form, e);
            if (data) {
                app.ajax(
                    'm/AEGIS/management/add_type',
                    data,
                    function() {
                        project_types.add_type_modal.hide();
                        card_views.load_card_data(document.querySelector('[data-model="Type"]'));
                    },
                    null,
                    function() {
                        app.toggle_loader();
                    }
                );
            }
        });
    }
};
document.addEventListener('DOMContentLoaded', function() {
    project_types.init();
}, false);
