const add = {
    reference: null,
    next:      null,
    prefix:    null,
    types:     null,
    init: () => {
        add.reference = document.querySelector('.reference-outer');
        add.next      = add.reference.querySelector('input');
        add.prefix    = add.reference.querySelector('.input-group-text');
        add.types     = document.querySelector('[name="type"]');
        add.watch_company();
        add.watch_customer();
        add.watch_type();
    },
    get_details:function(value, set_previous = false) {
        if (value) {
            app.show_loader();
            app.ajax(
                'm/AEGIS/companies/get-details',
                {
                    company_id: value
                },
                function(json) {
                    if (json.status) {
                        let html             = '<option value="">Select&hellip;</option>';
                        add.prefix.innerHTML = json.data.prefix + '/';
                        if (json.data.types) {
                            for (let id in json.data.types) {
                                let type = json.data.types[id];
                                if (typeof type === 'string') {
                                    html += '<option value="' + id + '">' + type + '</option>';
                                } else {
                                    html += '<optgroup label="' + id + '">';
                                    for (let child in type) {
                                        html += '<option value="' + child + '">' + type[child] + '</option>';
                                    }
                                    html += '</optgroup>';
                                }
                            }
                            add.types.disabled = false;
                        } else {
                            add.types.disabled = true;
                        }
                        add.types.innerHTML = html;
                        if (set_previous) {
                            add.types.value = window.localStorage.getItem('aegis.projects.add.type');
                        }
                    }
                },
                null,
                function() {
                    app.hide_loader();
                },
                '.project .card-body'
            );
        }
    },
    watch_company:function() {
        let company = document.querySelector('[name="company_id"]');
        add.get_details(company.value, true);
        company.addEventListener('change', function() {
            add.next.value       = '';
            add.prefix.innerHTML = '&hellip;/';
            add.get_details(this.value);
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
    },
    watch_type: () => {
        add.types.addEventListener('change', (e) => {
            window.localStorage.setItem('aegis.projects.add.type', e.target.value);
        });
    },
};
document.addEventListener('DOMContentLoaded', add.init);
