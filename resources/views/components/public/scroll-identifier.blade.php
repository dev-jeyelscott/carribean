@props([
    'label' => 'Scroll',
])

{{--
    Render the control for every public layout.

    JavaScript determines whether the current page has another navigable
    section. This avoids coupling reusable presentation to route names and
    supports new public pages without changing this component.
--}}
<button
    type="button"
    data-public-scroll-identifier
    data-scroll-state="loading"
    class="public-scroll-identifier"
    aria-label="Scroll to the next page section"
    aria-hidden="true">
    <span
        class="public-scroll-identifier__track"
        aria-hidden="true">
        <span class="public-scroll-identifier__dot"></span>
    </span>

    <span class="public-scroll-identifier__label">
        {{ $label }}
    </span>
</button>
