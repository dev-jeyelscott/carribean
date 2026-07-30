<x-layouts.public
    :title="$page?->meta_title ?: 'Menu'"
    :description="$page?->meta_description ?: 'Explore Caribbean-inspired dishes crafted with island soul, fresh ingredients, and coastal ease.'"
    :header-overlay="true">
    <div
        data-menu-page
        class="menu-page-shell min-h-screen bg-canvas">

        <livewire:menu.catalog />

        <livewire:menu.product-modal />
    </div>
</x-layouts.public>