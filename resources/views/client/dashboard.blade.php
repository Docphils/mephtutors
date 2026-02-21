```blade
<x-app-layout>
    <livewire:client.dashboard-controller />

    <div class="container mx-auto mt-8">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <livewire:client.crm-manager />
            </div>
        </div>
    </div>

</x-app-layout>
@include('layouts.footer')


<x-app-layout>
    <livewire:client.dashboard-controller />

    <div class="container mx-auto mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <livewire:client.crm-manager />
            </div>
            <div>
                <livewire:client.tutor-requests-manager />
            </div>
        </div>
    </div>

</x-app-layout>
@include('layouts.footer')
