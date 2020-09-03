const bootstrap = require('bootstrap')
var suppliers={
    init:function(){
        this.watch_modal();
    },
    watch_modal:function(){
        var modal=new bootstrap.Modal(document.querySelector('#modal-add-competency-supplier'));
        document.querySelector('#modal-add-competency-supplier .modal-save').addEventListener('click',function(e){
            var form=document.querySelector('#modal-add-competency-supplier form');
            if((data=window.form.validate_form(form,e))!==false){
                app.ajax(
                    'm/Aegis/suppliers/add_supplier',
                    data,
                    function(json){
                        modal.hide();
                        tables.load_table_data(document.querySelector('[data-api="suppliers"]'));
                    },
                    function(json){
                        console.log(json);
                    },
                    function(json){
                        app.toggle_loader();
                    }
                );
            }
        });
    }
};
document.addEventListener('DOMContentLoaded', function() {
    suppliers.init();
}, false);
