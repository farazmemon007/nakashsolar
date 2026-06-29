@include('admin_panel.include.header_include')
<style>
    .info-card {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .info-card h5 {
        margin: 0;
        font-weight: 600;
    }
    .info-card p {
        margin: 5px 0 0 0;
        font-size: 14px;
        opacity: 0.9;
    }
    table input {
        font-size: 14px;
        padding: 8px 10px;
    }
    .table thead th {
        background-color: #f8fafc;
        font-weight: 600;
        color: #334155;
        border-bottom: 2px solid #e2e8f0;
        text-align: center;
    }
    .return-qty {
        background-color: #fef3c7;
        font-weight: 600;
        border-color: #f59e0b;
    }
    .return-qty:focus {
        background-color: #fffbeb;
        border-color: #d97706;
        box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15);
    }
    .return-amount {
        background-color: #ecfeff;
        font-weight: 600;
        color: #0891b2;
        border-color: #22d3ee;
    }
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Purchase Return</h4>
                    <h6>Return items from purchase invoice</h6>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('purchase.return.store') }}" method="POST" id="purchase-return-form">
                        @csrf
                        <input type="hidden" name="purchase_id" id="purchase_id" value="{{ $purchase->id ?? '' }}">
                        <input type="hidden" name="party_name" id="party_name" value="{{ $purchase->party_name ?? '' }}">

                        <!-- Selection Filters (Shown when no ID is passed or selectable) -->
                        <div class="row g-3 mb-4 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Return Date <span class="text-danger">*</span></label>
                                <input type="date" name="return_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                            </div>
                            
                            @if(!$purchase)
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Filter Purchases by Date</label>
                                    <input type="date" id="filter_purchase_date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Select Purchase Invoice <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="invoice_select" required>
                                        <option value="">-- Choose Invoice --</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-secondary w-100" id="btn-refresh-invoices">
                                        <i class="fa fa-sync me-1"></i> Refresh
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Info Card (Displays Selected Invoice Meta) -->
                        <div id="invoice-info-section" class="{{ $purchase ? '' : 'd-none' }}">
                            <div class="info-card">
                                <div class="row text-center text-md-start">
                                    <div class="col-md-3 mb-3 mb-md-0">
                                        <h5 id="info-invoice-number">{{ $purchase->invoice_number ?? '' }}</h5>
                                        <p>Invoice Number</p>
                                    </div>
                                    <div class="col-md-3 mb-3 mb-md-0">
                                        <h5 id="info-purchase-date">{{ $purchase ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') : '' }}</h5>
                                        <p>Purchase Date</p>
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <h5 id="info-vendor-name">{{ $purchase->vendor->Party_name ?? '' }}</h5>
                                        <p>Vendor Name</p>
                                    </div>
                                    <div class="col-md-2">
                                        <h5 id="info-grand-total">PKR {{ $purchase ? number_format($purchase->grand_total, 0) : '0' }}</h5>
                                        <p>Grand Total</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div id="items-table-section" class="{{ $purchase ? '' : 'd-none' }}">
                            <div class="section-title">Returned Items Details</div>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 25%">Item Name</th>
                                            <th style="width: 15%">Rate</th>
                                            <th>Discount</th>
                                            <th>Purchased Qty</th>
                                            <th>Already Returned</th>
                                            <th>Available Qty</th>
                                            <th style="width: 12%">Return Qty</th>
                                            <th style="width: 15%">Return Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-tbody">
                                        @if($purchase)
                                            @php
                                                // Calculate already returned quantities on load if purchase is provided
                                                $returnedQtys = [];
                                                $returns = \App\Models\PurchaseReturn::where('purchase_id', $purchase->id)->get();
                                                foreach ($returns as $ret) {
                                                    $retItems = json_decode($ret->item ?? '[]', true) ?: [];
                                                    $retQtys = json_decode($ret->return_qty ?? '[]', true) ?: [];
                                                    foreach ($retItems as $idx => $itemName) {
                                                        $returnedQtys[$itemName] = ($returnedQtys[$itemName] ?? 0) + (float)($retQtys[$idx] ?? 0);
                                                    }
                                                }

                                                $pItems = is_string($purchase->item) ? json_decode($purchase->item, true) : ($purchase->item ?? []);
                                                $pRates = is_string($purchase->rate) ? json_decode($purchase->rate, true) : ($purchase->rate ?? []);
                                                $pDiscounts = is_string($purchase->discount) ? json_decode($purchase->discount, true) : ($purchase->discount ?? []);
                                                $pPcs = is_string($purchase->pcs) ? json_decode($purchase->pcs, true) : ($purchase->pcs ?? []);
                                            @endphp

                                            @if(is_array($pItems))
                                                @foreach($pItems as $index => $item)
                                                    @php
                                                        $purchasedQty = (float)($pPcs[$index] ?? 0);
                                                        $alreadyReturned = (float)($returnedQtys[$item] ?? 0);
                                                        $availableQty = max(0, $purchasedQty - $alreadyReturned);
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <input type="text" name="item[]" class="form-control-plaintext fw-bold" value="{{ $item }}" readonly>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="rate[]" class="form-control-plaintext text-center rate" value="{{ $pRates[$index] ?? 0 }}" readonly>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="discount[]" class="form-control-plaintext text-center discount" value="{{ $pDiscounts[$index] ?? 0 }}" readonly>
                                                        </td>
                                                        <td class="text-center fw-semibold purchased-qty" data-qty="{{ $purchasedQty }}">
                                                            {{ $purchasedQty }}
                                                        </td>
                                                        <td class="text-center text-muted fw-semibold">
                                                            {{ $alreadyReturned }}
                                                        </td>
                                                        <td class="text-center text-success fw-bold available-qty" data-available="{{ $availableQty }}">
                                                            {{ $availableQty }}
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                name="return_qty[]"
                                                                class="form-control return-qty text-center"
                                                                data-index="{{ $index }}"
                                                                data-max="{{ $availableQty }}"
                                                                step="0.01"
                                                                min="0"
                                                                max="{{ $availableQty }}"
                                                                placeholder="0"
                                                                {{ $availableQty <= 0 ? 'disabled' : '' }}>
                                                            <small class="text-danger error-msg d-none" id="error-{{ $index }}">Max: {{ $availableQty }}</small>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="return_amount[]"
                                                                class="form-control return-amount text-center"
                                                                id="return-amount-{{ $index }}"
                                                                readonly>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @endif
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="7" class="text-end">Total Return Amount:</th>
                                            <th class="text-center">
                                                <input type="text" id="grand_total" class="form-control fw-bold text-danger text-center" readonly value="PKR 0.00">
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Reason and Notes -->
                            <div class="row mt-4 g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Return Reason <span class="text-danger">*</span></label>
                                    <textarea name="reason" class="form-control" rows="3" placeholder="Enter the reason for this return..." required></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Optional Notes</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Enter additional notes or remarks..."></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('all-purchase-return') }}" class="btn btn-secondary px-4">
                                    <i data-feather="arrow-left" class="me-1"></i> Back to Returns
                                </a>
                                <button type="submit" class="btn btn-danger px-5 shadow">
                                    <i data-feather="rotate-ccw" class="me-1"></i> Submit Return
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<!-- SweetAlert2 + JQuery Select2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize select2
        if ($('.select2').length) {
            $('.select2').select2({
                placeholder: "-- Choose Invoice --",
                allowClear: true
            });
        }

        // Load invoices for selection dropdown
        function loadInvoices() {
            let date = $('#filter_purchase_date').val() || '';
            $('#invoice_select').html('<option value="">Loading...</option>');

            $.ajax({
                url: '{{ route("get-purchase-invoices") }}',
                type: 'GET',
                data: { date: date },
                success: function (data) {
                    let options = '<option value="">-- Choose Invoice --</option>';
                    if (Array.isArray(data) && data.length) {
                        $.each(data, function (i, inv) {
                            options += `<option value="${inv.id}" data-party-name="${inv.party_name}">${inv.label}</option>`;
                        });
                    } else {
                        options = '<option value="">No purchase invoices found</option>';
                    }
                    $('#invoice_select').html(options).trigger('change');
                },
                error: function () {
                    $('#invoice_select').html('<option value="">Failed to load invoices</option>');
                }
            });
        }

        @if(!$purchase)
            loadInvoices();
            $('#filter_purchase_date').on('change', loadInvoices);
            $('#btn-refresh-invoices').on('click', loadInvoices);
        @endif

        // When invoice is selected
        $('#invoice_select').on('change', function() {
            let purchaseId = $(this).val();
            if (!purchaseId) {
                $('#invoice-info-section, #items-table-section').addClass('d-none');
                $('#purchase_id').val('');
                $('#party_name').val('');
                return;
            }

            // Fetch details via AJAX
            $.ajax({
                url: '{{ route("fetch-purchase-details") }}',
                type: 'GET',
                data: { id: purchaseId },
                success: function(response) {
                    if (response.success) {
                        // Populate meta card
                        $('#purchase_id').val(response.purchase.id);
                        $('#party_name').val(response.purchase.party_name);
                        
                        $('#info-invoice-number').text(response.purchase.invoice_number);
                        
                        let dateFormatted = response.purchase.purchase_date;
                        try {
                            let d = new Date(response.purchase.purchase_date);
                            dateFormatted = d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
                        } catch(e) {}
                        
                        $('#info-purchase-date').text(dateFormatted);
                        $('#info-vendor-name').text(response.purchase.vendor_name);
                        $('#info-grand-total').text('PKR ' + parseFloat(response.purchase.grand_total).toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 0}));

                        // Populate rows
                        let tbody = '';
                        $.each(response.items, function(idx, item) {
                            let disabledAttr = item.available_qty <= 0 ? 'disabled' : '';
                            tbody += `
                            <tr>
                                <td>
                                    <input type="text" name="item[]" class="form-control-plaintext fw-bold" value="${item.item}" readonly>
                                </td>
                                <td>
                                    <input type="number" name="rate[]" class="form-control-plaintext text-center rate" value="${item.rate}" readonly>
                                </td>
                                <td>
                                    <input type="number" name="discount[]" class="form-control-plaintext text-center discount" value="${item.discount}" readonly>
                                </td>
                                <td class="text-center fw-semibold purchased-qty" data-qty="${item.purchased_qty}">
                                    ${item.purchased_qty}
                                </td>
                                <td class="text-center text-muted fw-semibold">
                                    ${item.already_returned}
                                </td>
                                <td class="text-center text-success fw-bold available-qty" data-available="${item.available_qty}">
                                    ${item.available_qty}
                                </td>
                                <td>
                                    <input type="number"
                                        name="return_qty[]"
                                        class="form-control return-qty text-center"
                                        data-index="${idx}"
                                        data-max="${item.available_qty}"
                                        step="0.01"
                                        min="0"
                                        max="${item.available_qty}"
                                        placeholder="0"
                                        ${disabledAttr}>
                                    <small class="text-danger error-msg d-none" id="error-${idx}">Max: ${item.available_qty}</small>
                                </td>
                                <td>
                                    <input type="text" name="return_amount[]"
                                        class="form-control return-amount text-center"
                                        id="return-amount-${idx}"
                                        readonly>
                                </td>
                            </tr>
                            `;
                        });

                        $('#items-tbody').html(tbody);
                        $('#grand_total').val('PKR 0.00');

                        // Show sections
                        $('#invoice-info-section, #items-table-section').removeClass('d-none');
                    } else {
                        Swal.fire('Error', response.message || 'Failed to fetch details.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Communication error with the server.', 'error');
                }
            });
        });

        // Input & calculations
        $(document).on('input', '.return-qty', function() {
            let input = $(this);
            let idx = input.data('index');
            let maxQty = parseFloat(input.data('max')) || 0;
            let row = input.closest('tr');
            let errorMsg = $(`#error-${idx}`);

            let rate = parseFloat(row.find('.rate').val()) || 0;
            let discount = parseFloat(row.find('.discount').val()) || 0;
            let purchasedQty = parseFloat(row.find('.purchased-qty').data('qty')) || 1;
            let returnQty = parseFloat(input.val()) || 0;

            if (returnQty < 0) {
                input.val(0);
                returnQty = 0;
            }

            // Client-side stock validation
            if (returnQty > maxQty) {
                input.addClass('is-invalid');
                errorMsg.removeClass('d-none');
                input.val(maxQty);
                returnQty = maxQty;
            } else {
                input.classList ? input.removeClass('is-invalid') : input.removeClass('is-invalid');
                errorMsg.addClass('d-none');
            }

            // Math calculation: (rate * purchased_qty - discount) / purchased_qty * return_qty
            let grossTotal = rate * purchasedQty;
            let netTotal = grossTotal - discount;
            let effectiveRate = purchasedQty > 0 ? netTotal / purchasedQty : rate;
            let returnAmount = returnQty * effectiveRate;

            row.find('.return-amount').val(returnAmount.toFixed(2));

            calculateGrandTotal();
        });

        function calculateGrandTotal() {
            let total = 0;
            $('.return-amount').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#grand_total').val('PKR ' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }

        // Form submission confirmation
        $('#purchase-return-form').on('submit', function(e) {
            e.preventDefault();

            let form = this;
            let hasValidReturn = false;
            let qtyExceeded = false;

            $('.return-qty').each(function() {
                let qty = parseFloat($(this).val()) || 0;
                let maxQty = parseFloat($(this).data('max')) || 0;
                if (qty > 0) {
                    hasValidReturn = true;
                }
                if (qty > maxQty) {
                    qtyExceeded = true;
                }
            });

            if (!hasValidReturn) {
                Swal.fire('Validation Error', 'Please specify a return quantity of at least one item.', 'warning');
                return;
            }

            if (qtyExceeded) {
                Swal.fire('Validation Error', 'Some items have return quantities exceeding available stock.', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Purchase Return?',
                text: 'Are you sure you want to submit this return? This will adjust the vendor ledger balance accordingly.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Submit Return',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
