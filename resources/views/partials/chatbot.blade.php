<div x-data="chatbot()" x-cloak class="relative z-50">
    {{-- Floating Trigger Button --}}
    <div class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-50">
        <button type="button" @click="toggle()" aria-label="Buka asisten virtual"
                class="inline-flex items-center justify-center gap-2.5 rounded-full bg-forest-800 px-5 py-3 text-xs sm:text-sm font-bold text-white shadow-xl shadow-forest-950/30 ring-1 ring-white/20 transition-all hover:bg-forest-900 active:scale-95">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            <x-icon name="bot" class="h-4 w-4 text-sand-300" />
            <span x-text="open ? 'Tutup Asisten' : 'Tanya Asisten'"></span>
        </button>
    </div>

    {{-- Chat Drawer / Dialog --}}
    <div x-show="open" x-transition.origin.bottom.right
         class="fixed inset-x-3 bottom-20 top-20 sm:top-auto sm:bottom-20 sm:right-6 sm:left-auto sm:w-[22rem] sm:h-[32rem] flex flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/10 z-50">
        {{-- Header --}}
        <div class="flex items-center justify-between bg-forest-900 px-4 py-3 text-white">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('homestay.name') }}" class="h-8 w-8 rounded-full object-cover ring-1 ring-white/30">
                <div>
                    <p class="text-xs font-bold leading-tight">Asisten {{ config('homestay.name') }}</p>
                    <p class="text-[10px] text-forest-200">Info kamar & ketersediaan</p>
                </div>
            </div>
            <button type="button" @click="open = false" class="rounded-lg p-1.5 text-white/80 hover:bg-white/10 hover:text-white" aria-label="Tutup">
                <x-icon name="x" class="h-4 w-4" />
            </button>
        </div>

        {{-- Messages Container --}}
        <div x-ref="box" class="flex-1 space-y-3 overflow-y-auto bg-warm-50 p-3 sm:p-4">
            <template x-for="(m, i) in messages" :key="i">
                <div :class="m.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div class="max-w-[85%] space-y-2">
                        <div :class="m.role === 'user' ? 'bg-forest-800 text-white' : 'bg-white text-slate-800 ring-1 ring-slate-200/80 shadow-sm'"
                             class="rounded-2xl px-3.5 py-2.5 text-xs sm:text-sm leading-relaxed" x-html="format(m.content)"></div>
                        <template x-for="(a, j) in (m.actions || [])" :key="j">
                            <a :href="a.url" target="_blank" rel="noopener"
                               class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition">
                                <x-icon name="message-circle" class="h-3.5 w-3.5" />
                                <span x-text="a.label"></span>
                            </a>
                        </template>
                    </div>
                </div>
            </template>
            <div x-show="loading" class="flex justify-start">
                <div class="rounded-2xl bg-white px-3.5 py-2 text-xs text-slate-400 ring-1 ring-slate-200 shadow-sm">
                    Mengetik…
                </div>
            </div>
        </div>

        {{-- Quick prompts --}}
        <div class="flex flex-wrap gap-1.5 border-t border-slate-100 bg-white px-3 py-2" x-show="messages.length < 3">
            <template x-for="q in quick" :key="q">
                <button type="button" @click="send(q)"
                        class="rounded-full bg-forest-50 border border-forest-100 px-2.5 py-1 text-[11px] font-semibold text-forest-800 hover:bg-forest-100 transition"
                        x-text="q"></button>
            </template>
        </div>

        {{-- Input Form --}}
        <form @submit.prevent="send()" class="flex items-center gap-2 border-t border-slate-100 bg-white p-2.5">
            <input x-model="input" type="text" maxlength="600" placeholder="Tulis pertanyaan…" :disabled="loading"
                   class="flex-1 rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs sm:text-sm focus:border-forest-600 focus:bg-white focus:ring-forest-600">
            <button type="submit" :disabled="loading || !input.trim()"
                    class="inline-flex items-center justify-center rounded-xl bg-forest-800 px-4 py-2 text-xs font-bold text-white transition hover:bg-forest-900 disabled:opacity-40">
                Kirim
            </button>
        </form>
    </div>
</div>

<script>
function chatbot() {
    return {
        open: false, input: '', loading: false,
        quick: ['Harga kamar', 'Ketersediaan akhir pekan', 'Cara pesan'],
        messages: [{
            role: 'assistant', actions: [],
            content: 'Halo! Saya asisten virtual {{ config('homestay.name') }}. Saya bisa bantu info kamar, harga, dan ketersediaan. Untuk memesan, nanti saya arahkan langsung ke WhatsApp pengelola.'
        }],
        toggle() { this.open = !this.open; if (this.open) this.scroll(); },
        scroll() { this.$nextTick(() => { const b = this.$refs.box; if (b) b.scrollTop = b.scrollHeight; }); },
        format(t) {
            const e = String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return e.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
        },
        async send(text) {
            const msg = (text ?? this.input).trim();
            if (!msg || this.loading) return;
            this.messages.push({ role: 'user', content: msg, actions: [] });
            this.input = ''; this.loading = true; this.scroll();
            const history = this.messages.slice(0, -1).filter(m => m.content).slice(-10)
                .map(m => ({ role: m.role, content: m.content }));
            try {
                const res = await fetch(@json(route('chatbot')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ message: msg, history })
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                this.messages.push({ role: 'assistant', content: data.reply, actions: data.actions || [] });
            } catch (e) {
                this.messages.push({
                    role: 'assistant',
                    content: 'Maaf, sedang ada gangguan. Silakan langsung hubungi pengelola via WhatsApp.',
                    actions: [{ type: 'whatsapp', label: 'Chat WhatsApp Pengelola', url: @json(\App\Services\WhatsAppLink::general()) }]
                });
            }
            this.loading = false; this.scroll();
        }
    };
}
</script>
