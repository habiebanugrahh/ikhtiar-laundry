@extends('layouts.panel')

@section('title', 'Transaksi Baru')
@section('page_title', 'Transaksi Baru')

@section('panel')
@php
    $oldItems = old('items');

    // Data awal untuk komponen Alpine. Saat validasi gagal, isian lama dikembalikan.
    $config = [
        'services' => $services->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'unit' => $s->unit,
            'price' => $s->price,
        ])->values(),
        'customer' => $customer ? $customer->only(['id', 'name', 'phone', 'address']) : null,
        'items' => is_array($oldItems)
            ? array_values(array_map(fn ($i) => [
                'service_id' => $i['service_id'] ?? '',
                'quantity' => $i['quantity'] ?? '',
            ], $oldItems))
            : [['service_id' => '', 'quantity' => '']],
        'isExpress' => (bool) old('is_express'),
        'expressFee' => old('express_fee', \App\Models\Transaction::DEFAULT_EXPRESS_FEE),
        'method' => old('payment_method', 'cash'),
        'paymentInput' => old('payment_input', ''),
        'searchUrl' => route('customers.search'),
        'quickStoreUrl' => route('customers.quick'),
    ];
@endphp

@if ($services->isEmpty())
    <div class="rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-brand-100">
        <p class="font-semibold text-ink-700">Belum ada layanan aktif</p>
        <p class="mt-1 text-sm text-ink-700/70">Minta Owner mengaktifkan atau menambah layanan di menu Pengaturan Tarif.</p>
    </div>
@else
    @if ($errors->any())
        <div role="alert" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-800 ring-1 ring-red-200">
            <p class="font-semibold">Transaksi belum disimpan. Periksa isian berikut:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('transactions.store') }}"
        x-data="transactionForm(@js($config))"
        x-on:submit="submitting = true"
        novalidate
    >
        @csrf

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">

            {{-- Kolom kiri: pelanggan, layanan, catatan --}}
            <div class="space-y-6">

                {{-- Pelanggan --}}
                <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
                    <h2 class="font-semibold text-ink-700">Pelanggan</h2>
                    <input type="hidden" name="customer_id" :value="customer ? customer.id : ''">

                    <template x-if="customer">
                        <div class="mt-3 flex items-start justify-between gap-3 rounded-xl bg-cyan-soft p-4">
                            <div class="min-w-0">
                                <p class="font-semibold text-ink-700" x-text="customer.name"></p>
                                <p class="text-sm text-ink-700/70" x-text="customer.phone"></p>
                                <p class="text-sm text-ink-700/70" x-text="customer.address || ''"></p>
                            </div>
                            <button type="button" x-on:click="clearCustomer()" class="shrink-0 text-sm font-medium text-brand-600 hover:text-ink-700">Ganti</button>
                        </div>
                    </template>

                    <div x-show="! customer" class="mt-3">
                        <div class="relative" x-on:click.outside="showResults = false">
                            <label for="customer-search" class="sr-only">Cari pelanggan</label>
                            <input
                                id="customer-search"
                                type="search"
                                x-model="search"
                                autocomplete="off"
                                placeholder="Cari nama atau nomor telepon"
                                x-on:focus="openResults()"
                                x-on:input.debounce.250ms="searchCustomers()"
                                x-on:keydown.enter.prevent
                                class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                            >

                            <ul x-show="showResults" x-cloak class="absolute z-10 mt-1 max-h-64 w-full overflow-auto rounded-xl bg-white py-1 shadow-lg ring-1 ring-brand-100">
                                <template x-for="c in results" :key="c.id">
                                    <li>
                                        <button type="button" x-on:click="selectCustomer(c)" class="block w-full px-4 py-2 text-left text-sm hover:bg-cyan-soft">
                                            <span class="font-medium text-ink-700" x-text="c.name"></span>
                                            <span class="ml-2 text-ink-700/60" x-text="c.phone"></span>
                                        </button>
                                    </li>
                                </template>
                                <li x-show="results.length === 0 && ! searching" class="px-4 py-3 text-sm text-ink-700/60">Pelanggan tidak ditemukan.</li>
                            </ul>
                        </div>

                        <button
                            type="button"
                            x-on:click="showNew = ! showNew"
                            class="mt-3 text-sm font-medium text-brand-600 hover:text-ink-700"
                            x-text="showNew ? 'Batal tambah pelanggan baru' : 'Pelanggan baru? Tambahkan di sini'"
                        ></button>

                        <div x-show="showNew" x-cloak class="mt-3 space-y-3 rounded-xl border border-brand-100 p-4">
                            <div>
                                <label for="new-name" class="block text-sm font-medium text-ink-700">Nama pelanggan</label>
                                <input id="new-name" type="text" x-model="newCustomer.name" maxlength="100" x-on:keydown.enter.prevent class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30">
                            </div>
                            <div>
                                <label for="new-phone" class="block text-sm font-medium text-ink-700">Nomor telepon atau WhatsApp</label>
                                <input id="new-phone" type="tel" x-model="newCustomer.phone" maxlength="20" inputmode="tel" x-on:keydown.enter.prevent class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30">
                            </div>
                            <div>
                                <label for="new-address" class="block text-sm font-medium text-ink-700">Alamat (boleh dikosongkan)</label>
                                <input id="new-address" type="text" x-model="newCustomer.address" maxlength="500" x-on:keydown.enter.prevent class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30">
                            </div>

                            <p x-show="newError" x-cloak x-text="newError" class="text-sm text-red-600"></p>

                            <button
                                type="button"
                                x-on:click="saveNewCustomer()"
                                x-bind:disabled="savingNew"
                                class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-ink-700 disabled:opacity-60"
                            >
                                Simpan pelanggan
                            </button>
                        </div>
                    </div>
                </section>

                {{-- Layanan --}}
                <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
                    <h2 class="font-semibold text-ink-700">Layanan</h2>

                    <div class="mt-3 space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="grid gap-3 rounded-xl border border-brand-100 p-3 sm:grid-cols-[minmax(0,1fr)_8rem_7rem_auto] sm:items-center">
                                <div>
                                    <label class="sr-only" x-bind:for="'service-' + index">Layanan</label>
                                    <select
                                        x-bind:id="'service-' + index"
                                        x-bind:name="'items[' + index + '][service_id]'"
                                        x-model="item.service_id"
                                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                                    >
                                        <option value="">Pilih layanan</option>
                                        @foreach ($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }} ({{ \App\Support\Format::rupiah($service->price) }} per {{ $service->unit }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="relative">
                                    <label class="sr-only" x-bind:for="'qty-' + index">Kuantitas</label>
                                    <input
                                        x-bind:id="'qty-' + index"
                                        x-bind:name="'items[' + index + '][quantity]'"
                                        type="number"
                                        x-model="item.quantity"
                                        x-bind:step="unitOf(item) === 'pcs' ? 1 : 0.1"
                                        x-bind:min="unitOf(item) === 'pcs' ? 1 : 0.1"
                                        inputmode="decimal"
                                        placeholder="0"
                                        x-on:keydown.enter.prevent
                                        class="block w-full rounded-lg border border-slate-300 py-2.5 pl-3 pr-10 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                                    >
                                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-ink-700/60" x-text="unitOf(item)"></span>
                                </div>

                                <p class="text-sm font-medium text-ink-700 sm:text-right" x-text="rupiah(lineTotal(item))"></p>

                                <button
                                    type="button"
                                    x-show="items.length > 1"
                                    x-on:click="removeItem(index)"
                                    class="justify-self-start text-sm font-medium text-red-600 hover:text-red-800 sm:justify-self-end"
                                >
                                    Hapus
                                </button>
                            </div>
                        </template>
                    </div>

                    <button
                        type="button"
                        x-show="items.length < 20"
                        x-on:click="addItem()"
                        class="mt-3 text-sm font-medium text-brand-600 hover:text-ink-700"
                    >
                        Tambah layanan lain
                    </button>
                </section>

                {{-- Catatan --}}
                <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
                    <label for="notes" class="font-semibold text-ink-700">Catatan pesanan</label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="3"
                        maxlength="500"
                        placeholder="Contoh: pakaian putih dipisah, ada noda di kemeja biru"
                        class="mt-3 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                    >{{ old('notes') }}</textarea>
                </section>
            </div>

            {{-- Kolom kanan: ringkasan dan pembayaran --}}
            <aside class="h-fit space-y-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100 lg:sticky lg:top-20">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-700/70">Tanggal</dt>
                        <dd class="font-medium text-ink-700">{{ now()->translatedFormat('d F Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-700/70">Subtotal</dt>
                        <dd class="font-medium text-ink-700" x-text="rupiah(subtotal)"></dd>
                    </div>
                </dl>

                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-sm text-ink-700">
                        <input type="checkbox" name="is_express" value="1" x-model="isExpress" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        Layanan ekspres
                    </label>

                    <div x-show="isExpress" x-cloak>
                        <label for="express_fee" class="block text-sm text-ink-700/70">Biaya ekspres (Rp)</label>
                        <input
                            id="express_fee"
                            name="express_fee"
                            type="number"
                            min="0"
                            step="500"
                            inputmode="numeric"
                            x-model="expressFee"
                            x-bind:disabled="! isExpress"
                            x-on:keydown.enter.prevent
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                        >
                    </div>
                </div>

                <div class="flex items-baseline justify-between gap-3 border-t border-brand-100 pt-4">
                    <p class="font-semibold text-ink-700">Total</p>
                    <p class="text-2xl font-bold text-ink-700" x-text="rupiah(total)"></p>
                </div>

                <div class="space-y-3 border-t border-brand-100 pt-4">
                    <h2 class="font-semibold text-ink-700">Pembayaran</h2>

                    <div>
                        <label for="payment_method" class="block text-sm text-ink-700/70">Metode</label>
                        <select
                            id="payment_method"
                            name="payment_method"
                            x-model="method"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                        >
                            <option value="cash">Tunai</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    <div>
                        <label
                            for="payment_input"
                            class="block text-sm text-ink-700/70"
                            x-text="method === 'cash' ? 'Uang diterima (Rp)' : 'Nominal dibayar (Rp)'"
                        ></label>
                        <div class="mt-1 flex gap-2">
                            <input
                                id="payment_input"
                                name="payment_input"
                                type="number"
                                min="0"
                                step="1"
                                inputmode="numeric"
                                x-model="paymentInput"
                                x-on:keydown.enter.prevent
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
                            >
                            <button
                                type="button"
                                x-on:click="payFull()"
                                class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-brand-600 ring-1 ring-brand-400/60 transition hover:bg-brand-100"
                            >
                                Pas
                            </button>
                        </div>
                        <p class="mt-1.5 text-xs text-ink-700/60">Kosongkan jika pelanggan belum membayar.</p>
                    </div>

                    <p x-show="method !== 'cash' && inputAmount > total" x-cloak class="text-sm text-red-600">
                        Nominal melebihi total tagihan.
                    </p>

                    <dl class="space-y-1.5 text-sm">
                        <div class="flex justify-between gap-3" x-show="method === 'cash' && change > 0" x-cloak>
                            <dt class="text-ink-700/70">Kembalian</dt>
                            <dd class="font-semibold text-ink-700" x-text="rupiah(change)"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-700/70">Tercatat dibayar</dt>
                            <dd class="font-medium text-ink-700" x-text="rupiah(paid)"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-700/70">Sisa tagihan</dt>
                            <dd class="font-semibold" x-bind:class="remaining > 0 ? 'text-red-600' : 'text-emerald-600'" x-text="rupiah(remaining)"></dd>
                        </div>
                    </dl>
                </div>

                <button
                    type="submit"
                    x-bind:disabled="submitting"
                    class="w-full rounded-lg bg-brand-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:opacity-60"
                >
                    Simpan transaksi
                </button>
            </aside>
        </div>
    </form>
@endif
@endsection

@push('scripts')
<script>
    // Logika form transaksi: pilih pelanggan, baris layanan, dan hitung total
    function transactionForm(config) {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        return {
            services: config.services,
            customer: config.customer,
            items: config.items,
            isExpress: config.isExpress,
            expressFee: config.expressFee,
            method: config.method,
            paymentInput: config.paymentInput,

            search: '',
            results: [],
            searching: false,
            showResults: false,
            showNew: false,
            newCustomer: { name: '', phone: '', address: '' },
            newError: '',
            savingNew: false,
            submitting: false,

            // ---- Layanan dan total ----
            serviceOf(item) {
                return this.services.find((s) => String(s.id) === String(item.service_id)) || null;
            },
            unitOf(item) {
                const service = this.serviceOf(item);
                return service ? service.unit : 'kg';
            },
            lineTotal(item) {
                const service = this.serviceOf(item);
                if (! service) return 0;
                return Math.round((parseFloat(item.quantity) || 0) * service.price);
            },
            addItem() {
                this.items.push({ service_id: '', quantity: '' });
            },
            removeItem(index) {
                this.items.splice(index, 1);
            },
            get subtotal() {
                return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
            },
            get fee() {
                return this.isExpress ? (parseInt(this.expressFee) || 0) : 0;
            },
            get total() {
                return this.subtotal + this.fee;
            },

            // ---- Pembayaran ----
            get inputAmount() {
                return Math.max(0, parseInt(this.paymentInput) || 0);
            },
            get paid() {
                return Math.min(this.inputAmount, this.total);
            },
            get change() {
                return this.method === 'cash' ? Math.max(0, this.inputAmount - this.total) : 0;
            },
            get remaining() {
                return Math.max(0, this.total - this.paid);
            },
            payFull() {
                this.paymentInput = this.total;
            },
            rupiah(value) {
                return 'Rp' + new Intl.NumberFormat('id-ID').format(value || 0);
            },

            // ---- Pelanggan ----
            openResults() {
                this.showResults = true;
                if (this.results.length === 0) this.searchCustomers();
            },
            async searchCustomers() {
                this.searching = true;
                this.showResults = true;
                try {
                    const url = config.searchUrl + '?q=' + encodeURIComponent(this.search);
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    this.results = response.ok ? await response.json() : [];
                } catch (error) {
                    this.results = [];
                } finally {
                    this.searching = false;
                }
            },
            selectCustomer(customer) {
                this.customer = customer;
                this.search = '';
                this.results = [];
                this.showResults = false;
            },
            clearCustomer() {
                this.customer = null;
            },
            async saveNewCustomer() {
                this.newError = '';
                this.savingNew = true;
                try {
                    const response = await fetch(config.quickStoreUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify(this.newCustomer),
                    });
                    const data = await response.json();

                    if (! response.ok) {
                        const messages = data.errors ? Object.values(data.errors).flat() : [];
                        this.newError = messages[0] || data.message || 'Pelanggan gagal disimpan.';
                        return;
                    }

                    this.selectCustomer(data);
                    this.showNew = false;
                    this.newCustomer = { name: '', phone: '', address: '' };
                } catch (error) {
                    this.newError = 'Koneksi bermasalah. Coba lagi.';
                } finally {
                    this.savingNew = false;
                }
            },
        };
    }
</script>
@endpush