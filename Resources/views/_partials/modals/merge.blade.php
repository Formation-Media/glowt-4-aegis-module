<x-modal
    id="merge"
    save-text="dictionary.merge"
    title="aegis::phrases.merge-customer"
>
    <x-form name="merge">
        <x-field
            label="aegis::phrases.merge-into"
            name="to"
            :options="$customers"
            required
            type="select"
        />
    </x-form>
</x-modal>
