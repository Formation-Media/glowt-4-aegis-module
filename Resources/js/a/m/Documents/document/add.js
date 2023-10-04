var add = {
    category:  null,
    issue:     null,
    populated: false,
    variant:   null,
    init:function() {
        if (window._GET.category) {
            add.category = window._GET.category;
        }
        if (window._GET.project_phase) {
            add.variant = window._GET.project_phase;
        }
        add.issue = document.querySelector('[name="aegis[issue]"]');
        this.watch_category();
        this.watch_create_as();
        this.watch_reference();
        this.watch_select_project();
        this.watch_select_project_variant();
    },
    check_issue:function(reference) {
        if (this.category && this.variant && reference.length > 0) {
            app.show_loader();
            let submit = document.querySelector('[name="document"] [name="add"][type="submit"]');
            app.ajax(
                'm/AEGIS/projects/check-issue',
                {
                    category:        this.category,
                    project_variant: this.variant,
                    reference:       reference,
                },
                function(json) {
                    if (json.status) {
                        add.issue.value = json.data.issue;
                        if (json.data.previous_document) {
                            let approval_process         = '';
                            let approval_process_dom     = document.querySelector('[name="approval_process"]');
                            document.querySelector('[name="aegis[feedback-list-type]"]').value  = json.data.previous_document.document.meta_data.feedback_list_type_id;
                            document.querySelector('[name="aegis[final-feedback-list]"]').value = json.data.previous_document.document.meta_data.final_feedback_list;
                            document.querySelector('[name="category"]').value                   = json.data.previous_document.document.category_id;
                            document.querySelector('[name="description"]').value                = json.data.previous_document.document.description;
                            document.querySelector('[name="link"]').value                       = json.data.previous_document.document.link;
                            document.querySelector('[name="name"]').value                       = json.data.previous_document.document.name;

                            let option = approval_process_dom.querySelector('[value="' + json.data.previous_document.document.process_id + '"]');
                            if (option) {
                                approval_process = json.data.previous_document.document.process_id;
                            }
                            approval_process_dom.value = approval_process;
                        }
                        app.unset_hidden(submit);
                    } else {
                        add.issue.value = json.data.issue;
                        app.set_hidden(submit);
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
        add.populated = true;
    },
    watch_category:function() {
        let category_id = document.querySelector('[name="category"]');
        setInterval(() => {
            if (category_id.value && !add.populated) {
                app.show_loader();
                add.category = category_id.value;
                app.ajax(
                    'm/Documents/categories/get-category',
                    {
                        category: category_id.value,
                    },
                    function(json) {
                        // Call successful
                        if (json.status) {
                            // Action successful
                            add.toggle_feedback_list_type(json.data.prefix);
                            add.get_reference();
                        }
                    },
                    null,
                    function() {
                        // Success or failed, this'll trigger
                        app.hide_loader();
                    }
                );
            }
        }, 1000);
        category_id.addEventListener('change', function() {
            add.category    = this.value;
            add.issue.value = 1;
            add.populated   = false;
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
        reference.addEventListener('change', function() {
            add.get_reference();
            add.check_issue(this.value);
        });
        reference.addEventListener('keyup', function() {
            add.get_reference();
            add.check_issue(this.value);
        });
    },
    watch_select_project:function() {
        var variants = document.getElementById('aegisproject-variant');
        document
            .getElementById('aegisproject-autocomplete')
            .addEventListener('autocomplete-select', function (e) {
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
                            add.variant    = json.data.default_variant;
                        }
                        add.get_reference();
                    },
                    null,
                    function() {
                        app.toggle_loader();
                    }
                );
            });
    },
    watch_select_project_variant:function() {
        document.getElementById('aegisproject-variant').addEventListener(
            'change',
            function(e) {
                add.variant = e.target.value;
                add.get_reference();
            }
        );
    }
};
document.addEventListener('DOMContentLoaded', function() {
    add.init();
}, false);
