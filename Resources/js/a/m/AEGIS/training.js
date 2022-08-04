const training = {
    init: () => {
        training.watch_location();
        training.watch_presentation();
    },
    watch_location: () => {
        document.querySelector('[name="location-autocomplete"]')?.addEventListener('autocomplete-add', e => {
            e.valueTarget.value = e.value;
        });
    },
    watch_presentation: () => {
        document.querySelector('[name="presentation-autocomplete"]')?.addEventListener('autocomplete-add', e => {
            e.valueTarget.value = e.value;
        });
    },
};
document.addEventListener('DOMContentLoaded', training.init);
