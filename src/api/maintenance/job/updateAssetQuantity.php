<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:ASSET_QUANTITY") or !isset($_POST['maintenanceJobsAssets_id']) or !isset($_POST['quantityInMaintenance'])) die("404");

// Validate quantity is non-negative integer
$quantity = intval($_POST['quantityInMaintenance']);
if ($quantity < 0) finish(false, ["code" => "INVALID-QUANTITY", "message" => "Quantity in maintenance cannot be negative"]);

$DBLIB->where("maintenanceJobsAssets.maintenanceJobsAssets_id", $_POST['maintenanceJobsAssets_id']);
$DBLIB->join("maintenanceJobs", "maintenanceJobsAssets.maintenanceJobs_id=maintenanceJobs.maintenanceJobs_id", "LEFT");
$jobAsset = $DBLIB->getOne("maintenanceJobsAssets", ["maintenanceJobsAssets_id", "maintenanceJobsAssets_quantity", "maintenanceJobs.instances_id"]);
if (!$jobAsset or $jobAsset['instances_id'] != $AUTH->data['instance']['instances_id']) finish(false, ["code" => "NOT-FOUND", "message" => "Job asset not found"]);

// Validate that quantity in maintenance doesn't exceed total quantity
if ($quantity > $jobAsset['maintenanceJobsAssets_quantity']) {
    finish(false, ["code" => "QUANTITY-EXCEEDS", "message" => "Quantity in maintenance cannot exceed total quantity in job"]);
}

$DBLIB->where("maintenanceJobsAssets_id", $_POST['maintenanceJobsAssets_id']);
$update = $DBLIB->update("maintenanceJobsAssets", ["maintenanceJobsAssets_quantityInMaintenance" => $quantity]);
if (!$update) finish(false, ["code" => "UPDATE-FAILED", "message" => "Could not update quantity"]);

$bCMS->auditLog("UPDATE-QUANTITY", "maintenanceJobsAssets", $_POST['maintenanceJobsAssets_id'], $AUTH->data['users_userid'], null, null, null);

finish(true);

/** @OA\Post(
 *     path="/maintenance/job/updateAssetQuantity.php", 
 *     summary="Update Asset Quantity in Maintenance Job", 
 *     description="Update the quantity of a bulk asset in maintenance  
Requires Instance Permission MAINTENANCE_JOBS:EDIT:ASSET_QUANTITY
", 
 *     operationId="updateAssetQuantity", 
 *     tags={"maintenanceJobs"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="default", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *                 @OA\Property(
 *                     property="error", 
 *                     type="array", 
 *                     description="An Array containing an error code and a message",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="maintenanceJobsAssets_id",
 *         in="query",
 *         description="Job asset ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 *     @OA\Parameter(
 *         name="quantityInMaintenance",
 *         in="query",
 *         description="Quantity in maintenance",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 * )
 */
