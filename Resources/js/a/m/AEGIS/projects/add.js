let add = {
    init:function() {
        this.watch_company();
        this.watch_customer();
    },
    watch_company:function() {
        let reference = document.querySelector('.reference-outer');
        let next      = reference.querySelector('input');
        let prefix    = reference.querySelector('.input-group-text');
        document.querySelector('[name="company_id"]').addEventListener('change', function() {
            next.value       = '';
            prefix.innerHTML = '&hellip;/';
            if (this.value) {
                app.show_loader();
                app.ajax(
                    'm/AEGIS/companies/get-reference',
                    {
                        company_id: this.value
                    },
                    function(json) {
                        if (json.status) {
                            prefix.innerHTML = json.data.prefix + '/';
                        }
                    },
                    null,
                    function() {
                        app.hide_loader();
                    },
                    '.project .card-body'
                );
            }
        });
    },
    watch_customer:function() {
        document.getElementById('customer-autocomplete').addEventListener('autocomplete-add', function(e) {
            app.show_loader();
            app.ajax(
                'm/AEGIS/customers/add_customer',
                {
                    name:e.value
                },
                function(json) {
                    if (json.data) {
                        document.getElementById('customer').value = json.data.id;
                    }
                },
                null,
                function() {
                    app.hide_loader();
                }
            );
        });
    }
};
document.addEventListener('DOMContentLoaded', function() {
    add.init();
}, false);
