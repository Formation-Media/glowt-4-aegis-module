const account = {
    init: () => {
        account.watch_document_tables();
    },
    watch_document_tables: () => {
        document.querySelectorAll('.card-view[data-model="Document"][data-module="Documents"]').forEach(card_view => {
            card_view.querySelector('.project-autocomplete')?.addEventListener('autocomplete-select', e => {
                let html         = '<option value="">Select&hellip;</option>';
                let phase_select = card_view.querySelector('#phase');
                if (e.selection.phases.length) {
                    for (let i = 0; i < e.selection.phases.length; i++) {
                        let phase = e.selection.phases[i];
                        html += '<option value="' + phase.id + '">' + phase.name + '</option>';
                    }
                    form.unset_disabled(phase_select);
                } else {
                    form.set_disabled(phase_select);
                }
                phase_select.innerHTML = html;
            });
        });
    },
};
document.addEventListener('DOMContentLoaded', account.init);
