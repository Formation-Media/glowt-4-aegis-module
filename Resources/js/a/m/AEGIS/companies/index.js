var companies={
    table:false,
    init:function(){
        companies.table=document.querySelector('.table[data-method="companies"]');
        companies.table.addEventListener('table-loaded',companies.watch_deletes);
        this.watch_modal();
    },
    watch_deletes:function(){
        let deletes=companies.table.querySelectorAll('.js-delete-company');
        if(deletes){
            deletes.forEach(function(del){
                del.addEventListener('click',function(){
                    app.confirm(
                        'Delete company? It will delete all applicable data from all applicable locations.',
                        function(result){
                            if(result){
                                app.toggle_loader();
                                app.ajax(
                                    'm/AEGIS/companies/delete_company',
                                    {
                                        id:del.dataset.id
                                    },
                                    function(json){
                                        del.parentNode.parentNode.parentNode.remove();
                                    },
                                    null,
                                    function(json){
                                        app.toggle_loader();
                                    }
                                );
                            }
                        },
                        'Are you sure you want to delete this item? It will delete all applicable data from all applicable locations.'
                    )
                })
            });
        }
    },
    watch_modal:function(){
        var modal = new bootstrap.Modal(document.querySelector('#modal-add-competency-company'));
        document.querySelector('#modal-add-competency-company .modal-save').addEventListener('click',function(e){
            var form = document.querySelector('#modal-add-competency-company form');
            let data = window.form.validate_form(form,e);
            if(data){
                app.ajax(
                    'm/AEGIS/companies/add_company',
                    data,
                    function(json){
                        modal.hide();
                        tables.load_table_data(document.querySelector('[data-controller="companies"]'));
                    },
                    null,
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
