<?php
require_once __DIR__ . '/../apiHeadSecure.php';

//Essentially a duplicate of headSecure

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("cmsPages_deleted", 0);
$DBLIB->where("cmsPages_archived", 0);
$DBLIB->where("cmsPages_showNav", 1);
if ($AUTH->data['instance']["instancePositions_id"]) $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))"); //If the user doesn't have a position - they're server admins
$allPages = $DBLIB->get("cmsPages", null, ["cmsPages_fontAwesome", "cmsPages_name", "cmsPages_id", "cmsPages_subOf", "cmsPages_navOrder"]);
if (!$allPages) $allPages = [];

$rootPages = [];
$subPagesByParent = [];

foreach ($allPages as $page) {
    if ($page['cmsPages_subOf'] === null) {
        $rootPages[] = $page;
    } else {
        $subPagesByParent[$page['cmsPages_subOf']][] = $page;
    }
}

// Sort root pages: navOrder ASC, id ASC
usort($rootPages, function ($a, $b) {
    if ($a['cmsPages_navOrder'] != $b['cmsPages_navOrder']) return $a['cmsPages_navOrder'] - $b['cmsPages_navOrder'];
    return $a['cmsPages_id'] - $b['cmsPages_id'];
});

$NAVIGATIONCMSPages = [];
foreach ($rootPages as $page) {
    $parentID = $page['cmsPages_id'];
    $subs = isset($subPagesByParent[$parentID]) ? $subPagesByParent[$parentID] : [];

    // Sort subpages: name ASC
    usort($subs, function ($a, $b) {
        return strcmp($a['cmsPages_name'], $b['cmsPages_name']);
    });

    $NAVIGATIONCMSPages[] = [
        "cmsPages_fontAwesome" => $page['cmsPages_fontAwesome'],
        "cmsPages_name" => $page['cmsPages_name'],
        "cmsPages_id" => $page['cmsPages_id'],
        "SUBPAGES" => array_map(function ($p) {
            return [
                "cmsPages_fontAwesome" => $p['cmsPages_fontAwesome'],
                "cmsPages_name" => $p['cmsPages_name'],
                "cmsPages_id" => $p['cmsPages_id']
            ];
        }, $subs)
    ];
}
finish(true,null,$NAVIGATIONCMSPages);

/** @OA\Get(
 *     path="/cms/list.php", 
 *     summary="List CMS Pages", 
 *     description="List all pages", 
 *     operationId="listPages", 
 *     tags={"cms"}, 
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
 *                 @OA\Property(
 *                     property="response", 
 *                     type="array", 
 *                     description="An Array containing all pages",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     )
 */