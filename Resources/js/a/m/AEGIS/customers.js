const customers = {
    items_view: null,
    modal:      null,
    modal_dom:  null,
    to_merge:   null,
    init: () => {
        customers.items_view = document.querySelector('.table[data-controller="customers"]');
        if (customers.items_view) {
            customers.items_view.addEventListener('table-loaded', customers.watch_table_merge);
        }
        let page_merge = document.querySelectorAll('.page-menu .js-merge');
        if (page_merge) {
            customers.watch_merge(page_merge);
        }
    },
    watch_table_merge: () => {
        customers.watch_merge(customers.items_view.querySelectorAll('.js-merge'));
    },
    watch_merge:function (mergers) {
        if (mergers.length) {
            customers.modal_dom = document.querySelector('#modal-merge');

            customers.modal = new bootstrap.Modal(customers.modal_dom);

            mergers.forEach(merge => {
                merge.onclick = function () {
                    customers.to_merge = merge.dataset.id;
                    customers.modal.show();
                };
            });

            customers.modal_dom.querySelector('.modal-save').onclick = function () {
                let data = form.validate(customers.modal_dom.querySelector('form'));
                if (data) {
                    data.append('from', customers.to_merge);
                    app.ajax(
                        'm/AEGIS/customers/merge',
                        data,
                        function(json) {
                            if (json.status) {
                                window.location = '/a/m/AEGIS/customers/customer/' + json.data;
                            } else {
                                app.hide_loader();
                            }
                        },
                        function() {
                            app.hide_loader();
                        }
                    );
                }
            };
        }
    }
};
document.addEventListener('DOMContentLoaded', customers.init);
