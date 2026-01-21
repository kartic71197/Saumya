<?php

namespace App\Services\Audit;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductAuditService
{
    /**
     * Log product update changes (including unit-level changes)
     *
     * @param \App\Models\Product $product
     * @param array $oldValues
     * @param array $newValues
     * @param array|null $oldUnits
     * @param array|null $newUnits
     * @return bool
     */
    public function logProductUpdate(Product $product, array $oldValues, array $newValues, ?array $oldUnits = [], ?array $newUnits = [])
    {
        try {
            Log::info('🧾 [ProductAudit] Starting audit for product update', [
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'organization_id' => auth()->user()->organization_id ?? null
            ]);

            $changed = [];

            // === Step 1: Compare base product attributes ===
            foreach ($newValues as $key => $value) {
                if (array_key_exists($key, $oldValues) && $oldValues[$key] != $value) {
                    $changed['product_fields'][$key] = [
                        'old' => $oldValues[$key],
                        'new' => $value
                    ];
                    Log::info("🔄 [ProductAudit] Field changed", [
                        'field' => $key,
                        'old' => $oldValues[$key],
                        'new' => $value
                    ]);
                }
            }

            // === Step 2: Compare Product Units ===
            $unitChanges = $this->compareUnits($oldUnits, $newUnits);

            if (!empty($unitChanges)) {
                $changed['unit_changes'] = $unitChanges;
                Log::info('📦 [ProductAudit] Unit-level changes detected', $unitChanges);
            }

            // Skip audit if nothing changed
            if (empty($changed)) {
                Log::info('✅ [ProductAudit] No product or unit changes detected — skipping audit log.');
                return false;
            }

            // Log before DB insert for debugging
            Log::info('🗂 [ProductAudit] Preparing to insert audit record', [
                'auditable_id' => $product->id,
                'changed_fields' => array_keys($changed),
                'changed_data' => $changed
            ]);

            // Extract only changed old/new values
            $oldChangedValues = [];
            $newChangedValues = [];

            // Product field-level changes
            if (isset($changed['product_fields'])) {
                foreach ($changed['product_fields'] as $field => $diff) {
                    $oldChangedValues[$field] = $diff['old'];
                    $newChangedValues[$field] = $diff['new'];
                }
            }

            // Unit-level changes (optional detailed logging)
            if (isset($changed['unit_changes'])) {
                $oldChangedValues['units'] = collect($changed['unit_changes'])
                    ->filter(fn($u) => $u['action'] !== 'added')
                    ->values();

                $newChangedValues['units'] = collect($changed['unit_changes'])
                    ->filter(fn($u) => $u['action'] !== 'deleted')
                    ->values();
            }

            // Insert only changed fields
            DB::table('audits')->insert([
                'user_id' => auth()->id(),
                'event' => 'Updated',
                'auditable_type' => 'Products',
                'auditable_id' => $product->id,
                'old_values' => json_encode($oldChangedValues),
                'new_values' => json_encode($newChangedValues),
                'created_at' => now(),
                'updated_at' => now(),
                'organization_id' => auth()->user()->organization_id ?? null,
            ]);


            Log::info('✅ [ProductAudit] Successfully logged audit for product', [
                'product_id' => $product->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('❌ [ProductAudit] Failed to log product audit', [
                'product_id' => $product->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Compare old and new product units
     *
     * @param array $oldUnits
     * @param array $newUnits
     * @return array
     */
    private function compareUnits(array $oldUnits, array $newUnits): array
    {
        $changes = [];

        Log::info('⚙️ [ProductAudit] Comparing product units', [
            'old_unit_count' => count($oldUnits),
            'new_unit_count' => count($newUnits)
        ]);

        // Normalize units by unit_id
        $oldById = collect($oldUnits)->keyBy('unit_id');
        $newById = collect($newUnits)->keyBy('unit_id');

        // Detect changed or updated units
        foreach ($newById as $unitId => $newUnit) {
            if (!isset($oldById[$unitId])) {
                $changes[] = [
                    'action' => 'added',
                    'unit_id' => $unitId,
                    'new' => $newUnit,
                ];
                Log::info("➕ [ProductAudit] New unit added", ['unit_id' => $unitId, 'new_unit' => $newUnit]);
            } else {
                $oldUnit = $oldById[$unitId];
                $diff = [];

                foreach (['operator', 'conversion_factor'] as $field) {
                    if ($oldUnit[$field] != $newUnit[$field]) {
                        $diff[$field] = [
                            'old' => $oldUnit[$field],
                            'new' => $newUnit[$field],
                        ];
                        Log::info("🔁 [ProductAudit] Unit field changed", [
                            'unit_id' => $unitId,
                            'field' => $field,
                            'old' => $oldUnit[$field],
                            'new' => $newUnit[$field]
                        ]);
                    }
                }

                if (!empty($diff)) {
                    $changes[] = [
                        'action' => 'updated',
                        'unit_id' => $unitId,
                        'changes' => $diff,
                    ];
                }
            }
        }

        // Detect deleted units
        foreach ($oldById as $unitId => $oldUnit) {
            if (!isset($newById[$unitId])) {
                $changes[] = [
                    'action' => 'deleted',
                    'unit_id' => $unitId,
                    'old' => $oldUnit,
                ];
                Log::info("🗑 [ProductAudit] Unit deleted", ['unit_id' => $unitId, 'old_unit' => $oldUnit]);
            }
        }

        Log::info('🧮 [ProductAudit] Finished comparing units', [
            'total_unit_changes' => count($changes)
        ]);

        return $changes;
    }
}
