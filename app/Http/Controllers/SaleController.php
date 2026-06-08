<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Distributor;
use App\Models\DistributorLedger;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Salesman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function add_sale()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $Distributors = Distributor::where('admin_or_user_id', $userId)->get();
            $categories = Category::where('admin_or_user_id', $userId)->get();
            $Staffs = Salesman::where('admin_or_user_id', $userId)->get();

            return view('admin_panel.sale.add_sale', compact('Distributors', 'categories', 'Staffs'));
        } else {
            return redirect()->back();
        }
    }

    public function store_sale(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $invoiceNo = Sale::generateSaleInvoiceNo();
            $request->validate([
                'Date' => 'required|date',
                'Booker' => 'required|string',
                'Saleman' => 'required|string',
                'grand_total' => 'required|numeric',
                'discount_value' => 'required|numeric',
                'scheme_value' => 'required|numeric',
                'net_amount' => 'required|numeric',
                'item_name' => 'required|array',
                'pcs' => 'required|array',
                'rate' => 'required|array',
                'amount' => 'required|array',
            ]);

            // Sale Data Save
            $sale = Sale::create([
                'admin_or_user_id' => $userId,
                'invoice_number' => $invoiceNo,
                'Date' => $request->Date,
                'distributor_id' => $request->distributor_id,
                'distributor_city' => $request->distributor_city,
                'distributor_area' => $request->distributor_area,
                'distributor_address' => $request->distributor_address,
                'distributor_phone' => $request->distributor_phone,
                'Booker' => $request->Booker,
                'Saleman' => $request->Saleman,
                'category' => json_encode([]),
                'subcategory' => json_encode([]),
                'code' => json_encode([]),
                'item' => json_encode($request->item_name),
                'size' => json_encode([]),
                'pcs_carton' => json_encode([]),
                'carton_qty' => json_encode([]),
                'pcs' => json_encode($request->pcs),
                'liter' => json_encode([]),
                'rate' => json_encode($request->rate),
                'discount' => json_encode([]),
                'amount' => json_encode($request->amount),
                'grand_total' => $request->grand_total,
                'discount_value' => $request->discount_value,
                'scheme_value' => $request->scheme_value,
                'net_amount' => $request->net_amount,
            ]);
            // Stock Update Logic
            foreach ($request->item_name as $index => $item_name) {
                $product = Product::where('item_name', $item_name)->first();
                if ($product) {
                    $pcsSold = (int) $request->pcs[$index];

                    $product->initial_stock -= $pcsSold;

                    // Ensure stock doesn't go negative
                    $product->initial_stock = max($product->initial_stock, 0);

                    $product->save();
                }
            }

            // Fetch previous balance for distributor
            $previousBalance = DistributorLedger::where('distributor_id', $request->distributor_id)
                ->value('closing_balance') ?? 0; // If no previous balance, start from 0

            // Calculate new balances
            $newPreviousBalance = $request->net_amount;
            $newClosingBalance = $previousBalance + $request->net_amount;

            // Update or create distributor ledger
            DistributorLedger::updateOrCreate(
                ['distributor_id' => $request->distributor_id],
                [
                    'distributor_id' => $request->distributor_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $newPreviousBalance,
                    'closing_balance' => $newClosingBalance,
                    'updated_at' => Carbon::now(),
                ]
            );

            foreach ($request->item_name as $index => $item_name) {
                $pcs = (int) $request->pcs[$index];
                $rate = $request->rate[$index];

                $distributorProduct = \App\Models\DistributorProduct::where([
                    'distributor_id' => $request->distributor_id,
                    'item' => $item_name,
                ])->first();

                if ($distributorProduct) {
                    // Update existing stock
                    $distributorProduct->pcs += $pcs;

                    // Update initial stock
                    $distributorProduct->initial_stock += $pcs;

                    $distributorProduct->save();
                } else {
                    // Create new stock entry
                    \App\Models\DistributorProduct::create([
                        'distributor_id' => $request->distributor_id,
                        'category' => '',
                        'subcategory' => '',
                        'code' => '',
                        'item' => $item_name,
                        'size' => '',
                        'price' => $rate,
                        'pcs_carton' => 0,
                        'carton_quantity' => 0,
                        'pcs' => $pcs,
                        'initial_stock' => $pcs, // new field
                    ]);
                }
            }

            return redirect()->route('sale.invoice', $sale->id)->with('success', 'Sale recorded successfully and stock updated!');
        } else {
            return redirect()->back();
        }
    }

    public function all_sale()
    {
        if (Auth::id()) {
            $user = Auth::user();
            if ($user->usertype === 'admin') {
                $Sales = Sale::where('admin_or_user_id', $user->id)
                    ->with('distributor')
                    ->get();
            } elseif ($user->usertype === 'distributor') {
                $Sales = Sale::where('distributor_id', $user->user_id)
                    ->with('distributor')
                    ->get();
            } else {
                return redirect()->back()->with('error', 'Unauthorized access');
            }

            return view('admin_panel.sale.all_sale', compact('Sales'));
        } else {
            return redirect()->back();
        }
    }

    public function show_sale($id)
    {
        if (Auth::id()) {
            $sale = Sale::findOrFail($id);

            return view('admin_panel.sale.show_sale', compact('sale'));
        } else {
            return redirect()->back();
        }
    }

    public function saleInvoice($id)
    {
        $sale = Sale::with('distributor')->findOrFail($id);

        $distributorLedger = null;

        // Agar is sale me distributor_id hai to ledger dhoondo
        if ($sale->distributor_id) {
            $distributorLedger = \App\Models\DistributorLedger::where('admin_or_user_id', $sale->admin_or_user_id)
                ->where('distributor_id', $sale->distributor_id)
                ->latest('id') // most recent entry
                ->first();
        }

        return view('admin_panel.sale.invoice', compact('sale', 'distributorLedger'));
    }

    public function saleEdit($id)
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $Distributors = Distributor::where('admin_or_user_id', $userId)->get();
            $categories = Category::where('admin_or_user_id', $userId)->get();  // all categories from DB
            $Staffs = Salesman::where('admin_or_user_id', $userId)->get();
            $original = Sale::findOrFail($id);

            return view('admin_panel.sale.edit_sale', compact('Distributors', 'categories', 'Staffs', 'original'));
        } else {
            return redirect()->back();
        }
    }

    public function saleupdate(Request $request, $id)
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $request->validate([
                'Date' => 'required|date',
                'Booker' => 'required|string',
                'Saleman' => 'required|string',
                'grand_total' => 'required|numeric',
                'discount_value' => 'required|numeric',
                'scheme_value' => 'required|numeric',
                'net_amount' => 'required|numeric',
                'item_name' => 'required|array',
                'pcs' => 'required|array',
                'rate' => 'required|array',
                'amount' => 'required|array',
            ]);

            $sale = Sale::findOrFail($id);

            $oldItems = json_decode($sale->item, true) ?? [];
            $oldAmounts = json_decode($sale->amount, true) ?? [];

            // STEP 2: NEW values from request
            $newItems = $request->item_name;
            $newAmounts = $request->amount;

            // STEP 3: Calculate difference before updating
            $diffAmount = 0;

            // Newly added items
            foreach ($newItems as $index => $itemName) {
                if (! in_array($itemName, $oldItems)) {
                    $diffAmount += (float) $newAmounts[$index];
                }
            }
            // Removed items
            foreach ($oldItems as $index => $itemName) {
                if (! in_array($itemName, $newItems)) {
                    $diffAmount -= (float) $oldAmounts[$index];
                }
            }

            // STEP 4: Update Distributor Ledger (only apply diff)
            $ledger = DistributorLedger::where('distributor_id', $request->distributor_id)->first();

            if ($ledger) {
                $ledger->closing_balance = $ledger->closing_balance + $diffAmount;
                $ledger->previous_balance = $request->net_amount;
                $ledger->admin_or_user_id = $userId;
                $ledger->updated_at = now();
                $ledger->save();
            } else {
                DistributorLedger::create([
                    'distributor_id' => $request->distributor_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $request->net_amount,
                    'closing_balance' => $request->net_amount,
                    'updated_at' => now(),
                ]);
            }

            // Revert Old Stock Before Updating with new data
            $oldPcs = json_decode($sale->pcs, true) ?? [];
            foreach ($oldItems as $index => $itemName) {
                $product = Product::where('item_name', $itemName)->first();
                if ($product) {
                    $pcsToRevert = (int) ($oldPcs[$index] ?? 0);
                    $product->initial_stock += $pcsToRevert;
                    $product->save();
                }
            }

            // Update sale data
            $sale->update([
                'Date' => $request->Date,
                'distributor_id' => $request->distributor_id,
                'distributor_city' => $request->distributor_city,
                'distributor_area' => $request->distributor_area,
                'distributor_address' => $request->distributor_address,
                'distributor_phone' => $request->distributor_phone,
                'Booker' => $request->Booker,
                'Saleman' => $request->Saleman,
                'category' => json_encode([]),
                'subcategory' => json_encode([]),
                'code' => json_encode([]),
                'item' => json_encode($request->item_name),
                'size' => json_encode([]),
                'pcs_carton' => json_encode([]),
                'carton_qty' => json_encode([]),
                'pcs' => json_encode($request->pcs),
                'liter' => json_encode([]),
                'rate' => json_encode($request->rate),
                'discount' => json_encode([]),
                'amount' => json_encode($request->amount),
                'grand_total' => $request->grand_total,
                'discount_value' => $request->discount_value,
                'scheme_value' => $request->scheme_value,
                'net_amount' => $request->net_amount,
            ]);

            // Update stock quantities based on new data
            foreach ($request->item_name as $index => $item_name) {
                $product = Product::where('item_name', $item_name)->first();
                if ($product) {
                    $pcsSold = (int) ($request->pcs[$index] ?? 0);

                    $product->initial_stock -= $pcsSold;

                    // Prevent negative stock
                    $product->initial_stock = max($product->initial_stock, 0);

                    $product->save();
                }
            }

            return redirect()->route('sale.invoice', $sale->id)->with('success', 'Sale updated successfully and stock updated!');
        } else {
            return redirect()->back();
        }
    }

    public function delete($id)
    {
        $sale = Sale::findOrFail($id);

        $distributorId = $sale->distributor_id;
        $netAmount = $sale->net_amount;

        // Step 1: Decode product-related arrays
        $items = json_decode($sale->item);
        $pcs = json_decode($sale->pcs);

        // Step 2: Loop through all products in the sale
        for ($i = 0; $i < count($items); $i++) {
            $product = Product::where('item_name', $items[$i])->first();

            if ($product) {
                $pcsReturned = (int) $pcs[$i];

                // Restore stock as it was reduced during sale
                $product->initial_stock += $pcsReturned;

                $product->save();
            }
        }

        // Step 3: Delete the sale
        $sale->forceDelete();

        // Step 4: Update distributor ledger
        $ledger = DistributorLedger::where('distributor_id', $distributorId)->latest()->first();
        if ($ledger) {
            $ledger->closing_balance -= $netAmount;
            $ledger->save();
        }

        return redirect()->back()->with('success', 'Sale deleted, stock restored, and ledger adjusted.');
    }
}