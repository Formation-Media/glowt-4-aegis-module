import { Modal } from 'bootstrap/js/dist/modal';
var grades={
    table:false,
    init:function(){
        grades.table=document.querySelector('.table[data-method="user-grades"]');
        grades.table.addEventListener('table-loaded',grades.watch_deletes);
        this.watch_modal();
    },
    watch_deletes:function(){
        let deletes=grades.table.querySelectorAll('.js-delete-user-grade');
        if(deletes){
            deletes.forEach(function(del){
                del.addEventListener('click',function(){
                    app.confirm(
                        'Delete grade? It will delete all applicable data from all applicable locations.',
                        function(result){
                            if(result){
                                app.toggle_loader();
                                app.ajax(
                                    'm/AEGIS/management/delete_user_grade',
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
        var modal=new Modal(document.querySelector('#modal-add-user-grade'));
        document.querySelector('#modal-add-user-grade .modal-save').addEventListener('click',function(e){
            var form=document.querySelector('#modal-add-user-grade form');
            var data=window.form.validate_form(form,e);
            if(data){
                app.ajax(
                    'm/AEGIS/management/add_user_grade',
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
    grades.init();
}, false);
