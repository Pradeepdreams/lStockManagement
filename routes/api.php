<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\Customer\CustomerController;
use App\Http\Controllers\Admin\Employee\EmployeeController;
use App\Http\Controllers\Admin\Masters\AgentController;
use App\Http\Controllers\Admin\Masters\CityController;
use App\Http\Controllers\Admin\Masters\CountryController;
use App\Http\Controllers\Admin\Masters\StateController;
use App\Http\Controllers\Admin\Masters\AreaController;
use App\Http\Controllers\Admin\Masters\AttributeController;
use App\Http\Controllers\Admin\Masters\AttributeValueController;
use App\Http\Controllers\Admin\Masters\CategoryController;
use App\Http\Controllers\Admin\Masters\DiscountOnPurchaseController;
use App\Http\Controllers\Admin\Masters\GroupController;
use App\Http\Controllers\Admin\Masters\GstRegistrationTypeController;
use App\Http\Controllers\Admin\Masters\ItemController;
use App\Http\Controllers\Admin\Masters\LogisticController;
use App\Http\Controllers\Admin\Masters\PaymentTermController;
use App\Http\Controllers\Admin\Masters\PincodeController;
use App\Http\Controllers\Admin\Masters\QualificationController;
use App\Http\Controllers\Admin\Masters\SocialMediaController;
use App\Http\Controllers\Admin\Masters\TdsDetailController;
use App\Http\Controllers\Admin\Masters\TdsSectionController;
use App\Http\Controllers\Admin\Masters\VendorGroupController;
use App\Http\Controllers\Admin\PurchaseEntry\PurchaseEntryController;
use App\Http\Controllers\Admin\PurchaseOrder\PurchaseOrderController;
use App\Http\Controllers\Admin\StockItem\StockItemController;
use App\Http\Controllers\Admin\Vendor\VendorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleAssignController;
use App\Http\Controllers\RoleController;
use App\Http\Middleware\SetUserBranch;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('email/verify/{id}/{hash}', [AuthController::class, 'emailVerify'])->middleware(['signed'])->name('verification.verify');
Route::post('email/resend', [AuthController::class, 'resendVerificationMail'])->middleware(['auth:sanctum']);
Route::post('forgot-password', [AuthController::class, 'forgetPassword']);

Route::middleware(['auth:sanctum', SetUserBranch::class])->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'resetPassword']);

    // Roles & Permissions
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::get('/list', [RoleController::class, 'list']);
        Route::post('/', [RoleController::class, 'store']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::get('/permissions/user/{id}', [AuthController::class, 'getUserBranchRolesPermissions']);
        Route::post('/assign/permissions', [RoleController::class, 'assignPermissionsToRole']);
        Route::post('/user/assign/branch', [RoleAssignController::class, 'assignRolesToUser']);
        Route::get('/user/{userId}/branch/{branchId}', [RoleAssignController::class, 'getUserRolesForBranch']);
        Route::post('/user/remove/branch', [RoleAssignController::class, 'removeUserRolesFromBranch']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);
    });

    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::post('/', [PermissionController::class, 'store']);
        Route::get('/{id}', [PermissionController::class, 'show']);
        Route::put('/{id}', [PermissionController::class, 'update']);
        Route::delete('/{id}', [PermissionController::class, 'destroy']);
    });


    // Branch
    Route::prefix('branches')->group(function () {
        Route::get('/', [BranchController::class, 'index']);
        Route::get('/list', [BranchController::class, 'list']);
        Route::post('/', [BranchController::class, 'store']);
        Route::get('/{id}', [BranchController::class, 'show']);
        Route::put('/{id}', [BranchController::class, 'update']);
        Route::post('/switch', [AuthController::class, 'switchBranch']);
        Route::delete('/{id}', [BranchController::class, 'destroy']);
    });

    // Country
    Route::get('countries/list', [CountryController::class, 'list']);
    Route::apiResource('countries', CountryController::class);

    // State
    Route::get('states/list', [StateController::class, 'list']);
    Route::apiResource('states', StateController::class);

    // City
    Route::get('cities/list', [CityController::class, 'list']);
    Route::apiResource('cities', CityController::class);

    // Pincode
    Route::get('pincodes/list', [PincodeController::class, 'list']);
    Route::apiResource('pincodes', PincodeController::class);

    // Area
    Route::get('areas/list', [AreaController::class, 'list']);
    Route::apiResource('areas', AreaController::class);

    // Category
    Route::get('categories/gst-history', [CategoryController::class, 'getGstHistory']);
    Route::get('categories/hsn-history', [CategoryController::class, 'getHsnHistory']);
    Route::get('categories/list', [CategoryController::class, 'list']);
    Route::apiResource('categories', CategoryController::class);

    // Attribute
    Route::get('attributes/list', [AttributeController::class, 'list']);
    Route::apiResource('attributes', AttributeController::class);

    // Attribute Values
    Route::get('attribute-values/list', [AttributeValueController::class, 'list']);
    Route::apiResource('attribute-values', AttributeValueController::class);

    // Social Media
    Route::get('socialmedia/list', [SocialMediaController::class, 'list']);
    Route::apiResource('socialmedia', SocialMediaController::class);

    // Payment Terms
    Route::get('payment-terms/list', [PaymentTermController::class, 'list']);
    Route::apiResource('payment-terms', PaymentTermController::class);

    // Item
    Route::get('items/po/list/{id}', [ItemController::class, 'poList']);
    Route::get('items/list', [ItemController::class, 'list']);
    Route::get('items/attribute-values', [ItemController::class, 'getItemAttributeValues']);
    Route::apiResource('items', ItemController::class);

    // Agents
    Route::get('agents/list', [AgentController::class, 'list']);
    Route::apiResource('agents', AgentController::class);

    // Groups
    Route::get('groups/list', [GroupController::class, 'list']);
    Route::apiResource('groups', GroupController::class);

    // Vendor Groups
    Route::get('vendor-groups/list', [VendorGroupController::class, 'list']);
    Route::apiResource('vendor-groups', VendorGroupController::class);

    // Vendors
    Route::get('vendors/list', [VendorController::class, 'list']);
    Route::apiResource('vendors', VendorController::class);

    // Tds Details
    Route::get('tds-details/list', [TdsDetailController::class, 'list']);
    Route::apiResource('tds-details', TdsDetailController::class);

    // Tds Sections
    Route::get('tds-sections/list', [TdsSectionController::class, 'list']);
    Route::apiResource('tds-sections', TdsSectionController::class);

    // Gst Registration Type
    Route::get('gst-registration-types/list', [GstRegistrationTypeController::class, 'list']);
    Route::apiResource('gst-registration-types', GstRegistrationTypeController::class);

    // Activity Log
    Route::get('/activity-logs/model', [ActivityLogController::class, 'getByModel']);
    Route::get('/activity-logs/user', [ActivityLogController::class, 'getByUser']);


    // Employees
    Route::get('employees/list', [EmployeeController::class, 'list']);
    Route::apiResource('employees', EmployeeController::class);

    // Qualifications
    Route::get('qualifications/list', [QualificationController::class, 'list']);
    Route::apiResource('qualifications', QualificationController::class);

    // Logistics
    Route::get('logistics/list', [LogisticController::class, 'list']);
    Route::apiResource('logistics', LogisticController::class);

    // Purchase Order
    Route::get('purchase-orders/vendors/{id}', [PurchaseOrderController::class, 'vendorPendingPo']);
    Route::get('purchase-orders/pending', [PurchaseOrderController::class, 'pendingPo']);
    Route::get('purchase-orders/latest-number', [PurchaseOrderController::class, 'latestPo']);
    Route::get('purchase-orders/list', [PurchaseOrderController::class, 'list']);
    Route::apiResource('purchase-orders', PurchaseOrderController::class);

    // Purchase Entry
    Route::get('purchase-entries/history/{id}', [PurchaseEntryController::class, 'getHistory']);
    Route::put('purchase-entries/approve/{id}', [PurchaseEntryController::class, 'approveEntry']);
    Route::get('purchase-entries/pending', [PurchaseEntryController::class, 'pendingEntry']);
    Route::get('purchase-entries/latest-number', [PurchaseEntryController::class, 'latestEntry']);
    Route::match(['POST', 'PUT'], 'purchase-entries/update/{id}', [PurchaseEntryController::class, 'update']);
    Route::get('purchase-entries/list', [PurchaseEntryController::class, 'list']);
    Route::apiResource('purchase-entries', PurchaseEntryController::class);

    // Discount on Purchase
    Route::get('dicount-on-purchases/active/type', [DiscountOnPurchaseController::class, 'getDiscountType']);
    Route::get('discount-on-purchases/change/status', [DiscountOnPurchaseController::class, 'changeDiscountType']);
    Route::get('discount-on-purchases/active/percent', [DiscountOnPurchaseController::class, 'discountPercent']);
    Route::apiResource('discount-on-purchases', DiscountOnPurchaseController::class);


    // Stock Items with Barcode
    Route::apiResource('stock-items', StockItemController::class);

    // Barcode details
    Route::get('/barcode-details/{barcode}', [StockItemController::class, 'getByBarcode']);

    // Customer
    Route::get('customers/list', [CustomerController::class, 'list']);
    Route::apiResource('customers', CustomerController::class);
});
