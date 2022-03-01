let add = {
    init:function() {
        this.watch_company();
        this.watch_scope();
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
                            next.value       = json.data.next;
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
    watch_scope:function() {
        document.getElementById('scope-autocomplete').addEventListener('autocomplete-add', function(e) {
            app.show_loader();
            app.ajax(
                'm/AEGIS/scopes/add_scope',
                {
                    name:e.value
                },
                function(json) {
                    if (json.data) {
                        document.getElementById('scope').value = json.data.id;
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
