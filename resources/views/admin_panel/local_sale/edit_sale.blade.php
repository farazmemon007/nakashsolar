@include('admin_panel.include.header_include')

<style>
    /* Simple styles for custom autocomplete dropdown */
    .autocomplete-list {
        position: absolute;
        z-index: 9999;
        background: #fff;
        border: 1px solid #ddd;
        max-height: 220px;
        overflow-y: auto;
        width: 100%;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .autocomplete-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    .autocomplete-item:last-child {
        border-bottom: none;
    }
    .autocomplete-item:hover,
    .autocomplete-item.active {
        background: #e9ecef;
    }

    .row-relative {
        position: relative;
    }

    /* General Table Styling */
    #saleTable {
        width: 100%;
        table-layout: fixed;
    }

    #saleTable th {
        font-weight: 600;
        background-color: #f8f9fa;
        color: #333;
        border-bottom: 2px solid #dee2e6;
        padding: 10px 5px !important;
        font-size: 13px;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
    }

    #saleTable td {
        vertical-align: middle;
        padding: 8px 6px !important;
    }

    /* Column Widths */
    #saleTable th:nth-child(1), #saleTable td:nth-child(1) { width: 250px; } /* Item Name */
    #saleTable th:nth-child(2), #saleTable td:nth-child(2) { width: 100px; } /* Quantity */
    #saleTable th:nth-child(3), #saleTable td:nth-child(3) { width: 100px; } /* Unit */
    #saleTable th:nth-child(4), #saleTable td:nth-child(4) { width: 120px; } /* Price/ Unit */
    #saleTable th:nth-child(5), #saleTable td:nth-child(5) { width: 150px; } /* Amount */
    #saleTable th:nth-child(6), #saleTable td:nth-child(6) { width: 80px; }  /* Action */

    /* Input & Select Styling */
    #saleTable .form-control {
        border-radius: 4px;
        border: 1px solid #ced4da;
        font-size: 13px;
        padding: 6px 8px;
        height: 34px; /* Consistent height */
        width: 100% !important;
    }

    #saleTable .form-control:focus {
        border-color: #637381;
        box-shadow: none;
    }

    /* Readonly inputs styling */
    .readonly-box {
        background-color: #f8f9fa !important;
        color: #6c757d;
        cursor: default;
    }

    /* Action Buttons */
    #saleTable .remove-row {
        padding: 4px 10px;
        font-size: 12px;
        white-space: nowrap;
    }

    #saleTable tbody tr:hover {
        background-color: #f5f5f5;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <h4 class="mb-3">✏️ Edit Job Order</h4>

            <form method="POST" action="{{ route('local.sale.update', $original->id) }}" id="saleForm">
                @csrf
                @method('PUT')

                <div class="container-fluid">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-3">

                                @php
                                $partyName = '';
                                $phone = '';
                                $address = '';

                                if ($original->party_type === 'customer' && $original->customer) {
                                    $partyName = $original->customer->customer_name ?? $original->customer->shop_name;
                                    $phone = $original->customer->phone_number;
                                    $address = $original->customer->address;
                                } elseif ($original->party_type === 'vendor' && $original->vendor) {
                                    $partyName = $original->vendor->Party_name;
                                    $phone = $original->vendor->Party_phone;
                                    $address = $original->vendor->Party_address;
                                } else {
                                    // walkin or fallback
                                    $partyName = $original->customer_shopname;
                                    $phone = $original->customer_phone;
                                    $address = $original->customer_address;
                                }
                                @endphp

                                <input type="hidden" name="party_type" value="{{ $original->party_type }}">
                                <input type="hidden" name="customer_id" value="{{ $original->customer_id }}">
                                <input type="hidden" name="vendor_id" value="{{ $original->vendor_id }}">
                                <input type="hidden" name="walkin_name" value="{{ $original->customer_shopname }}">

                                <div class="col-md-3">
                                    <label>Party Type</label>
                                    <input class="form-control readonly-box" value="{{ ucfirst($original->party_type) }}" readonly>
                                </div>

                                <div class="col-md-3">
                                    <label>Party</label>
                                    <input class="form-control readonly-box" value="{{ $partyName }}" readonly>
                                </div>

                                <div class="col-md-3">
                                    <label>Phone</label>
                                    <input class="form-control readonly-box" value="{{ $phone }}" readonly>
                                </div>

                                <div class="col-md-3">
                                    <label>Address</label>
                                    <input class="form-control readonly-box" value="{{ $address }}" readonly>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $items = json_decode($original->item, true) ?? [];
                    $units = json_decode($original->unit, true) ?? [];
                    $rates = json_decode($original->rate, true) ?? [];
                    $qtys = json_decode($original->qty, true) ?? [];
                    $amounts = json_decode($original->amount, true) ?? [];
                @endphp

                <div class="card mb-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center" id="saleTable">
                                <thead>
                                    <tr class="bg-light">
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Price/ Unit</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($items as $i => $item)
                                        <tr class="sale-row">
                                            <td style="position:relative;">
                                                <input type="text" class="form-control item-input" name="item_name[]" autocomplete="off" placeholder="Type item name" value="{{ $item }}">
                                                <div class="autocomplete-list d-none"></div>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control qty text-center" name="qty[]" min="0" value="{{ $qtys[$i] ?? 1 }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control unit text-center" name="unit[]" readonly value="{{ $units[$i] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control rate text-end" name="rate[]" min="0" value="{{ $rates[$i] ?? 0 }}">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control amount text-end" name="amount[]" readonly value="{{ $amounts[$i] ?? 0 }}">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-row">Delete</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label>Gross Total</label>
                                <input id="grandTotal" name="grand_total" class="form-control readonly-box" readonly value="{{ $original->grand_total }}">
                            </div>

                            <div class="col-md-3">
                                <label>Discount</label>
                                <input name="discount_value" class="form-control discount" value="{{ $original->discount_value }}">
                            </div>

                            <div class="col-md-3">
                                <label>Advance</label>
                                <input id="advance" name="advance_amount" class="form-control advance" value="{{ $original->advance_amount }}">
                            </div>

                            <div class="col-md-3">
                                <label>Net Amount / Remaining</label>
                                <input id="netAmount" name="net_amount" class="form-control readonly-box" readonly value="{{ $original->net_amount }}">
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">Update Job Order</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: "{{ session('success') }}",
            timer: 2000,
            showConfirmButton: false
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "{{ session('error') }}"
        });
    </script>
@endif

@if ($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: `{!! implode('<br>', $errors->all()) !!}`
        });
    </script>
@endif

@include('admin_panel.include.footer_include')

<script>
$(document).ready(function () {

    // ========== ROW CREATION ==========
    function createRowHtml() {
        return `
            <tr class="sale-row">
                <td style="position:relative;">
                    <input type="text" class="form-control item-input" name="item_name[]" autocomplete="off" placeholder="Type item name">
                    <div class="autocomplete-list d-none"></div>
                </td>
                <td>
                    <input type="number" class="form-control qty text-center" name="qty[]" min="0">
                </td>
                <td>
                    <input type="text" class="form-control unit text-center" name="unit[]" readonly>
                </td>
                <td>
                    <input type="number" class="form-control rate text-end" name="rate[]" min="0">
                </td>
                <td>
                    <input type="number" class="form-control amount text-end" name="amount[]" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row">Delete</button>
                </td>
            </tr>`;
    }

    function appendNewRow() {
        $('#saleTable tbody').append(createRowHtml());
    }

    // Remove row
    $(document).on('click', '.remove-row', function () {
        let rowCount = $('#saleTable tbody tr').length;
        if (rowCount > 1) {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        } else {
            Swal.fire('Cannot Delete', 'At least one row must remain.', 'warning');
        }
    });

    // ========== AUTOCOMPLETE SEARCH ==========
    $(document).on('input', '.item-input', function () {
        let input = $(this);
        let row = input.closest('tr');
        let list = row.find('.autocomplete-list');
        let q = input.val().trim();

        if (!q) {
            list.addClass('d-none');
            return;
        }

        $.ajax({
            url: "{{ route('get.items') }}",
            type: "GET",
            data: { q: q },
            success: function (res) {
                if (!Array.isArray(res) || res.length === 0) {
                    list.addClass('d-none');
                    return;
                }

                list.empty().removeClass('d-none');
                res.forEach(it => {
                    let el = $(`<div class="autocomplete-item">${it.item_name}</div>`);
                    el.data('item', it);
                    list.append(el);
                });
            }
        });
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.item-input, .autocomplete-list').length) {
            $('.autocomplete-list').addClass('d-none');
        }
    });

    $(document).on('click', '.autocomplete-item', function () {
        let it = $(this).data('item');
        let row = $(this).closest('tr');

        row.find('.item-input').val(it.item_name);

        let unitVal = it.product_mode || '';
        if (unitVal === 'measurements') unitVal = 'Sq.ft';
        else if (unitVal === 'simple') unitVal = 'Pcs';
        row.find('.unit').val(unitVal);

        row.find('.rate').val(parseInt(it.retail_price) || 0);
        
        if (!row.find('.qty').val()) {
            row.find('.qty').val(1);
        }

        row.find('.autocomplete-list').addClass('d-none');

        calculateRow(row);
        autoAddIfNeeded();
    });

    // ========== CALCULATIONS ==========
    $(document).on('input', '.rate, .qty', function () {
        let row = $(this).closest('tr');
        calculateRow(row);
        autoAddIfNeeded();
    });

    function calculateRow(row) {
        let rate = parseFloat(row.find('.rate').val()) || 0;
        let qty = parseFloat(row.find('.qty').val()) || 0;

        let finalAmount = rate * qty;
        row.find('.amount').val(finalAmount.toFixed(2));

        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let total = 0;
        $('.amount').each(function () {
            total += parseFloat($(this).val()) || 0;
        });

        $('#grandTotal').val(total.toFixed(2));

        let discount = parseFloat($('.discount').val()) || 0;
        let net = total - discount;
        
        // Wait, here "net_amount" means the remaining amount after advance?
        // Let's use the same logic as the old view
        let advance = parseFloat($('.advance').val()) || 0;
        $('#netAmount').val((net - advance).toFixed(2));
    }

    $('.discount, .advance').on('input', calculateGrandTotal);

    // ========== AUTO ADD ROW WHEN NEEDED ==========
    function isRowEmpty(row) {
        let itemName = row.find('.item-input').val().trim();
        let rate = parseFloat(row.find('.rate').val()) || 0;
        let qty = parseFloat(row.find('.qty').val()) || 0;

        return !itemName && rate === 0 && qty === 0;
    }

    function autoAddIfNeeded() {
        let rows = $('#saleTable tbody tr');
        let emptyRowExists = false;

        rows.each(function () {
            if (isRowEmpty($(this))) {
                emptyRowExists = true;
                return false;
            }
        });

        if (!emptyRowExists) {
            appendNewRow();
        }
    }

    $('#saleForm').on('submit', function(e) {
        let validItems = 0;
        $('.item-input').each(function() {
            if ($(this).val().trim() !== '') validItems++;
        });

        if (validItems === 0) {
            e.preventDefault();
            Swal.fire('Error', 'Please add at least one item.', 'error');
            return false;
        }
    });

    // Make sure at least one empty row exists at the end
    autoAddIfNeeded();
});
</script>
