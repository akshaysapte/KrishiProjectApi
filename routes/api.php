<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SellOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('adminLogin', [AuthController::class, 'adminLogin']);

Route::any('test', [AuthController::class, 'test']);


//dashboard
Route::get('dashboardCount', [DashboardController::class,'dashboardCount']);
Route::get('purchaseDashboardDetail', [DashboardController::class,'purchaseDashboardDetail']);
Route::get('sellDashboardDetail', [DashboardController::class,'sellDashboardDetail']);

Route::get('profitLossInfoDashboard', [DashboardController::class,'profitLossInfoDashboard']);

Route::get('papayaPalntedTalukas', [DashboardController::class,'papayaPalntedTalukas']);






//subadmin
Route::post('subAdminCreate', [AuthController::class, 'subAdminCreate']);
Route::get('subAdminDetail', [AuthController::class, 'subAdminDetail']);
Route::post('subAdminUpdate', [AuthController::class, 'subAdminUpdate']);
Route::get('subAdminDelete', [AuthController::class, 'subAdminDelete']);
Route::get('subAdminList', [AuthController::class, 'subAdminList']);

//invoice
Route::post('sellInvoiceCreate', [SellOrderController::class, 'sellInvoiceCreate']);
Route::get('sellInvoiceDetail', [SellOrderController::class, 'sellInvoiceDetail']);
Route::post('sellInvoiceUpdate', [SellOrderController::class, 'sellInvoiceUpdate']);
Route::get('sellInvoiceDelete', [SellOrderController::class, 'sellInvoiceDelete']);
Route::get('sellInvoiceList', [SellOrderController::class, 'sellInvoiceList']);


Route::post('purchaseInvoiceCreate', [PurchaseOrderController::class, 'purchaseInvoiceCreate']);
Route::get('purchaseInvoiceDetail', [PurchaseOrderController::class, 'purchaseInvoiceDetail']);
Route::post('purchaseInvoiceUpdate', [PurchaseOrderController::class, 'purchaseInvoiceUpdate']);
Route::get('purchaseInvoiceDelete', [PurchaseOrderController::class, 'purchaseInvoiceDelete']);
Route::get('purchaseInvoiceList', [PurchaseOrderController::class, 'purchaseInvoiceList']);

Route::post('vehicleInvoiceCreate', [PurchaseOrderController::class, 'vehicleInvoiceCreate']);
Route::get('vehicleInvoiceDetail', [PurchaseOrderController::class, 'vehicleInvoiceDetail']);
Route::post('vehicleInvoiceUpdate', [PurchaseOrderController::class, 'vehicleInvoiceUpdate']);
Route::get('vehicleInvoiceDelete', [PurchaseOrderController::class, 'vehicleInvoiceDelete']);
Route::get('vehicleInvoiceList', [PurchaseOrderController::class, 'vehicleInvoiceList']);

//varayaties types
Route::post('varietyCreate', [FarmerController::class, 'varietyCreate']);
Route::get('varietyList', [FarmerController::class, 'varietyList']);
Route::get('varietyDetail', [FarmerController::class, 'varietyDetail']);
Route::post('varietyUpdate', [FarmerController::class, 'varietyUpdate']);
Route::get('varietyDropdown', [FarmerController::class, 'varietyDropdown']);


//pyamnets
Route::post('paymentCreate', [SellOrderController::class, 'paymentCreate']);
Route::get('paymentList', [SellOrderController::class, 'paymentList']);
Route::get('paymentDetail', [SellOrderController::class, 'paymentDetail']);
Route::post('paymentUpdate', [SellOrderController::class, 'paymentUpdate']);
Route::get('paymentDelete', [SellOrderController::class, 'paymentDelete']);


// dropdown
Route::get('fruitDropdown', [FarmerController::class, 'fruitDropdown']);
Route::get('talukaDropdown', [FarmerController::class, 'talukaDropdown']);
Route::get('districtDropdown', [FarmerController::class, 'districtDropdown']);


//farmer
Route::post('createFarmer', [FarmerController::class, 'createFarmer']);
Route::get('FarmerList', [FarmerController::class, 'FarmerList']);
Route::get('FarmerDetail', [FarmerController::class, 'FarmerDetail']);
Route::post('farmerUpdate', [FarmerController::class, 'farmerUpdate']);

Route::post('createFarmerOrder', [FarmerController::class, 'createFarmerOrder']);

Route::get('farmerOrderList', [FarmerController::class, 'farmerOrderList']);



//farmer and merchant listing
Route::get('allFarmers', [FarmerController::class, 'allFarmers']);
Route::get('allMerchants', [MerchantController::class, 'allMerchants']);



//merchant
Route::post('createMerchant', [MerchantController::class, 'createMerchant']);
Route::get('merchantList', [MerchantController::class, 'merchantList']);
Route::get('merchantDetail', [MerchantController::class, 'merchantDetail']);
Route::post('merchantUpdate', [MerchantController::class, 'merchantUpdate']);

//sell
Route::post('sellOrderCreate', [SellOrderController::class, 'sellOrderCreate']);
Route::get('sellOrderList', [SellOrderController::class, 'sellOrderList']);
Route::get('sellOrderDetail', [SellOrderController::class, 'sellOrderDetail']);
Route::post('sellOrderUpdate', [SellOrderController::class, 'sellOrderUpdate']);
Route::get('sellOrderDelete', [SellOrderController::class, 'sellOrderDelete']);



//purchase
Route::post('purchaseOrderCreate', [PurchaseOrderController::class, 'purchaseOrderCreate']);
Route::get('purchaseOrderList', [PurchaseOrderController::class, 'purchaseOrderList']);
Route::get('purchaseOrderDetail', [PurchaseOrderController::class, 'purchaseOrderDetail']);
