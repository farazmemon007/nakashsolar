@include('admin_panel.include.header_include')

<style>
    .qty-box {
        display: flex;
        gap: 4px;
        align-items: center;
    }

    .qty-box .qty {
        max-width: 60px;
    }

    .sale-table th:nth-child(1) {
        width: 20%;
    }

    .sale-table th:nth-child(2) {
        width: 8%;
    }

    .sale-table th:nth-child(3) {
        width: 8%;
    }

    .sale-table th:nth-child(4) {
        width: 8%;
    }

    .sale-table th:nth-child(5) {
        width: 10%;
    }

    .sale-table th:nth-child(6) {
        width: 10%;
    }

    .sale-table th:nth-child(7) {
        width: 12%;
    }

    .sale-table th:nth-child(8) {
        width: 12%;
    }

    .sale-table th:nth-child(9) {
        width: 12%;
    }

    .sale-table input {
        font-size: 14px;
        padding: 6px 8px;
    }

    .sale-table select {
        font-size: 14px;
        padding: 6px 8px;
    }

    .sale-table .btn-sm {
        padding: 4px 8px;
        font-size: 14px;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <h4 class="mb-3">🧾 Job Order</h4>

            <form method="POST" action="{{ route('store-local-sale') }}">
                @csrf

                <div class="container-fluid">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label>Party Type</label>
                                    <select id="partyType" name="party_type" class="form-control">
                                        <option value="customer">Customer</option>
                                        <option value="vendor">Vendor</option>
                                        <option value="walkin">Walk-In</option>
                                    </select>
                                </div>

                                <div class="col-md-6 party-box" id="customerBox">
                                    <label>Customer</label>
                                    <select class="form-control" name="customer_id" id="customer">
                                        <option value="">Select</option>
                                        @foreach ($Customers as $c)
                                            <option value="{{ $c->id }}" data-phone="{{ $c->phone_number }}"
                                                data-address="{{ $c->address }}">
                                                {{ $c->shop_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 party-box d-none" id="vendorBox">
                                    <label>Vendor</label>
                                    <select class="form-control" name="vendor_id" id="vendor">
                                        <option value="">Select</option>
                                        @foreach ($Vendors as $v)
                                            <option value="{{ $v->id }}" data-phone="{{ $v->Party_phone }}"
                                                data-address="{{ $v->Party_address }}">
                                                {{ $v->Party_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 readonly-wrap">
                                    <label>Phone</label>
                                    <input id="phone" class="form-control readonly-box" readonly>
                                </div>

                                <div class="col-md-6 readonly-wrap">
                                    <label>Address</label>
                                    <input id="address" class="form-control readonly-box" readonly>
                                </div>

                                <div class="col-md-6 d-none" id="walkinName">
                                    <label>Name</label>
                                    <input name="walkin_name" class="form-control">
                                </div>

                                <div class="col-md-6 d-none" id="walkinPhone">
                                    <label>Phone</label>
                                    <input name="walkin_phone" class="form-control">
                                </div>

                                <div class="col-md-6 d-none" id="walkinAddress">
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
                            <table class="table table-bordered text-center mb-0 sale-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>H</th>
                                        <th>W</th>
                                        <th>Unit</th>
                                        <th>Area (ft²)</th>
                                        <th>Rate</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody id="saleTableBody">
                                    <tr class="sale-row">
                                        <td><input name="item_name[]" class="form-control"></td>
                                        <td><input name="height[]" class="form-control height"></td>
                                        <td><input name="width[]" class="form-control width"></td>
                                        <td>
                                            <select name="unit[]" class="form-control unit">
                                                <option value="ft" selected>Feet</option>
                                                <option value="inch">Inch</option>
                                            </select>
                                        </td>
                                        <td><input class="form-control area readonly-box" readonly></td>
                                        <td><input name="rate[]" class="form-control rate"></td>
                                        <td>
                                            <div class="qty-box">
                                                <button type="button"
                                                    class="btn btn-sm btn-secondary qty-minus">−</button>
                                                <input name="qty[]" class="form-control qty text-center" value="1">
                                                <button type="button"
                                                    class="btn btn-sm btn-secondary qty-plus">+</button>
                                            </div>
                                        </td>
                                        <td><input name="amount[]" class="form-control item-total readonly-box"
                                                readonly></td>
                                        <td>
                                            <button type="button" class="btn btn-success btn-sm add-row">+</button>
                                            <button type="button" class="btn btn-danger btn-sm remove-row">×</button>
                                        </td>
                                    </tr>
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
                                <input id="grandTotal" class="form-control readonly-box" readonly>
                            </div>

                            <div class="col-md-3">
                                <label>Discount</label>
                                <input name="gross_discount" class="form-control" value="0">
                            </div>

                            <div class="col-md-3">
                                <label>Advance</label>
                                <input id="advance" name="advance_amount" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label>Remaining</label>
                                <input id="remaining" class="form-control readonly-box" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="net_amount" id="netAmount">
                <button class="btn btn-primary">Save Job Order</button>
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

        calcGrand();
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

    function toFeet(value, unit) {
        if (!value) return 0;

        value = value.toString().trim();
        let parts = value.split('.');

        let whole = parseInt(parts[0]) || 0;
        let decimal = parts[1] ? parseInt(parts[1]) : 0;

        if (unit === 'ft') {
            return whole + (decimal / 12);
        }

        let inches = whole + (decimal / 25.4);
        return inches / 12;
    }

    function calcRow(r) {
        let unit = r.find('.unit').val();
        let h = toFeet(r.find('.height').val(), unit);
        let w = toFeet(r.find('.width').val(), unit);
        let rate = parseFloat(r.find('.rate').val()) || 0;
        let qty = parseFloat(r.find('.qty').val()) || 1;

        let area = h * w;
        r.find('.area').val(area ? area.toFixed(2) : '');
        r.find('.item-total').val((area * rate * qty).toFixed(2));

        calcGrand();
    }

    $(document).on('input change', '.height,.width,.unit,.rate,.qty', e => {
        calcRow($(e.target).closest('tr'));
    });

    $(document).on('click', '.qty-plus', e => {
        let r = $(e.target).closest('tr');
        r.find('.qty').val(+r.find('.qty').val() + 1);
        calcRow(r);
    });

    $(document).on('click', '.qty-minus', e => {
        let r = $(e.target).closest('tr');
        r.find('.qty').val(Math.max(1, +r.find('.qty').val() - 1));
        calcRow(r);
    });

    $('.add-row').click(() => {
        let r = $('.sale-row:first').clone();
        r.find('input').val('');
        r.find('.qty').val(1);
        $('#saleTableBody').append(r);
    });

    $(document).on('click', '.remove-row', e => {
        if ($('.sale-row').length > 1) {
            $(e.target).closest('tr').remove();
            calcGrand();
        }
    });

    function calcGrand() {
        let g = 0;
        $('.item-total').each((_, e) => g += +e.value || 0);
        let d = +$('[name="gross_discount"]').val() || 0;
        let net = g - d;
        $('#grandTotal').val(g.toFixed(2));
        $('#netAmount').val(net.toFixed(2));
        let adv = +$('#advance').val() || 0;
        $('#remaining').val((net - adv).toFixed(2));
    }

    $('#advance,[name="gross_discount"]').on('input', calcGrand);

    $('form').on('submit', function () {
        calcGrand();

        if (!$('#netAmount').val() || $('#netAmount').val() <= 0) {
            Swal.fire('Error', 'Please add at least one item', 'error');
            return false;
        }
    });
</script>