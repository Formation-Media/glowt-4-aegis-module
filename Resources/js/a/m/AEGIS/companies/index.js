var companies = {
    table:false,
    init:function() {
        companies.table = document.querySelector('.table[data-method="companies"]');
        companies.table.addEventListener('table-loaded', companies.watch_deletes);
    },
    watch_deletes:function() {
        let deletes  = companies.table.querySelectorAll('.js-delete-company');
        let restores = companies.table.querySelectorAll('.js-restore-company');
        if (deletes) {
            deletes.forEach(function(del) {
                del.addEventListener('click', function() {
                    app.confirm(
                        'Delete company? It will delete all applicable data from all applicable locations.',
                        function(result) {
                            if (result) {
                                app.toggle_loader();
                                app.ajax(
                                    'm/AEGIS/companies/delete_company',
                                    {
                                        id:del.dataset.id
                                    },
                                    function() {
                                        tables.load_table_data(document.querySelector('[data-controller="companies"]'));
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
        if (restores) {
            restores.forEach(function(restore) {
                restore.addEventListener('click', function() {
                    app.show_loader();
                    app.ajax(
                        'm/AEGIS/companies/restore-company',
                        {
                            id:restore.dataset.id
                        },
                        function() {
                            tables.load_table_data(document.querySelector('[data-controller="companies"]'));
                        },
                        null,
                        function() {
                            app.hide_loader();
                        }
                    );
                });
            });
        }
    },
};
document.addEventListener('DOMContentLoaded', function() {
    companies.init();
}, false);
