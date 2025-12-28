<?php

namespace App\Http\Controllers;

use App\Models\LocalSale;
use App\Models\Product;
use App\Models\StockOut;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockOutController extends Controller
{
    public function stockout()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $products = Product::all();

            $localSales = LocalSale::with('customer')
                ->select('id', 'invoice_number', 'customer_id')
                ->orderBy('created_at', 'desc')
                ->get();

            $stockOuts = StockOut::with(['product', 'localSale.customer'])
                ->orderBy('created_at', 'desc')
                ->get();

            return view('admin_panel.stockOut.stockout', [
                'stockOuts' => $stockOuts,
                'products' => $products,
                'localSales' => $localSales,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store_stockout(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $request->validate([
                'local_sales_id' => 'required|exists:local_sales,id',
                'products' => 'required|array',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.current_stock' => 'required|numeric',
                'products.*.close_stock' => 'required|numeric',
            ]);

            foreach ($request->products as $product) {
                $total_stock = $product['current_stock'] - $product['close_stock'];

                StockOut::create([
                    'admin_or_user_id' => $userId,
                    'product_id' => $product['product_id'],
                    'local_sales_id' => $request->local_sales_id,
                    'current_stock' => $product['current_stock'],
                    'close_stock' => $product['close_stock'],
                    'total_stock' => $total_stock,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            return redirect()->back()->with('success', 'StockOut created successfully');
        } else {
            return redirect()->back();
        }
    }

    public function update_stockout(Request $request)
    {
        $request->validate([
            'stockout_id' => 'required|exists:stock_outs,id',
            'product_id' => 'required|exists:products,id',
            'local_sales_id' => 'required|exists:local_sales,id',
            'current_stock' => 'required|numeric',
            'close_stock' => 'required|numeric',
        ]);

        $total_stock = $request->current_stock - $request->close_stock;

        StockOut::where('id', $request->stockout_id)->update([
            'product_id' => $request->product_id,
            'local_sales_id' => $request->local_sales_id,
            'current_stock' => $request->current_stock,
            'close_stock' => $request->close_stock,
            'total_stock' => $total_stock,
            'updated_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'StockOut updated successfully');
    }

    public function delete_stockout(Request $request)
    {
        $stockout = StockOut::find($request->id);

        if ($stockout) {
            $stockout->delete();

            return response()->json(['success' => 'StockOut deleted successfully']);
        }

        return response()->json(['error' => 'StockOut not found'], 404);
    }
}
