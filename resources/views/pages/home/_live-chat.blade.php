<div
    x-data="landingChat()"
    class="fixed bottom-4 right-4 z-50 sm:bottom-6 sm:right-6"
    aria-live="polite"
>
    <button
        type="button"
        class="inline-flex items-center gap-2 rounded-full border border-emerald-300/40 bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-900/30 transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-400/70"
        x-show="!open"
        x-transition.opacity
        @click="open = true"
        aria-label="Open live chat"
    >
        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-200"></span>
        Live Chat
    </button>

    <section
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-4 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-4 opacity-0"
        class="w-[calc(100vw-2rem)] max-w-sm overflow-hidden rounded-3xl border border-emerald-200/40 bg-[#05160f] text-white shadow-2xl shadow-black/50"
    >
        <header class="flex items-start justify-between border-b border-white/10 px-4 py-3">
            <div>
                <p class="text-sm font-semibold tracking-wide">Neolifeporium Live Support</p>
                <p class="text-xs text-emerald-200/80">Average reply: under 3 mins</p>
            </div>
            <button
                type="button"
                class="rounded-full p-1 text-white/70 transition hover:bg-white/10 hover:text-white"
                @click="open = false"
                aria-label="Close live chat"
            >
                ✕
            </button>
        </header>

        <div class="max-h-80 space-y-3 overflow-y-auto px-4 py-4" x-ref="chatScroll">
            <template x-for="(message, index) in messages" :key="index">
                <div :class="message.from === 'agent' ? 'mr-6' : 'ml-6 text-right'">
                    <div
                        class="inline-block rounded-2xl px-3 py-2 text-sm leading-relaxed"
                        :class="message.from === 'agent' ? 'bg-white/10 text-white' : 'bg-emerald-500 text-white'"
                        x-text="message.text"
                    ></div>
                </div>
            </template>

            <div x-show="isTyping" class="mr-6">
                <div class="inline-flex items-center gap-1 rounded-2xl bg-white/10 px-3 py-2 text-xs text-white/70">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white/70"></span>
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white/70 [animation-delay:120ms]"></span>
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white/70 [animation-delay:220ms]"></span>
                </div>
            </div>
        </div>

        <div class="border-t border-white/10 px-4 py-3">
            <p class="mb-2 text-[11px] uppercase tracking-[0.14em] text-emerald-200/75">Quick questions</p>
            <div class="flex flex-wrap gap-2">
                <template x-for="(prompt, index) in quickPrompts" :key="index">
                    <button
                        type="button"
                        class="rounded-full border border-white/20 bg-white/5 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-white/15"
                        @click="sendPrompt(prompt)"
                        x-text="prompt"
                    ></button>
                </template>
            </div>
        </div>
    </section>
</div>
