<x-app-layout>
    <div class="min-h-screen p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Invoices</h1>
                    <p class="text-gray-600">Manage and track all your invoices in one place</p>
                </div>
                <div>
                    <form method="POST" action="{{ route('invoices.export') }}" class="inline">
                        @csrf
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export CSV
                        </button>
                    </form>
                </div>
            </div>

            <!-- Alert Messages -->
            @if (session('success'))
                <div
                    class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filters and Search Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <form method="GET" action="{{ route('invoices.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                Search Invoice
                            </label>
                            <input type="text" id="search" name="search" value="{{ request('search') }}"
                                placeholder="Invoice number..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>
                            <select id="status" name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Statuses</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid
                                </option>
                                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open
                                </option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft
                                </option>
                                <option value="uncollectible"
                                    {{ request('status') === 'uncollectible' ? 'selected' : '' }}>Uncollectible
                                </option>
                                <option value="void" {{ request('status') === 'void' ? 'selected' : '' }}>Void
                                </option>
                            </select>
                        </div>

                        <!-- Date Range From -->
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">
                                From Date
                            </label>
                            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Date Range To -->
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">
                                To Date
                            </label>
                            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            Apply Filters
                        </button>
                        <a href="{{ route('invoices.index') }}"
                            class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Invoices</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Paid</p>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['paid'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 mt-1">${{ number_format($stats['paid_amount'] ?? 0, 2) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Unpaid</p>
                            <p class="text-2xl font-bold text-red-600">{{ $stats['unpaid'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                ${{ number_format($stats['unpaid_amount'] ?? 0, 2) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Amount</p>
                            <p class="text-2xl font-bold text-gray-900">
                                ${{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Invoice #
                                </th>
                                @if (auth()->user()->role_id == 1)
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Practice
                                    </th>
                                @endif
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    BIlling email
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Status
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Due Date
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Created
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse ($invoices as $invoice)
                                @php
                                    $isCashier = method_exists($invoice, 'total');

                                    $amount = $isCashier
                                        ? $invoice->total()
                                        : number_format(($invoice->amount_due ?? 0) / 100, 2);

                                    $status = strtolower($invoice->status);

                                    $created = $isCashier
                                        ? $invoice->date()->toDateString()
                                        : \Carbon\Carbon::createFromTimestamp($invoice->created)->toDateString();

                                    $dueDate = null;
                                    if ($isCashier && $invoice->due_date) {
                                        $dueDate = \Carbon\Carbon::createFromTimestamp(
                                            $invoice->due_date,
                                        )->toDateString();
                                    } elseif (!$isCashier && $invoice->due_date) {
                                        $dueDate = \Carbon\Carbon::createFromTimestamp(
                                            $invoice->due_date,
                                        )->toDateString();
                                    }

                                    $statusColors = [
                                        'paid' => 'bg-green-100 text-green-800',
                                        'open' => 'bg-yellow-100 text-yellow-800',
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'uncollectible' => 'bg-red-100 text-red-800',
                                        'void' => 'bg-purple-100 text-purple-800',
                                    ];
                                @endphp

                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $invoice->number ?? '—' }}
                                        </span>
                                    </td>
                                    @if (auth()->user()->role_id == 1)
                                        <td class="px-6 py-4">
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $invoice->customer_name ?? '—' }}
                                            </span>
                                        </td>
                                    @endif

                                    <td class="px-6 py-4">
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ $invoice->customer_email }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ $amount }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600">
                                            {{ $dueDate ?? '—' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600">
                                            {{ $created }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex gap-2 flex-wrap">
                                            @if ($invoice->invoice_pdf)
                                                <a title="Download invoice PDF" href="{{ $invoice->invoice_pdf }}"
                                                    target="_blank"
                                                    class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors text-sm font-medium">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>

                                                </a>
                                            @endif

                                            @if ($invoice->status != 'paid' && $invoice->hosted_invoice_url)
                                                <a title="Pay invoice" href="{{ $invoice->hosted_invoice_url }}"
                                                    target="_blank"
                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-md hover:bg-indigo-100 transition-colors text-sm font-medium">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </a>
                                            @endif

                                            @if (auth()->user()->role_id == 1)
                                                <!-- Admin Actions -->
                                                @if (in_array($invoice->status, ['open', 'past_due']))
                                                    <form
                                                        action="{{ route('stripe.invoices.reminder', $invoice->id) }}"
                                                        method="POST" class="inline">
                                                        @csrf

                                                        <button type="submit" title="Send Reminder"
                                                            class="inline-flex items-center px-3 py-1.5 bg-orange-50 text-orange-700 rounded-md hover:bg-orange-100 transition-colors text-sm font-medium">
                                                            <svg class="w-4 h-4 mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11 a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341  C7.67 6.165 6 8.388 6 11v3.159 c0 .538-.214 1.055-.595 1.436L4 17h5m6 0 v1a3 3 0 11-6 0v-1m6 0H9" />
                                                            </svg>

                                                        </button>
                                                    </form>
                                                @endif
                                                {{-- @if ($invoice->status !== 'paid')
                                                    <form method="POST"
                                                        action="{{ route('invoices.mark-paid', $invoice->id) }}"
                                                        class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            onclick="return confirm('Mark this invoice as paid?')"
                                                            class="inline-flex items-center px-3 py-1.5 bg-green-50 text-green-700 rounded-md hover:bg-green-100 transition-colors text-sm font-medium">
                                                            Mark Paid
                                                        </button>
                                                    </form>
                                                @endif --}}

                                                {{-- @if ($invoice->status !== 'void')
                                                    <form method="POST"
                                                        action="{{ route('invoices.void', $invoice->id) }}"
                                                        class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            onclick="return confirm('Void this invoice?')"
                                                            class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 rounded-md hover:bg-red-100 transition-colors text-sm font-medium">
                                                            Void
                                                        </button>
                                                    </form>
                                                @endif --}}
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-gray-500 font-medium">No invoices found</p>
                                        <p class="text-gray-400 text-sm mt-1">Try adjusting your filters</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
