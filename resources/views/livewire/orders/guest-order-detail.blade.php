<div>
    <x-orders.detail
        :order="$order"
        :back-url="route('menu')"
        back-label="Return to menu"
        :can-confirm-received="$this->canConfirmReceived" />
</div>
