@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>StockOut List</h4>
                    <h6>Manage StockOut</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addStockOutModal">
                        <img src="assets/img/icons/plus.svg" class="me-1" alt="img">Add StockOut
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Invoice #</th>
                                    <th>Customer Name</th>
                                    <th>Current Stock</th>
                                    <th>Close Stock</th>
                                    <th>Total Stock Out</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockOuts as $key => $stockOut)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $stockOut->product->item_name ?? 'N/A' }}</td>
                                        <td>{{ $stockOut->localSale->invoice_number ?? '-' }}</td>
                                        <td>{{ $stockOut->localSale->customer->shop_name ?? '-' }}</td>
                                        <td>{{ number_format($stockOut->current_stock, 0) }}</td>
                                        <td>{{ number_format($stockOut->close_stock, 0) }}</td>
                                        <td class="text-danger fw-bold">{{ number_format($stockOut->total_stock, 0) }}</td>
                                        <td>{{ $stockOut->created_at->format('d-M-Y') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary editStockOutBtn"
                                                data-id="{{ $stockOut->id }}" data-product="{{ $stockOut->product_id }}"
                                                data-localsale="{{ $stockOut->local_sales_id }}"
                                                data-current="{{ $stockOut->current_stock }}"
                                                data-close="{{ $stockOut->close_stock }}"
                                                data-height="{{ $stockOut->product->height ?? '' }}"
                                                data-width="{{ $stockOut->product->width ?? '' }}" data-bs-toggle="modal"
                                                data-bs-target="#editStockOutModal">
                                                Edit
                                            </button>

                                            <button class="btn btn-sm btn-danger deleteStockOutBtn"
                                                data-id="{{ $stockOut->id }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add StockOut Modal -->
<div class="modal fade" id="addStockOutModal" tabindex="-1" aria-labelledby="addStockOutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add StockOut</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('store-stockout') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Invoice Number <span class="text-danger">*</span></label>
                            <select class="form-control" name="local_sales_id" id="add_local_sales_id" required>
                                <option value="">Select Invoice</option>
                                @foreach($localSales as $sale)
                                    <option value="{{ $sale->id }}"
                                        data-customer="{{ $sale->customer->shop_name ?? 'N/A' }}">
                                        {{ $sale->invoice_number }} - {{ $sale->customer->shop_name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control bg-light" id="add_customer_name" readonly>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Height</th>
                                    <th>Width</th>
                                    <th>Current Stock</th>
                                    <th>Close Stock</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                <tr class="product-row">
                                    <td>
                                        <select class="form-control product-select" name="products[0][product_id]"
                                            required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-height="{{ $product->height }}"
                                                    data-width="{{ $product->width }}">
                                                    {{ $product->item_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control bg-light height-input" readonly></td>
                                    <td><input type="text" class="form-control bg-light width-input" readonly></td>
                                    <td><input type="number" class="form-control current-stock"
                                            name="products[0][current_stock]" min="0" required></td>
                                    <td><input type="number" class="form-control close-stock"
                                            name="products[0][close_stock]" min="0" required></td>
                                    <td><input type="text" class="form-control bg-light total-display" readonly
                                            value="0"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row"
                                            disabled>Delete</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info">
                        <strong>Grand Total Stock Out:</strong> <span id="grandTotal">0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save StockOut</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit StockOut Modal -->
<div class="modal fade" id="editStockOutModal" tabindex="-1" aria-labelledby="editStockOutModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit StockOut</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('update-stockout') }}" method="POST">
                @csrf
                <input type="hidden" name="stockout_id" id="edit_stockout_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Invoice Number <span class="text-danger">*</span></label>
                            <select class="form-control" name="local_sales_id" id="edit_local_sales_id" required>
                                <option value="">Select Invoice</option>
                                @foreach($localSales as $sale)
                                    <option value="{{ $sale->id }}"
                                        data-customer="{{ $sale->customer->shop_name ?? 'N/A' }}">
                                        {{ $sale->invoice_number }} - {{ $sale->customer->shop_name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control bg-light" id="edit_customer_name" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-control" name="product_id" id="edit_product_id" required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-height="{{ $product->height }}"
                                        data-width="{{ $product->width }}">
                                        {{ $product->item_name }} ({{ $product->height }} x {{ $product->width }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Height</label>
                            <input type="text" class="form-control bg-light" id="edit_height" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Width</label>
                            <input type="text" class="form-control bg-light" id="edit_width" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Current Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="current_stock" id="edit_current_stock"
                                min="0" step="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Close Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="close_stock" id="edit_close_stock" min="0"
                                step="1" required>
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong>Total Stock Out:</strong> <span id="edit_total_display">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update StockOut</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
    $(document).ready(function () {
        let rowIndex = 1;

        // Add Modal - Invoice Change
        $('#add_local_sales_id').on('change', function () {
            let selected = $(this).find('option:selected');
            $('#add_customer_name').val(selected.data('customer') || '-');
        });

        // Add Modal - Product Change (Auto add new row)
        $(document).on('change', '.product-select', function () {
            let selected = $(this).find('option:selected');
            let row = $(this).closest('tr');
            row.find('.height-input').val(selected.data('height') || '-');
            row.find('.width-input').val(selected.data('width') || '-');

            // Auto add new row when product is selected in last row
            let isLastRow = row.is('#productTableBody tr:last');
            if (isLastRow && $(this).val() != '') {
                addNewRow();
            }
        });

        // Add Modal - Calculate Total
        $(document).on('input', '.current-stock, .close-stock', function () {
            let row = $(this).closest('tr');
            let current = parseInt(row.find('.current-stock').val()) || 0;
            let close = parseInt(row.find('.close-stock').val()) || 0;
            let total = current - close;
            row.find('.total-display').val(total);
            calculateGrandTotal();
        });

        function calculateGrandTotal() {
            let grandTotal = 0;
            $('#productTableBody tr').each(function () {
                let total = parseInt($(this).find('.total-display').val()) || 0;
                grandTotal += total;
            });
            $('#grandTotal').text(grandTotal);
        }

        // Add Row Button
        $('#addRowBtn').on('click', function () {
            let newRow = `
                <tr class="product-row">
                    <td>
                        <select class="form-control product-select" name="products[${rowIndex}][product_id]" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                    data-height="{{ $product->height }}"
                                    data-width="{{ $product->width }}">
                                    {{ $product->item_name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" class="form-control bg-light height-input" readonly></td>
                    <td><input type="text" class="form-control bg-light width-input" readonly></td>
                    <td><input type="number" class="form-control current-stock" name="products[${rowIndex}][current_stock]" min="0" required></td>
                    <td><input type="number" class="form-control close-stock" name="products[${rowIndex}][close_stock]" min="0" required></td>
                    <td><input type="text" class="form-control bg-light total-display" readonly value="0"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Delete</button></td>
                </tr>
            `;
            $('#productTableBody').append(newRow);
            rowIndex++;
            updateRemoveButtons();
        });

        // Remove Row
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
            updateRemoveButtons();
            calculateGrandTotal();
        });

        function updateRemoveButtons() {
            let rowCount = $('#productTableBody tr').length;
            if (rowCount === 1) {
                $('.remove-row').prop('disabled', true);
            } else {
                $('.remove-row').prop('disabled', false);
            }
        }

        // Edit Modal
        $(document).on("click", ".editStockOutBtn", function () {
            let id = $(this).data("id");
            let productId = $(this).data("product");
            let localSaleId = $(this).data("localsale");
            let current = parseInt($(this).data("current")) || 0;
            let close = parseInt($(this).data("close")) || 0;
            let height = $(this).data("height");
            let width = $(this).data("width");

            $('#edit_stockout_id').val(id);
            $('#edit_product_id').val(productId);
            $('#edit_local_sales_id').val(localSaleId).trigger('change');
            $('#edit_current_stock').val(current);
            $('#edit_close_stock').val(close);
            $('#edit_height').val(height || '-');
            $('#edit_width').val(width || '-');

            let total = current - close;
            $('#edit_total_display').text(total);
        });

        $('#edit_local_sales_id').on('change', function () {
            let selected = $(this).find('option:selected');
            $('#edit_customer_name').val(selected.data('customer') || '-');
        });

        $('#edit_product_id').on('change', function () {
            let selected = $(this).find('option:selected');
            $('#edit_height').val(selected.data('height') || '-');
            $('#edit_width').val(selected.data('width') || '-');
        });

        $('#edit_current_stock, #edit_close_stock').on('input', function () {
            let current = parseInt($('#edit_current_stock').val()) || 0;
            let close = parseInt($('#edit_close_stock').val()) || 0;
            let total = current - close;
            $('#edit_total_display').text(total);
        });

        // Delete
        $(document).on("click", ".deleteStockOutBtn", function (e) {
            e.preventDefault();
            let id = $(this).data("id");
            let deleteUrl = "{{ route('delete-stockout') }}";

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        type: "DELETE",
                        data: { id: id },
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                        },
                        success: function (response) {
                            Swal.fire("Deleted!", response.success, "success")
                                .then(() => location.reload());
                        },
                        error: function (xhr) {
                            Swal.fire("Error!", "Something went wrong!", "error");
                        }
                    });
                }
            });
        });

    });
</script>