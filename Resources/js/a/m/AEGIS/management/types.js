import Modal from 'bootstrap/js/dist/modal';
var types={
    init:function(){
        this.watch_modal();
        this.watch_modal_show();
        types.add_type_modal=new Modal(document.querySelector('#modal-add-type'));
    },
    watch_modal_show:function(){
        var add_type = document.querySelector('.js-add-type');
        if(add_type){
            add_type.addEventListener('click', function(){
                types.add_type_modal.show();
            })
        }
    },
    watch_modal:function(){
        var add_type = document.getElementById('modal-add-type');
        document.querySelector('#modal-add-type .modal-save').addEventListener('click',function(e){
            var form = add_type.querySelector('form');
            var data = window.form.validate_form(form,e)
            if(data){
                app.ajax(
                    'm/AEGIS/management/add_type',
                    data,
                    function(json){
                        types.add_type_modal.hide();
                        tables.load_table_data(document.querySelector('[data-api="management"]'));
                    },
                    null,
                    function(json){
                        app.toggle_loader();
                    }
                );
            }
        });
    }
}
document.addEventListener('DOMContentLoaded', function() {
    types.init();
}, false);
