<div
    class="flex justify-center"
    x-data="{
        open: false,
        openModal() {
            this.open = true;
        },
        closeModal() {
            this.open = false;
        },
    }"
    {{ "@".$name }}.window="openModal()"
>
    <!-- Modal -->
    <!-- dispatch $name-close-modal to close the modal -->
    <div
        x-show="open"
        {{ "@".$name."-close-modal" }}.window="closeModal()"
        style="display: none"
        x-on:keydown.escape.prevent.stop="closeModal()"
        role="dialog"
        aria-modal="true"
        x-id="['modal-title']"
        :aria-labelledby="$id('modal-title')"
        class="fixed inset-0 overflow-y-auto"
    >
        <!-- Overlay -->
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-80" ></div >

        <!-- Panel -->
        <div
            x-show="open" x-transition
            class="relative flex items-center justify-center min-h-screen p-4"
        >
            <!-- if $clickAwayClose to true allows clicking away to close the modal -->
            <div
                x-on:click.stop
                {!! $clickAwayCloses == true ? '@click.outside="closeModal()"' : '' !!}
                x-trap.noscroll.inert="open"
                class="relative w-full max-w-[1000px] py-4 overflow-y-auto bg-gray-800 border rounded-lg shadow-lg dark:text-gray-100 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between" >
                    <!-- Close X -->
                    <div class="absolute top-0 right-0 block pt-4 pr-4" >
                        <button @click="closeModal()" type="button"
                                class="text-gray-400 transition duration-150 ease-in-out hover:text-gray-500 focus:outline-none focus:text-gray-500"
                                aria-label="Close" >
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg >
                        </button >
                    </div >
                </div >
                <div class="p-4" >
                    {{ $slot }}
                </div >
            </div >
        </div >
    </div >
</div >
