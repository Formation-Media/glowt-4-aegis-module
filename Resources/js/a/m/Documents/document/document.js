var document = {
    init:function(){
        this.watch_select_project();
    },
    watch_select_project:function(){
        var variants = document.getElementById('aegisproject-variant');
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
                },
                null,
                function(json){
                    app.toggle_loader();
                }
            );
        });
    }
}
document.addEventListener('DOMContentLoaded', function(){
    document.init();
},false);
