var add = {
    init:function(){
        this.watch_select_project();
    },
    watch_select_project:function(){
        var variants = document.getElementById('aegisproject-variant');
        document.getElementById('aegisproject').addEventListener('change',
        function(e){
            //ajax to get an array of potential options
            console.log(e.target.value);
            app.show_loader()
            app.ajax(
                'm/AEGIS/projects/get_project_variants',
                {
                    project: e.target.value
                },
                function(json){
                    console.log(json);
                    if(json.data.variants){
                        variants.innerHTML ='<option value>Select...</option>';
                        for (const [key,value] of Object.entries(json.data.variants)){
                            console.log(key);
                            console.log(value);
                            variants.innerHTML += '<option value='+key+'>'+value+'</option>';
                        }
                    }
                },
                function(json){
                    console.log(json);
                },
                function(json){
                    app.toggle_loader();
                }
            );
        });
    }
}
document.addEventListener('DOMContentLoaded', function(){
    add.init();
},
false
);
