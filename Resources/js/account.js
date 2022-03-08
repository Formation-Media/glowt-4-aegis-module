let aegis = {
    init:function() {
        this.rename_to_mdss();
    },
    rename_to_mdss:function() {
        if (_GET.module === 'Documents') {
            let breadcrumb = document.querySelector('.breadcrumb-item').nextElementSibling;
            breadcrumb.firstChild.textContent = 'MDSS';
        }
    }
};
document.addEventListener('DOMContentLoaded', function() {
    aegis.init();
}, false);
