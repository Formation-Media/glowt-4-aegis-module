import { Modal } from 'bootstrap';
var titles={
    table:false,
    init:function(){
        titles.table=document.querySelector('.table[data-method="job-titles"]');
        titles.table.addEventListener('table-loaded',titles.watch_deletes);
        this.watch_modal();
    },
    watch_deletes:function(){
        let deletes=titles.table.querySelectorAll('.js-delete-job-title');
        if(deletes){
            deletes.forEach(function(del){
                del.addEventListener('click',function(){
                    app.confirm(
                        'Delete title? It will delete all applicable data from all applicable locations.',
                        function(result){
                            if(result){
                                app.toggle_loader();
                                app.ajax(
                                    'm/AEGIS/management/delete_job_title',
                                    {
                                        id:del.dataset.id
                                    },
                                    function(json){
                                        del.parentNode.parentNode.parentNode.remove();
                                    },
                                    function(json){
                                        console.log(json);
                                    },
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
        var modal=new Modal(document.querySelector('#modal-add-job-title'));
        document.querySelector('#modal-add-job-title .modal-save').addEventListener('click',function(e){
            var form=document.querySelector('#modal-add-job-title form');
            if((data=window.form.validate_form(form,e))!==false){
                app.ajax(
                    'm/AEGIS/management/add_job_title',
                    data,
                    function(json){
                        modal.hide();
                        tables.load_table_data(document.querySelector('[data-api="management"]'));
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
    titles.init();
}, false);
