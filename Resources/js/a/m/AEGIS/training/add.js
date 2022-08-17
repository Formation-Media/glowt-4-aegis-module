const add = {
    reference: document.getElementById('reference'),
    init: () => {
        add.watch_customer();
    },
    watch_customer: () => {
        document.getElementById('customer-id-autocomplete').addEventListener('autocomplete-add', (e) => {
            app.show_loader();
            app.ajax(
                'm/AEGIS/training/add-customer',
                {
                    customer: e.value
                },
                function(json) {
                    if (json.status) {
                        e.valueTarget.value = json.data.customer.id;
                        add.reference.value = json.data.reference;
                    }
                },
                null,
                function() {
                    app.hide_loader();
                }
            );
        });
        document.getElementById('customer-id-autocomplete').addEventListener('autocomplete-select', (e) => {
            app.show_loader();
            app.ajax(
                'm/AEGIS/training/next-reference',
                {
                    customer: e.selection.id
                },
                function(json) {
                    if (json.status) {
                        document.getElementById('reference').value = json.data;
                    }
                },
                null,
                function() {
                    app.hide_loader();
                }
            );
        });
    },
};
document.addEventListener('DOMContentLoaded', add.init);
