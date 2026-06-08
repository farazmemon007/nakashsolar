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
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Edit Distributor Sales Management</h4>
                    <h6>Manage Edit Distributor Sales Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif
                    <form action="{{ route('sale.update', $original->id) }}" method="POST" id="saleForm">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Invoice Date</label>
                                <input type="date" class="form-control" name="Date" id="Date" value="{{ $original['Date'] }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="distributor" class="form-label">Select Distributor</label>
                                <select class="form-control" name="distributor_id" id="distributor">
                                    <option value="">Select Distributor</option>
                                    @foreach($Distributors as $distributor)
                                    <option value="{{ $distributor->id }}"
                                        data-city="{{ $distributor->City }}"
                                        data-area="{{ $distributor->Area }}"
                                        data-address="{{ $distributor->Address }}"
                                        data-phone="{{ $distributor->Contact }}"
                                        {{ $original['distributor_id'] == $distributor->id ? 'selected' : '' }}>
                                        {{ $distributor->Customer }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="distributor_city" class="form-control readonly-box" id="city" value="{{ $original['distributor_city'] }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Area</label>
                                <input type="text" name="distributor_area" class="form-control readonly-box" id="area" value="{{ $original['distributor_area'] }}" readonly>

                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="distributor_address" class="form-control readonly-box" id="address" value="{{ $original['distributor_address'] }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="distributor_phone" class="form-control readonly-box" id="phone" value="{{ $original['distributor_phone'] }}" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Order Booker</label>
                                <select class="form-control" name="Booker" id="Booker" required>
                                    <option disabled>Select Booker</option>
                                    @foreach($Staffs as $Staff)
                                    <option value="{{ $Staff->name }}" {{ $original['Booker'] == $Staff->name ? 'selected' : '' }}>{{ $Staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Saleman</label>
                                <select class="form-control" name="Saleman" id="Saleman" required>
                                    <option disabled>Select Salesman</option>
                                    @foreach($Staffs as $Staff)
                                    <option value="{{ $Staff->name }}" {{ $original['Saleman'] == $Staff->name ? 'selected' : '' }}>{{ $Staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center" id="saleTable">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Price/ Unit</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $items = json_decode($original['item']) ?? [];
                                    $pcs = json_decode($original['pcs']) ?? [];
                                    // if qty is empty, try carton_qty
                                    if(empty($pcs)) {
                                        $pcs = json_decode($original['carton_qty']) ?? [];
                                    }
                                    // Since previous system didn't have 'unit', we'll set it to Pcs
                                    $rates = json_decode($original['rate']) ?? [];
                                    $amounts = json_decode($original['amount']) ?? [];
                                    $rowCount = count($items);
                                    @endphp

                                    @for ($i = 0; $i < $rowCount; $i++)
                                        <tr class="sale-row">
                                            <td style="position:relative;">
                                                <input type="text" class="form-control item-input" name="item_name[]" autocomplete="off" placeholder="Type item name" value="{{ $items[$i] ?? '' }}">
                                                <div class="autocomplete-list d-none"></div>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control qty text-center" name="pcs[]" min="0" value="{{ $pcs[$i] ?? 1 }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control unit text-center readonly-box" name="product_mode[]" readonly value="Pcs">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control rate text-end" name="rate[]" min="0" value="{{ $rates[$i] ?? 0 }}">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control amount text-end readonly-box" name="amount[]" readonly value="{{ $amounts[$i] ?? 0 }}">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-row">Delete</button>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                        <td>
                                            <input type="number" class="form-control fw-bold text-end readonly-box" id="grandTotal" name="grand_total" value="{{ $original['grand_total'] }}" readonly>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Discount:</td>
                                        <td>
                                            <div class="input-group">
                                                <input type="number" class="form-control fw-bold text-end" id="discountValue" name="discount_value" value="{{ $original['discount_value'] }}">
                                                <select id="discountType" name="discount_type" class="form-control" style="max-width: 80px;">
                                                    <option value="pkr" {{ $original['discount_type'] == 'pkr' ? 'selected' : '' }}>PKR</option>
                                                    <option value="percent" {{ $original['discount_type'] == 'percent' ? 'selected' : '' }}>%</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Scheme:</td>
                                        <td>
                                            <input type="number" class="form-control fw-bold text-end" id="schemeValue" name="scheme_value" value="{{ $original['scheme_value'] }}">
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Net Amount:</td>
                                        <td>
                                            <input type="number" class="form-control fw-bold text-end readonly-box" id="netAmount" name="net_amount" value="{{ $original['net_amount'] }}" readonly>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-success me-2" id="addRow">Add More</button>
                            <button type="submit" class="btn btn-primary btn-lg">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.getElementById('distributor').addEventListener('change', function() {
        let selectedOption = this.options[this.selectedIndex];

        document.getElementById('city').value = selectedOption.getAttribute('data-city') || '';
        document.getElementById('area').value = selectedOption.getAttribute('data-area') || '';
        document.getElementById('address').value = selectedOption.getAttribute('data-address') || '';
        document.getElementById('phone').value = selectedOption.getAttribute('data-phone') || '';
    });

    $(document).ready(function() {

        // ========== ROW CREATION ==========
        function createRowHtml() {
            return `
                <tr class="sale-row">
                    <td style="position:relative;">
                        <input type="text" class="form-control item-input" name="item_name[]" autocomplete="off" placeholder="Type item name">
                        <div class="autocomplete-list d-none"></div>
                    </td>
                    <td>
                        <input type="number" class="form-control qty text-center" name="pcs[]" min="0">
                    </td>
                    <td>
                        <input type="text" class="form-control unit text-center readonly-box" name="product_mode[]" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control rate text-end" name="rate[]" min="0">
                    </td>
                    <td>
                        <input type="number" class="form-control amount text-end readonly-box" name="amount[]" readonly>
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

            row.find('.rate').val(parseInt(it.wholesale_price) || parseInt(it.retail_price) || 0);
            
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

            let discountValue = parseFloat($('#discountValue').val()) || 0;
            let discountType = $('#discountType').val();
            let schemeValue = parseFloat($('#schemeValue').val()) || 0;
            let discountAmount = 0;

            if (discountType === "percent") {
                discountAmount = (total * discountValue) / 100;
            } else {
                discountAmount = discountValue;
            }

            let netAmount = total - discountAmount - schemeValue;
            $('#netAmount').val(netAmount.toFixed(2));
        }

        $('#discountValue, #discountType, #schemeValue').on('input change', calculateGrandTotal);

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

        $('#addRow').on('click', appendNewRow);

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

        autoAddIfNeeded();
    });
</script>