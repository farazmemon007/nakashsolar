<?php

namespace App\Console\Commands;

use App\Models\Purchase;
use Illuminate\Console\Command;

class FixDuplicateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchases:fix-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix duplicate invoice numbers and reset to proper sequence';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for duplicate invoice numbers...');

        // Get all purchases (including soft-deleted)
        $purchases = Purchase::withTrashed()->get();
        
        $this->info("Total purchases found: " . $purchases->count());

        // Count invoices
        $invoiceCount = $purchases->groupBy('invoice_number')->count();
        $this->info("Unique invoices: " . $invoiceCount);

        // Find duplicates
        $duplicates = $purchases->groupBy('invoice_number')
            ->filter(function ($group) {
                return $group->count() > 1;
            });

        if ($duplicates->count() > 0) {
            $this->error('❌ Found duplicate invoices:');
            foreach ($duplicates as $invoice => $items) {
                $this->error("  - {$invoice}: {$items->count()} records");
                foreach ($items as $item) {
                    $status = $item->deleted_at ? '(DELETED)' : '(ACTIVE)';
                    $this->line("    ID: {$item->id}, Vendor: {$item->party_name}, Amount: {$item->grand_total} {$status}");
                }
            }

            if ($this->confirm('Do you want to permanently delete soft-deleted duplicate invoices?')) {
                foreach ($duplicates as $invoice => $items) {
                    // Keep the first active one, delete soft-deleted ones
                    $activeItems = $items->whereNull('deleted_at');
                    $deletedItems = $items->where('deleted_at', '!=', null);

                    if ($activeItems->count() > 0 && $deletedItems->count() > 0) {
                        // Permanently delete the soft-deleted ones
                        foreach ($deletedItems as $item) {
                            $item->forceDelete();
                            $this->warn("  ✓ Permanently deleted Purchase ID: {$item->id}");
                        }
                    }
                }
                $this->info('✅ Cleaned up duplicate soft-deleted invoices');
            }
        } else {
            $this->info('✅ No duplicate invoices found');
        }

        // Show current invoice sequence
        $this->info("\n📊 Current invoice sequence:");
        $invoices = Purchase::withTrashed()
            ->where('invoice_number', 'like', 'INVPURC-%')
            ->orderBy('id')
            ->get()
            ->pluck('invoice_number')
            ->unique()
            ->sort();

        foreach ($invoices as $invoice) {
            $this->line("  - {$invoice}");
        }

        $this->info("\n✅ Invoice check complete!");
    }
}
