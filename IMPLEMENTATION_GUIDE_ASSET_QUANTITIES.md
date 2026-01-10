# Asset Quantity Support Implementation Guide

## Implementation Status Summary

### ✅ Completed Phase 1 (Database & Core Logic)

1. **Database Migrations**
   - ✅ Migration 1: Added `assets_isQuantityBased` (boolean) and `assets_quantity` (int) to assets table
   - ✅ Migration 2: Created `maintenanceJobsAssets` junction table with proper FK relationships
   - ✅ Added `assetsAssignments_quantity` (int) to assetsAssignments table

2. **Asset Creation UI**
   - ✅ Added serialized/bulk asset type toggle in [newAsset.twig](src/newAsset.twig)
   - ✅ Toggle controls quantity input visibility (hidden for serialized, shown for bulk)
   - ✅ Updated [newAssetFromType.php](src/api/assets/newAssetFromType.php) to accept and store quantity fields
   - ✅ Updated [editAsset.php](src/api/assets/editAsset.php) to support editing quantities

3. **Pricing & Finance Calculations**
   - ✅ Updated [projects/data.php](src/api/projects/data.php) to multiply asset pricing by `assetsAssignments_quantity`
   - ✅ Updated asset value and mass calculations to handle quantities
   - ✅ Maintained backward compatibility (quantity defaults to 1)

4. **Asset Assignment**
   - ✅ Updated [projects/assets/assign.php](src/api/projects/assets/assign.php) to accept quantity parameter
   - ✅ Finance cache properly scales by quantity

5. **Maintenance Support**
   - ✅ Created [maintenance/job/updateAssetQuantity.php](src/api/maintenance/job/updateAssetQuantity.php) endpoint
   - ✅ Added `MAINTENANCE_JOBS:EDIT:ASSET_QUANTITY` permission in [Auth/instanceActions.php](src/common/libs/Auth/instanceActions.php)

6. **Barcode Enhancements**
   - ✅ Updated [barcodes/searchAsset.php](src/api/barcodes/searchAsset.php) to return quantity fields
   - ✅ Barcode search now includes `assets_isQuantityBased` and `assets_quantity` in response

---

## ⚠️ Remaining Implementation Tasks

### Phase 2: Asset Availability & Blocking

**Location**: [src/common/head.php](src/common/head.php) - `assetFlagsAndBlocks()` function

**Current Function** (lines ~120-130):
```php
function assetFlagsAndBlocks($assetid)
{
    global $DBLIB;
    $DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
    $DBLIB->where("(maintenanceJobs.maintenanceJobs_blockAssets = 1 OR maintenanceJobs.maintenanceJobs_flagAssets = 1)");
    $DBLIB->where("(FIND_IN_SET(" . $assetid . ", maintenanceJobs.maintenanceJobs_assets) > 0)");
    // ... returns BLOCK and FLAG arrays
}
```

**Required Changes**:
1. Create new `assetAvailability()` function that calculates:
   - `total_quantity`: Total quantity of asset
   - `assigned_quantity`: Sum of all assetsAssignments_quantity for the asset
   - `in_maintenance_quantity`: Sum of maintenanceJobsAssets_quantityInMaintenance
   - `available_quantity`: total - assigned - in_maintenance
   - Return availability info alongside flags/blocks

2. Update all queries that use `assetFlagsAndBlocks()` to also use new availability function
   - [assets.php](src/assets.php)
   - [asset.php](src/asset.php)
   - [api/assets/list.php](src/api/assets/list.php)

**Proposed Function Structure**:
```php
function assetAvailability($assetid)
{
    global $DBLIB;
    
    // Get asset quantity and type
    $DBLIB->where("assets_id", $assetid);
    $asset = $DBLIB->getOne("assets", ["assets_quantity", "assets_isQuantityBased"]);
    
    if (!$asset['assets_isQuantityBased']) {
        // Serialized assets: return 0 or 1
        return ["total" => 1, "assigned" => 0, "in_maintenance" => 0, "available" => /* check assignments */];
    }
    
    $total = $asset['assets_quantity'];
    
    // Sum assigned quantities
    $assigned = /* SUM assetsAssignments_quantity WHERE assets_id = $assetid AND deleted = 0 */;
    
    // Sum in-maintenance quantities  
    $in_maintenance = /* SUM maintenanceJobsAssets_quantityInMaintenance WHERE assets_id = $assetid AND deleted = 0 */;
    
    return [
        "total" => $total,
        "assigned" => $assigned,
        "in_maintenance" => $in_maintenance,
        "available" => $total - $assigned - $in_maintenance
    ];
}
```

---

### Phase 3: Barcode Scanning with Quantity

**Files to Modify**:
1. **[barcode.php](src/barcode.php)** - Barcode scanning page
2. **[barcodes/searchAsset.php](src/api/barcodes/searchAsset.php)** - API endpoint (DONE for data)
3. **UI JavaScript** - Add quantity modal prompt

**Implementation Steps**:

1. **Extend searchAsset Response** (already returns quantity fields):
   - Response includes `assets_isQuantityBased` and `assets_quantity`

2. **Add Quantity Prompt Modal** (in barcode.php):
   - When bulk asset detected, show modal with:
     - Asset name and quantity available
     - Quantity input spinner (0 to available_quantity)
     - Validation: "Only X units available (assigned: Y, in-maintenance: Z)"
     - Submit button to create assignment with quantity

3. **JavaScript Handler**:
   ```javascript
   // After barcode scan returns asset data
   if (response.assets_isQuantityBased == 1) {
       // Show quantity modal
       calculateAvailability(response.assets_id, function(availability) {
           showQuantityModal({
               asset: response,
               available: availability.available,
               assigned: availability.assigned,
               in_maintenance: availability.in_maintenance
           });
       });
   } else {
       // Create assignment with quantity=1
       createAssignment(response.assets_id, 1);
   }
   ```

4. **Create Assignment API Call**:
   - Existing [projects/assets/assign.php](src/api/projects/assets/assign.php) already supports quantity parameter
   - Pass `assetsAssignments_quantity` from modal input

---

### Phase 4: Maintenance UI Updates

**Files to Modify**:
1. **[maintenance.php](src/maintenance/maintenance.php)** - Job listing and detail
2. **[maintenance-newJob.twig](src/maintenance/maintenance-newJob.twig)** - Job creation
3. **[api/maintenance/job/addAsset.php](src/api/maintenance/job/addAsset.php)** - Asset adding
4. **[api/maintenance/searchAssets.php](src/api/maintenance/searchAssets.php)** - Asset search

**Implementation Steps**:

1. **Normalize maintenanceJobs_assets Storage**:
   - Current: CSV of asset IDs in maintenanceJobs_assets
   - New: Use maintenanceJobsAssets junction table
   - Data migration: Parse existing CSV into new table (can be in migration file)

2. **Add Asset Type Filter**:
   - In maintenance-newJob.twig, add dropdown before asset selector:
     ```twig
     <select id="assetTypeFilter" class="form-control">
         <option value="">All Asset Types</option>
         <!-- List of asset types -->
     </select>
     ```
   - Modify [api/maintenance/searchAssets.php](src/api/maintenance/searchAssets.php) to accept `assetTypes_id` parameter

3. **Add Quantity Fields for Bulk Assets**:
   - In job detail view (maintenance.php), for each asset in maintenanceJobsAssets:
     - If asset is bulk (assets_isQuantityBased = 1):
       - Show quantity input for total quantity in job
       - Show quantity input for quantity currently in maintenance
       - Show "Available: X units" calculated from assignments
     - If asset is serialized:
       - Show only checkbox/flag (current behavior)

4. **Update addAsset Endpoint**:
   - Change [api/maintenance/job/addAsset.php](src/api/maintenance/job/addAsset.php) to:
     - Accept `maintenanceJobsAssets_quantity` parameter
     - Insert into maintenanceJobsAssets table instead of updating CSV
     - Update `maintenanceJobsAssets_quantityInMaintenance` to match quantity (default)

---

### Phase 5: Project Asset Display

**File to Modify**: [project-assetsList.twig](src/project/project-assetsList.twig)

**Implementation Steps**:

1. **Asset Group Context Detection**:
   - When displaying assets, detect if asset is part of a group:
     ```twig
     {% set assetGroups = asset.assets_assetGroups|split(',') %}
     {% if assetGroups|length > 0 and assetGroups[0] is not empty %}
         <span class="badge badge-info">Part of: {{ assetGroup.assetGroups_name }}</span>
     {% endif %}
     ```

2. **Quantity Display**:
   - For bulk assets (asset.assets_isQuantityBased = 1):
     ```twig
     {{ assetAssignment.assetsAssignments_quantity }}x {{ asset.assetTypes_name }}
     ```
   - For serialized assets (standard display)

3. **Group Label Display**:
   - Show "Part of: GroupName" label for assets that are in groups
   - Only group assets together if they're in the same group context

4. **Separate Row Display**:
   - Bulk and serialized assets of same type show in separate rows
   - Clear visual distinction (different styling for bulk quantities)

---

### Phase 6: Unassignment & Substitution Updates

**Files to Modify**:
1. **[projects/assets/unassign.php](src/api/projects/assets/unassign.php)**
2. **[projects/assets/swap.php](src/api/projects/assets/swap.php)**
3. **[assets/substitutions.php](src/api/assets/substitutions.php)**

**Changes**:
1. Update unassign to handle `assetsAssignments_quantity` in finance cache adjustments
2. Update substitution logic to check availability using new assetAvailability() function
3. Ensure all finance adjustments multiply/divide by quantity appropriately

---

## Integration Checklist

### Before Testing Phase
- [ ] Database migrations run successfully
- [ ] Asset creation UI displays toggle correctly
- [ ] Quantity field hidden/shown appropriately
- [ ] Asset pricing calculations include quantity
- [ ] assetAvailability() function created and integrated
- [ ] Barcode scanning modal shows for bulk assets
- [ ] Maintenance interface normalizes to new table structure
- [ ] Project asset list displays group labels

### Testing Scenarios

1. **Create Serialized Asset**:
   - Toggle "Serialized Asset" (default)
   - Quantity field hidden
   - Create asset
   - Verify quantity = 1 in database

2. **Create Bulk Asset**:
   - Toggle "Bulk Asset"
   - Quantity field visible
   - Enter quantity = 10
   - Create asset
   - Verify quantity = 10, isQuantityBased = true

3. **Assign Bulk Asset to Project**:
   - Assign 5 units of bulk asset
   - Verify pricing = unit_price × 5
   - Verify availability = 10 - 5 = 5 remaining

4. **Barcode Scan Bulk Asset**:
   - Scan bulk asset QR code
   - Quantity modal appears
   - Show "Only 5 units available (assigned: 5, in-maintenance: 0)"
   - Enter 3 units
   - Verify new assignment with quantity = 3

5. **Maintenance Job with Bulk Asset**:
   - Add bulk asset to maintenance job
   - Enter quantity = 2
   - Enter quantity in maintenance = 2
   - Verify blocks quantity from project assignment

6. **Asset Groups with Mixed Types**:
   - Create group with serialized asset + bulk asset
   - Assign group to project
   - Verify both appear in project
   - Verify "Part of: GroupName" labels display

---

## API Endpoint Summary

### New Endpoints
- `POST /api/maintenance/job/updateAssetQuantity.php` - Update bulk asset quantity in maintenance

### Modified Endpoints
- `POST /api/projects/assets/assign.php` - Now accepts `assetsAssignments_quantity`
- `POST /api/barcodes/searchAsset.php` - Now returns quantity fields
- `POST /api/assets/newAssetFromType.php` - Now accepts `assets_isQuantityBased`, `assets_quantity`
- `POST /api/assets/editAsset.php` - Now accepts `assets_quantity`

### Endpoints Requiring Updates
- `POST /api/maintenance/job/addAsset.php` - Add quantity support
- `POST /api/maintenance/searchAssets.php` - Add assetTypes_id filter
- `POST /api/projects/assets/unassign.php` - Handle quantities in finance
- `POST /api/projects/assets/swap.php` - Check availability with new function

---

## Database Schema Reference

### assets table additions:
```
assets_isQuantityBased (boolean) - default false
assets_quantity (int) - default 1
```

### assetsAssignments table addition:
```
assetsAssignments_quantity (int) - default 1
```

### New table: maintenanceJobsAssets
```
maintenanceJobsAssets_id (PK, auto-increment)
maintenanceJobs_id (FK)
assets_id (FK)
maintenanceJobsAssets_quantity (int)
maintenanceJobsAssets_quantityInMaintenance (int)
maintenanceJobsAssets_deleted (tinyint)
UNIQUE(maintenanceJobs_id, assets_id)
```

---

## Backward Compatibility Notes

- All quantity fields default to 1, preserving existing behavior
- Serialized assets (assets_isQuantityBased = 0/false) function identically to pre-feature behavior
- Existing asset assignment logic works unchanged for serialized assets
- Finance calculations transparently scale by quantity (1× for serialized)
- assetFlagsAndBlocks() continues to work; new assetAvailability() is supplementary

---

## Configuration & Permissions

### Required Permission
- `MAINTENANCE_JOBS:EDIT:ASSET_QUANTITY` - Adjust quantities in maintenance jobs (added to instanceActions.php)

### No Configuration Needed
- Feature is per-asset (user chooses serialized vs bulk at creation)
- No instance-level settings required

---

## Commits Made
1. **WIP: Add asset quantity support - Phase 1** (6af4cad)
   - Database migrations
   - UI toggle and creation logic
   - Pricing calculations
   - Asset assignment quantity support
   - Maintenance API endpoint
   - Permission definition

---

## Next Steps for Implementer

1. **Implement assetAvailability() function** (Phase 2)
2. **Add barcode quantity modal** (Phase 3)
3. **Update maintenance UI and normalization** (Phase 4)
4. **Update project asset display** (Phase 5)
5. **Update remaining API endpoints** (Phase 6)
6. **Run comprehensive testing** against all scenarios
7. **Update API documentation** with new parameters

