var add={
    init:function(){
        this.watch_scope_autocomplete();
    },
    watch_scope_autocomplete:function(){
        document.getElementById('scope-autocomplete').addEventListener('autocomplete-add', function(e){
            app.toggle_loader();
            app.ajax(
                'm/AEGIS/scopes/add_scope',
                {
                    name:e.value
                },
                function(json){
                    if(json.data){
                        document.getElementById('scope').value=json.data.id;
                    }
                },
                function(json){
                    console.log(json);
                },
                function(){
                    app.toggle_loader();
                }
            );
        })
    }
}
document.addEventListener('DOMContentLoaded', function(){
    add.init();
}, false)
