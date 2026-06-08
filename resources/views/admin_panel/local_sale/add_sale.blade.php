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

            <h4 class="mb-3">🧾 Job Order</h4>

            <form method="POST" action="{{ route('store-local-sale') }}" id="saleForm">
                @csrf

                <div class="container-fluid">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-md-3">
                                    <label>Party Type</label>
                                    <select id="partyType" name="party_type" class="form-control">
                                        <option value="customer">Customer</option>
                                        <option value="vendor">Vendor</option>
                                        <option value="walkin">Walk-In</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Sale Date & Time</label>
                                    <input type="datetime-local" name="sale_date" class="form-control" value="{{ date('Y-m-d\TH:i') }}">
                                </div>

                                <div class="col-md-3 party-box" id="customerBox">
                                    <label>Customer</label>
                                    <select class="form-control search" name="customer_id" id="customer">
                                        <option value="">Select</option>
                                        @foreach ($Customers as $c)
                                            <option value="{{ $c->id }}" data-phone="{{ $c->phone_number }}"
                                                data-address="{{ $c->address }}">
                                                {{ $c->customer_name ?? $c->shop_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 party-box d-none" id="vendorBox">
                                    <label>Vendor</label>
                                    <select class="form-control search" name="vendor_id" id="vendor">
                                        <option value="">Select</option>
                                        @foreach ($Vendors as $v)
                                            <option value="{{ $v->id }}" data-phone="{{ $v->Party_phone }}"
                                                data-address="{{ $v->Party_address }}">
                                                {{ $v->Party_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 readonly-wrap">
                                    <label>Phone</label>
                                    <input id="phone" class="form-control readonly-box" readonly>
                                </div>

                                <div class="col-md-3 readonly-wrap">
                                    <label>Address</label>
                                    <input id="address" class="form-control readonly-box" readonly>
                                </div>

                                <div class="col-md-3 d-none" id="walkinName">
                                    <label>Name</label>
                                    <input name="walkin_name" class="form-control">
                                </div>

                                <div class="col-md-3 d-none" id="walkinPhone">
                                    <label>Phone</label>
                                    <input name="walkin_phone" class="form-control">
                                </div>

                                <div class="col-md-3 d-none" id="walkinAddress">
                                    <label>Address</label>
                                    <input name="walkin_address" class="form-control">
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

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
                                    <!-- Rows injected via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-3 fw-bold text-primary">Delivery & Payment Details</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="fw-bold">Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" name="delivery_date" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold">Notify Before (Days)</label>
                                <input type="number" name="notify_days_before" class="form-control" value="2" min="1" max="30">
                                <small class="text-muted">System will notify you X days before delivery</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label>Gross Total</label>
                                <input id="grandTotal" class="form-control readonly-box" readonly>
                            </div>

                            <div class="col-md-3">
                                <label>Discount</label>
                                <input name="gross_discount" class="form-control discount" value="0">
                            </div>

                            <div class="col-md-3">
                                <label>Advance</label>
                                <input id="advance" name="advance_amount" class="form-control advance">
                            </div>

                            <div class="col-md-3">
                                <label>Remaining</label>
                                <input id="remaining" class="form-control readonly-box" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="net_amount" id="netAmount">
                <button class="btn btn-primary" type="submit">Save Job Order</button>
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
    // ========== PARTY TYPE SELECTION ==========
    $('#partyType').on('change', function () {
        let t = this.value;

        $('#customerBox,#vendorBox').addClass('d-none');
        $('#walkinName,#walkinPhone,#walkinAddress').addClass('d-none');
        $('.readonly-wrap').addClass('d-none');

        $('#advance').prop('readonly', false);
        $('#remaining').closest('.col-md-3').removeClass('d-none');

        if (t === 'customer') {
            $('#customerBox').removeClass('d-none');
            $('.readonly-wrap').removeClass('d-none');
        }

        if (t === 'vendor') {
            $('#vendorBox').removeClass('d-none');
            $('.readonly-wrap').removeClass('d-none');
        }

        if (t === 'walkin') {
            $('#walkinName,#walkinPhone,#walkinAddress').removeClass('d-none');
            $('#advance').val($('#grandTotal').val()).prop('readonly', true);
            $('#remaining').val('0');
            $('#remaining').closest('.col-md-3').addClass('d-none');
        }

        calculateGrandTotal();
    });

    $('#partyType').trigger('change');

    $('#customer').on('change', function () {
        let o = $('option:selected', this);
        $('#phone').val(o.data('phone') || '');
        $('#address').val(o.data('address') || '');
    });

    $('#vendor').on('change', function () {
        let o = $('option:selected', this);
        $('#phone').val(o.data('phone') || '');
        $('#address').val(o.data('address') || '');
    });

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

    // Initial 5 rows
    for (let i = 0; i < 5; i++) {
        $('#saleTable tbody').append(createRowHtml());
    }

    function appendNewRow() {
        $('#saleTable tbody').append(createRowHtml());
        let newRow = $('#saleTable tbody tr').last();
        newRow[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
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
        $('#netAmount').val(net.toFixed(2));

        let advance = parseFloat($('.advance').val()) || 0;
        
        if ($('#partyType').val() === 'walkin') {
            $('#advance').val(net.toFixed(2));
            $('#remaining').val('0.00');
        } else {
            $('#remaining').val((net - advance).toFixed(2));
        }
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
});
</script>
