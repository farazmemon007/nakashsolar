@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Distributor Sales Details</h4>
                    <h6>Complete Distributor Sales Information</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    <h5 class="mb-3"><strong>Invoice #{{ $sale->invoice_number }}</strong></h5>

                    <table class="table">
                        <tr>
                            <th>Date:</th>
                            <td>{{ $sale->Date }}</td>
                        </tr>
                        <tr>
                            <th>Booker:</th>
                            <td>{{ $sale->Booker }}</td>
                        </tr>
                        <tr>
                            <th>Salesman:</th>
                            <td>{{ $sale->Saleman }}</td>
                        </tr>
                        <tr>
                            <th>Distributor:</th>
                            <td>{{ $sale->distributor_id }}</td>
                        </tr>
                        <tr>
                            <th>City:</th>
                            <td>{{ $sale->distributor_city }}</td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td>{{ $sale->distributor_address }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $sale->distributor_phone }}</td>
                        </tr>
                    </table>

                    <h5 class="mt-4"><strong>Sale Items</strong></h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Price/ Unit</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $items = json_decode($sale->item, true) ?? [];
                                    $pcs = json_decode($sale->pcs, true) ?? [];
                                    if(empty($pcs)) $pcs = json_decode($sale->carton_qty, true) ?? [];
                                    $rates = json_decode($sale->rate, true) ?? [];
                                    $amounts = json_decode($sale->amount, true) ?? [];
                                @endphp

                                @foreach ($items as $index => $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item }}</td>
                                        <td>{{ $pcs[$index] ?? '1' }}</td>
                                        <td>Pcs</td>
                                        <td>{{ number_format($rates[$index] ?? 0, 2) }}</td>
                                        <td>{{ number_format($amounts[$index] ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-4"><strong>Financial Summary</strong></h5>
                    <table class="table">
                        <tr>
                            <th>Grand Total:</th>
                            <td>{{ number_format($sale->grand_total, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Discount:</th>
                            <td>{{ number_format($sale->discount_value, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Net Amount:</th>
                            <td>{{ number_format($sale->net_amount, 2) }}</td>
                        </tr>
                    </table>

                    <a href="{{ route('all-sale') }}" class="btn btn-primary mt-3">Back to Sales List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')
