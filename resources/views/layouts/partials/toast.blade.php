{{-- Toast global: dengarkan event Livewire `notify` & flash session --}}
<div x-data="{
        toasts: [],
        add(detail) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message: detail.message, type: detail.type || 'success' });
            setTimeout(() => this.remove(id), 3500);
        },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id); }
    }"
    x-on:notify.window="add($event.detail)"
    class="fixed z-50 space-y-2 top-20 right-4 w-80">

    @if (session('success'))
        <div x-init="add({ message: @js(session('success')), type: 'success' })"></div>
    @endif
    @if (session('error'))
        <div x-init="add({ message: @js(session('error')), type: 'error' })"></div>
    @endif

    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition.opacity
            class="flex items-start gap-3 p-4 text-sm text-white rounded-lg shadow-lg"
            :class="{
                'bg-emerald-600': toast.type === 'success',
                'bg-red-600': toast.type === 'error',
                'bg-amber-600': toast.type === 'warning',
                'bg-stone-700': toast.type === 'info'
            }">
            <span x-text="toast.message" class="flex-1"></span>
            <button @click="remove(toast.id)" class="text-white/80 hover:text-white">&times;</button>
        </div>
    </template>
</div>
