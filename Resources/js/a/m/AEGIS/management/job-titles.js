
var titles = {
    table:false,
    init:function() {
        titles.table = document.querySelector('.table[data-method="job-titles"]');
        titles.table.addEventListener('table-loaded', titles.watch_deletes);
        this.watch_modal();
    },
    watch_deletes:function() {
        let deletes = titles.table.querySelectorAll('.js-delete-job-title');
        if (deletes) {
            deletes.forEach(function(del) {
                del.addEventListener('click', function() {
                    app.confirm(
                        'Delete title? It will delete all applicable data from all applicable locations.',
                        function(result) {
                            if (result) {
                                app.toggle_loader();
                                app.ajax(
                                    'm/AEGIS/management/delete_job_title',
                                    {
                                        id:del.dataset.id
                                    },
                                    function() {
                                        del.parentNode.parentNode.parentNode.remove();
                                    },
                                    null,
                                    function() {
                                        app.toggle_loader();
                                    }
                                );
                            }
                        },
                        'Are you sure you want to delete this item? It will delete all applicable data from all applicable locations.'
                    );
                });
            });
        }
    },
    watch_modal:function() {
        var modal = new bootstrap.Modal(document.querySelector('#modal-add-job-title'));
        document.querySelector('#modal-add-job-title .modal-save').addEventListener('click', function(e) {
            var form = document.querySelector('#modal-add-job-title form');
            var data = window.form.validate(form, e);
            if (data) {
                app.ajax(
                    'm/AEGIS/management/add_job_title',
                    data,
                    function() {
                        modal.hide();
                        tables.load_table_data(document.querySelector('[data-controller="management"]'));
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
    titles.init();
}, false);
