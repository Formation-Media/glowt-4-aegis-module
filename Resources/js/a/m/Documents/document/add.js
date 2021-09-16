var add = {
    init:function(){
        this.watch_select_project();
        this.watch_select_project_variant();
    },
    watch_select_project:function(){
        var variants = document.getElementById('aegisproject-variant');
        var reference = document.getElementById('aegisreference');
        document.getElementById('aegisproject').addEventListener('change',
        function(e){
            app.show_loader()
            app.ajax(
                'm/AEGIS/projects/get_project_variants',
                {
                    project: e.target.value
                },
                function(json){
                    if(json.data.variants){
                        variants.innerHTML ='<option value>Select...</option>';
                        for (const [key,value] of Object.entries(json.data.variants)){
                            variants.innerHTML += '<option value='+key+'>'+value+'</option>';
                        }
                    }
                    if(json.data.default_variant){
                        variants.value = json.data.default_variant;
                    }
                    if(json.data.reference){
                        reference.value = json.data.reference;
                    }
                },
                null,
                function(json){
                    app.toggle_loader();
                }
            );
        });
    },
    watch_select_project_variant:function(){
        var reference = document.getElementById('aegisreference');
        document.getElementById('aegisproject-variant').addEventListener('change',
            function(e){
                app.show_loader();
                app.ajax(
                    'm/AEGIS/projects/get_variant_ref',
                    {
                        project_variant : e.target.value
                    },
                    function(json){
                        if(json.data.ref){
                            reference.value = json.data.ref;
                        } else {
                            reference.value = '';
                        }
                    },
                    null,
                    function(json){
                        app.hide_loader();
                    }
                )
            }
        )
    },
}
document.addEventListener('DOMContentLoaded', function(){
        add.init();
},false);
