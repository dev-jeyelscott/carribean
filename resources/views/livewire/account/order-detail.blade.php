<div>
    <x-orders.detail
        :order="$order"
        :show-account-navigation="true"
        :back-url="route('account.orders.index')"
        back-label="Back to orders"
        :can-confirm-received="$this->canConfirmReceived" />
</div>
