let doc = {
    category: null,
    issue:    null,
    variant:  null,
    init:function() {
        doc.issue   = document.querySelector('[name="aegis[issue]"]');
        doc.variant = document.querySelector('[name="aegis[project_variant]"]')?.value;
        this.watch_category();
        this.watch_create_as();
        this.watch_reference();
        this.watch_select_project();
        this.watch_select_project_variant();
    },
    get_issue:function(reference) {
        if (this.category && this.variant && reference.length > 0) {
            app.show_loader();
            app.ajax(
                'm/AEGIS/projects/get-issue',
                {
                    category:        this.category,
                    project_variant: this.variant,
                    reference:       reference,
                },
                function(json) {
                    if (json.status) {
                        doc.issue.value = json.data;
                    }
                },
                null,
                function() {
                    app.hide_loader();
                }
            );
        }
    },
    get_reference:function() {
        let reference = document.getElementById('aegisreference');
        if (this.category && this.variant) {
            app.show_loader();
            app.ajax(
                'm/AEGIS/projects/get_variant_ref',
                {
                    category:        this.category,
                    project_variant: this.variant,
                },
                function(json) {
                    if (json.data.reference) {
                        reference.value = json.data.reference;
                        previousSibling(reference).innerHTML = json.data.prefix;
                    } else {
                        reference.value = '';
                    }
                },
                null,
                function() {
                    app.hide_loader();
                }
            );
        }
    },
    toggle_feedback_list_type:function(prefix) {
        let feedback_list_type  = document.querySelector('[name="aegis[feedback-list-type]"]');
        let final_feedback_list = document.querySelector('[name="aegis[final-feedback-list]"]');
        if (prefix === 'FBL') {
            app.unset_hidden(feedback_list_type.parentNode);
            app.unset_hidden(final_feedback_list.parentNode);
            form.set_required(feedback_list_type);
            form.set_required(final_feedback_list);
        } else {
            form.unset_required(feedback_list_type);
            form.unset_required(final_feedback_list);
            app.set_hidden(feedback_list_type.parentNode);
            app.set_hidden(final_feedback_list.parentNode);
        }
    },
    watch_category:function() {
        let category_id = document.querySelector('[name="category"]');
        if (category_id?.value) {
            app.show_loader();
            app.ajax(
                'm/Documents/categories/get-category',
                {
                    category: category_id.value,
                },
                function(json) {
                    // Call successful
                    if (json.status) {
                        doc.toggle_feedback_list_type(json.data.prefix);
                    }
                },
                null,
                function() {
                    // Success or failed, this'll trigger
                    app.hide_loader();
                }
            );
        }
        category_id?.addEventListener('change', function() {
            doc.category    = this.value;
            doc.issue.value = 1;app.show_loader();
            app.ajax(
                'm/Documents/categories/get-category',
                {
                    category: this.value,
                },
                function(json) {
                    // Call successful
                    if (json.status) {
                        doc.toggle_feedback_list_type(json.data.prefix);
                        doc.get_reference();
                    }
                },
                null,
                function() {
                    // Success or failed, this'll trigger
                    app.hide_loader();
                }
            );
        });
    },
    watch_create_as:function() {
        let role = document.querySelector('select[name="aegis[author-role]"]');
        document.querySelector('select[name="created_by"]')?.addEventListener('change', function() {
            let select = '<option value="">Select&hellip;</option>';
            if (this.value) {
                app.show_loader();
                app.ajax(
                    'm/AEGIS/users/get_roles',
                    {
                        id: this.value
                    },
                    function(json) {
                        // Call successful
                        if (json.status) {
                            // Action successful
                            if (json.data.length) {
                                for (let i = 0; i < json.data.length; i++) {
                                    let role  = json.data[i];
                                    select   += '<option value="' + role.id + '">' + role.name + '</option>';
                                }
                            }
                            role.innerHTML = select;
                        }
                    },
                    function() {
                        role.innerHTML = select;
                    },
                    function() {
                        app.hide_loader();
                    }
                );
            } else {
                role.innerHTML = select;
            }
        });
    },
    watch_reference: function() {
        let reference = document.querySelector('[name="aegis[reference]"]');
        reference?.addEventListener('change', function() {
            doc.get_issue(this.value);
        });
        reference?.addEventListener('keyup', function() {
            doc.get_issue(this.value);
        });
    },
    watch_select_project:function() {
        let variants = document.getElementById('aegisproject-variant');
        let project_autocomplete = document.getElementById('aegisproject-autocomplete');
        if (project_autocomplete) {
            project_autocomplete.addEventListener('autocomplete-select', function (e) {
                app.show_loader();
                app.ajax(
                    'm/AEGIS/projects/get_project_variants',
                    {
                        project: e.selection.id
                    },
                    function(json) {
                        if (json.data.variants) {
                            variants.innerHTML = '<option value>Select...</option>';
                            for (const [key, value] of Object.entries(json.data.variants)) {
                                variants.innerHTML += '<option value=' + key + '>' + value + '</option>';
                            }
                        }
                        if (json.data.default_variant) {
                            variants.value = json.data.default_variant;
                            doc.variant    = json.data.default_variant;
                        }
                        doc.get_reference();
                    },
                    null,
                    function() {
                        app.toggle_loader();
                    }
                );
            });
        }
    },
    watch_select_project_variant:function() {
        document.getElementById('aegisproject-variant')?.addEventListener(
            'change',
            function(e) {
                doc.variant = e.target.value;
                doc.get_reference();
            }
        );
    }
};
document.addEventListener('DOMContentLoaded', function() {
    doc.init();
}, false);
