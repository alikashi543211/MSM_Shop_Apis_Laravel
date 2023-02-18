<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Customer\CartController as CustomerCartController;
use App\Http\Controllers\Api\Customer\MenuController as CustomerMenuController;
use App\Http\Controllers\Api\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Api\Customer\SettingController as CustomerSettingController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\ProductCatalogueController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\ProductPricingController;
use App\Http\Controllers\Api\QuickBookController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\UserController;
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
// Authentication Routes
Route::prefix('auth')->group(function () {

    Route::post('login', [LoginController::class, 'login']);
    Route::post('forgot-password', [LoginController::class, 'forgotPassword']);
    Route::post('verify-forgot-password-code', [LoginController::class, 'verifyForgotPasswordCode']);
    Route::post('reset-password', [LoginController::class, 'resetPassword']);
    Route::post('send-otp', [LoginController::class, 'sendOtp']);
    Route::post('verify-otp', [LoginController::class, 'verifyOtp']);
    Route::post('send-email-otp', [LoginController::class, 'sendEmailOtp']);
    Route::post('send-mobile-otp', [LoginController::class, 'sendMobileOtp']);
    Route::post('verify-email-otp', [LoginController::class, 'verifyEmailOtp']);
    Route::post('verify-mobile-otp', [LoginController::class, 'verifyMobileOtp']);

});

Route::post('check/total', [ProductController::class, 'checkTotalPriceForCheckout']);
Route::post('verify-user-token', [UserController::class, 'verifyUserToken']);

// Shop Admin Apis
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'logout']);
    });


    Route::middleware(['checkPermissions'])->group(function () {

        // Users Routes
        Route::prefix('user')->group(function () {
            Route::post('listing', [UserController::class, 'listing']);
            Route::post('store', [UserController::class, 'store']);
            Route::post('update', [UserController::class, 'update']);
            Route::post('delete', [UserController::class, 'delete']);
            Route::post('change-status', [UserController::class, 'changeStatus']);

            Route::prefix('roles')->group(function () {
                Route::post('listing', [RoleController::class, 'listing']);
                Route::post('update', [RoleController::class, 'update']);
            });
            Route::prefix('permissions')->group(function () {
                Route::post('listing', [RoleController::class, 'permissionListing']);
            });

        });

        // Category Routes
        Route::prefix('category')->group(function () {
            Route::post('listing', [CategoryController::class, 'listing']);
            Route::post('store', [CategoryController::class, 'store']);
            Route::post('update', [CategoryController::class, 'update']);
            Route::post('delete', [CategoryController::class, 'delete']);
            Route::post('update-sorting', [CategoryController::class, 'updateSorting']);

            Route::prefix('tag')->group(function () {
                Route::post('store', [TagController::class, 'store']);
                Route::post('delete', [TagController::class, 'delete']);
            });

        });

        // Menu Routes
        Route::prefix('menu')->group(function () {
            Route::post('listing', [MenuController::class, 'listing']);
            Route::post('categories', [MenuController::class, 'categories']);
            Route::post('store', [MenuController::class, 'store']);
            Route::post('update', [MenuController::class, 'update']);
            Route::post('image-update', [MenuController::class, 'imageUpdate']);
            Route::post('delete', [MenuController::class, 'delete']);
            Route::post('change-status', [MenuController::class, 'changeStatus']);
            Route::post('update-sorting', [MenuController::class, 'updateSorting']);
            Route::post('remove-image', [MenuController::class, 'removeImage']);
        });

        // Product Routes
        Route::prefix('product')->group(function () {
            Route::post('listing', [ProductController::class, 'listing']);
            Route::post('store', [ProductController::class, 'store']);
            Route::post('excel-import/store', [ProductController::class, 'fileImport']);
            Route::post('excel-export/listing', [ProductController::class, 'fileExport']);
            Route::post('clone', [ProductController::class, 'clone']);
            Route::post('update', [ProductController::class, 'update']);
            Route::post('delete', [ProductController::class, 'delete']);
            Route::post('delete-attribute', [ProductController::class, 'deleteAttribute']);
            Route::post('detail', [ProductController::class, 'detail']);
            Route::post('change-status', [ProductController::class, 'changeStatus']);
            Route::post('change-buy-now', [ProductController::class, 'changeBuyNow']);
            Route::post('change-buying-options', [ProductController::class, 'changeBuyingOptions']);
            Route::post('filter-listing', [ProductController::class, 'filterListing']);
            Route::post('listing/update', [ProductController::class, 'updateProduct']);


            Route::prefix('image')->group(function () {
                Route::post('listing', [ProductImageController::class, 'listing']);
                Route::post('store', [ProductImageController::class, 'store']);
                Route::post('update', [ProductImageController::class, 'update']);
                Route::post('delete', [ProductImageController::class, 'delete']);
                Route::post('update-sorting', [ProductImageController::class, 'updateSorting']);
                Route::post('remove-image', [ProductImageController::class, 'removeImage']);
            });

            Route::prefix('catalogue')->group(function () {
                Route::post('store', [ProductCatalogueController::class, 'store']);
                Route::post('menu/store', [ProductCatalogueController::class, 'storeMenu']);
                Route::post('menu/delete', [ProductCatalogueController::class, 'deleteProductMenu']);
                Route::post('tag/store', [ProductCatalogueController::class, 'storeTag']);
                Route::post('tag/delete', [ProductCatalogueController::class, 'deleteProductTag']);
            });

            Route::prefix('pricing')->group(function () {

                Route::get('merchant-listing', [ProductPricingController::class, 'merchantListing']);;
                Route::post('store', [ProductPricingController::class, 'store']);;
                Route::post('update-discount-type', [ProductPricingController::class, 'updateDiscountType']);;
                Route::post('delete-merchant', [ProductPricingController::class, 'deleteMerchant']);;
                Route::post('delete-mailbox', [ProductPricingController::class, 'deleteMailbox']);;
                Route::post('update-merchant-sorting', [ProductPricingController::class, 'updateMerchantSorting']);;

            });



        });

        // Settings Routes
        Route::prefix('setting')->group(function () {
            Route::post('listing', [SettingController::class, 'listing']);
            Route::post('store', [SettingController::class, 'store']);
        });

        // Cart Routes
        Route::prefix('cart')->group(function () {
            Route::post('user/listing', [CartController::class, 'userListing']);
            Route::post('delete', [CartController::class, 'delete']);
        });


    });

});

// Shop Customer Apis
Route::middleware(['customer'])->prefix('customer')->group(function () {


        // Product Routes
        Route::prefix('product')->group(function () {
            Route::post('listing', [CustomerProductController::class, 'listing']);
            Route::post('filter-listing', [CustomerProductController::class, 'filterListing']);
            Route::post('all-filter', [CustomerProductController::class, 'allFilter']);
            Route::post('detail', [CustomerProductController::class, 'detail']);
        });

        // Menu Routes
        Route::prefix('menu')->group(function () {
            Route::post('listing', [CustomerMenuController::class, 'listing']);
            Route::post('detail', [CustomerMenuController::class, 'detail']);
        });

        // Setting Routes
        Route::prefix('setting')->group(function () {
            Route::post('listing', [CustomerSettingController::class, 'listing']);
        });

        // Cart Routes
        Route::prefix('cart')->group(function () {
            Route::post('listing', [CustomerCartController::class, 'listing']);
            Route::post('store', [CustomerCartController::class, 'store']);
            Route::post('update', [CustomerCartController::class, 'update']);
            Route::post('update-cart-item', [CustomerCartController::class, 'updateCartItem']);
            Route::post('delete', [CustomerCartController::class, 'delete']);
            Route::post('delete-user-cart', [CustomerCartController::class, 'deleteUserCart']);
            Route::post('checkout', [CustomerCartController::class, 'checkout']);
            Route::post('checkout-user', [CustomerCartController::class, 'checkoutUser']);
        });

        // Pdf Create and Download Route
        Route::post('download-pdf', [PdfController::class, 'downloadPdf']);
});

