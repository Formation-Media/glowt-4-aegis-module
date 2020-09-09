const bootstrap = require('bootstrap')
var companies={
    init:function(){
        this.watch_modal();
    },
    watch_modal:function(){
        var modal=new bootstrap.Modal(document.querySelector('#modal-add-competency-company'));
        document.querySelector('#modal-add-competency-company .modal-save').addEventListener('click',function(e){
            var form=document.querySelector('#modal-add-competency-company form');
            if((data=window.form.validate_form(form,e))!==false){
                app.ajax(
                    'm/AEGIS/companies/add_company',
                    data,
                    function(json){
                        modal.hide();
                        tables.load_table_data(document.querySelector('[data-api="companies"]'));
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
    companies.init();
}, false);
