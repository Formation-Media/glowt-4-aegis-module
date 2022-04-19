let aegis = {
    init:function() {
        this.rename_to_mdss();
    },
    rename_to_mdss:function() {
        let name = 'MDSS';
        if (window._GET.module === 'Documents') {
            if (['approval-groups', 'approval-processes', 'categories'].indexOf(window._GET.feature) !== -1) {
                let breadcrumb = document.querySelector('.breadcrumb-item.active').previousElementSibling;
                breadcrumb.firstChild.textContent = name;
            } else {
                let breadcrumb = document.querySelector('.breadcrumb-item').nextElementSibling;
                breadcrumb.firstChild.textContent = name;
            }
        } else if (window._GET.module === null && window._GET.feature === 'management') {
            if (window._GET.page === null) {
                document.querySelectorAll('.card-title').forEach(function(card) {
                    if (card.innerHTML === 'Documents') {
                        card.innerHTML = name;
                        return;
                    }
                });
                document.querySelectorAll('.details dt').forEach(function(detail) {
                    if (detail.innerHTML.trim() === 'Documents') {
                        detail.innerHTML = name;
                        return;
                    }
                });
            } else if (window._GET.page === 'module-settings') {
                document.querySelector('h1').textContent = name;
                document.querySelector('.breadcrumb-item.active').textContent = name;
            }
        }
    }
};
document.addEventListener('DOMContentLoaded', function() {
    aegis.init();
}, false);
