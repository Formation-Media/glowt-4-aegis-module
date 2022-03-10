
<x-card class="shadow">
    <div class="grid-md-2">
        <x-field
            label="aegis::_settings.project-limit.title"
            name="project[character-limit]"
            note="aegis::_settings.project-limit.description"
            required
            type="number"
            :value="$settings['project']['character-limit']"
        />
    </div>
</x-card>
