var add={
    init:function() {
        this.watch_scope_autocomplete();
        this.watch_scope();
    },
    watch_scope_autocomplete:function() {
        document.getElementById('scope-autocomplete').addEventListener('autocomplete-add', function(e) {
            app.toggle_loader();
            app.ajax(
                'm/AEGIS/scopes/add_scope',
                {
                    name:e.value
                },
                function(json) {
                    if (json.data) {
                        document.getElementById('scope').value=json.data.id;
                    }
                },
                null,
                function() {
                    app.toggle_loader();
                }
            );
        });
    },
    watch_scope:function() {
        var reference = document.getElementById('reference-outer');
        var group     = reference.querySelector('div.input-group');
        var prefield  = group.querySelector('span.input-group-text');
        console.log(prefield);
        document.getElementById('scope-autocomplete').addEventListener('autocomplete-select', function(e) {
            app.show_loader();

            app.ajax(
                'm/AEGIS/projects/get_scope_ref',
                {
                    id: e.selection.id
                },
                function(json) {
                    if (json.data) {
                        prefield.innerHTML = json.data.prefix;
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
