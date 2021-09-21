<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', 'Api\AuthController@login');
Route::get('/login/list/entities', 'Api\AuthController@entities');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', 'Api\AuthController@logout');
    Route::get('/user', 'Api\AuthController@getUser');

    //********************************************ENTIDAD**************************************************
    Route::get('entities', 'Api\EntityController@index')->name('api.entity.index');
    Route::get('list/entities', 'Api\EntityController@listEntities')->name('api.entity.listEntities');
    Route::get('entities/filter/{id}', 'Api\EntityController@filterEntities')->name('api.entity.filterEntities');
    Route::post('entities', 'Api\EntityController@store')->name('api.entity.store');
    Route::put('entities/{entity}', 'Api\EntityController@update')->name('api.entity.update');
    Route::delete('entities/{id}', 'Api\EntityController@destroy')->name('api.entity.destroy');

    //********************************************AREA**************************************************
    Route::get('areas', 'Api\AreaController@index')->name('api.area.index');
    Route::get('list/areas', 'Api\AreaController@listAreas')->name('api.area.listAreas');
    Route::get('areas/filter/{id}', 'Api\AreaController@filterAreas')->name('api.area.filterAreas');
    Route::post('areas', 'Api\AreaController@store')->name('api.area.store');
    Route::put('areas/{id}', 'Api\AreaController@update')->name('api.area.update');
    Route::delete('areas/{id}', 'Api\AreaController@destroy')->name('api.area.destroy');

    //********************************************PERSONA**************************************************
    Route::get('persons', 'Api\PersonController@index')->name('api.person.index');
    Route::get('list/persons', 'Api\PersonController@listPersons')->name('api.person.listPersons');
    Route::get('persons/filter/{id}', 'Api\PersonController@filterPersons')->name('api.person.filterPersons');
    Route::post('persons', 'Api\PersonController@store')->name('api.person.store');
    Route::put('persons/{id}', 'Api\PersonController@update')->name('api.person.update');
    Route::delete('persons/{id}', 'Api\PersonController@destroy')->name('api.person.destroy');

    //********************************************ASIGNACIONES**************************************************
    Route::get('assignments', 'Api\AssignmentController@index')->name('api.assignment.index');
    Route::get('list/assignments', 'Api\AssignmentController@listAssignments')->name('api.assignment.listAssignments');
    Route::get('assignments/filter/person', 'Api\AssignmentController@filterPersons')->name('api.assignment.filterPerson');
    Route::get('assignments/filter/legal', 'Api\AssignmentController@filterPersonLegal')->name('api.assignment.filterPersonLegal');
    Route::get('assignments/areas/{id}', 'Api\AssignmentController@listAreas')->name('api.assignment.listAreas');
    Route::get('assignments/filter/{id}', 'Api\AssignmentController@filterAssignments')->name('api.assignment.filterAssignments');
    Route::post('assignments', 'Api\AssignmentController@store')->name('api.assignment.store');
    Route::put('assignments/{id}', 'Api\AssignmentController@update')->name('api.assignment.update');
    Route::delete('assignments/{id}', 'Api\AssignmentController@destroy')->name('api.assignment.destroy');

    //********************************************PRODUCTOS**************************************************
    Route::get('products', 'Api\ProductController@index')->name('api.product.index');
    Route::get('list/products', 'Api\ProductController@listProducts')->name('api.product.listProducts');
    Route::post('products', 'Api\ProductController@store')->name('api.product.store');
    Route::put('products/{id}', 'Api\ProductController@update')->name('api.product.update');
    Route::delete('products/{id}', 'Api\ProductController@destroy')->name('api.product.destroy');
    Route::post('products/laboratory', 'Api\ProductController@storeLaboratory')->name('api.product.storeLaboratory');
    Route::post('products/generic', 'Api\ProductController@storeGeneric')->name('api.product.storeGeneric');
    Route::post('products/category', 'Api\ProductController@storeCategory')->name('api.product.storeCategory');
    Route::post('products/presentation', 'Api\ProductController@storePresentation')->name('api.product.storePresentation');
    Route::post('products/location', 'Api\ProductController@storeLocation')->name('api.product.storeLocation');
    Route::get('products/list/laboratories', 'Api\ProductController@listLaboratories')->name('api.product.listLaboratories');
    Route::get('products/list/generics', 'Api\ProductController@listGenerics')->name('api.product.listGenerics');
    Route::get('products/list/categories', 'Api\ProductController@listCategories')->name('api.product.listCategories');
    Route::get('products/list/presentations', 'Api\ProductController@listPresentations')->name('api.product.listPresentations');
    Route::get('products/list/locations', 'Api\ProductController@listLocations')->name('api.product.listLocations');
    Route::get('products/name/{search}', 'Api\ProductController@getProducts')->name('api.product.getProducts');
    Route::get('products/kardex', 'Api\ProductController@getKardex')->name('api.product.getKardex');
    Route::post('products/kardex/fetch', 'Api\ProductController@fetchKardex')->name('api.product.fetchKardex');
    Route::get('products/kardex/filter', 'Api\ProductController@filterKardex')->name('api.product.filterKardex');

    //*********************************************COMPRAS**************************************************************
    Route::get('invoices/products/{search}', 'Api\InvoicePurchaseController@listProducts')->name('api.invoices.listProducts')->where('search', '.*');;
    Route::get('invoices/type', 'Api\InvoicePurchaseController@listTypeInvoicePurchases')->name('api.invoices.listTypeInvoicePurchases');
    Route::get('invoices', 'Api\InvoicePurchaseController@index')->name('api.invoices.index');
    Route::post('invoices', 'Api\InvoicePurchaseController@store')->name('api.invoices.store');
    Route::put('invoices/{id}', 'Api\InvoicePurchaseController@update')->name('api.invoices.update');
    Route::put('item/invoices/{id}', 'Api\InvoicePurchaseController@itemUpdate')->name('api.invoices.itemUpdate');
    Route::post('invoices/destroy', 'Api\InvoicePurchaseController@destroy')->name('api.invoices.destroy');
    Route::post('invoices/purchase', 'Api\InvoicePurchaseController@destroyInvoicePurchase')->name('api.ticket.destroyInvoicePurchase');

    //*********************************************VENTAS**************************************************************
    Route::get('ticket/products/{search}', 'Api\TicketInvoiceController@listProducts')->name('api.ticket.listProducts')->where('search', '.*');
    Route::get('ticket/barcode/{search}', 'Api\TicketInvoiceController@listProductBarcode')->name('api.ticket.listProductBarcode');
    Route::get('ticket/invoices', 'Api\TicketInvoiceController@listTypeTicketInvoices')->name('api.ticket.listTypeTicketInvoices');
    Route::get('ticket/buys', 'Api\TicketInvoiceController@listTypeBuys')->name('api.ticket.listTypeBuy');
    Route::get('ticket', 'Api\TicketInvoiceController@index')->name('api.ticket.index');
    Route::post('ticket', 'Api\TicketInvoiceController@store')->name('api.ticket.store');
    Route::put('ticket/{id}', 'Api\TicketInvoiceController@update')->name('api.ticket.update');
    Route::delete('ticket/{id}', 'Api\TicketInvoiceController@destroy')->name('api.ticket.destroy');
    Route::get('ticket/box', 'Api\TicketInvoiceController@getBox')->name('api.ticket.getBox');
    Route::post('ticket/box', 'Api\TicketInvoiceController@boxStore')->name('api.ticket.boxStore');
    Route::get('ticket/sale/invoices', 'Api\TicketInvoiceController@getInvoices')->name('api.ticket.getInvoices');
    Route::get('ticket/sale/purchases', 'Api\TicketInvoiceController@getPurchases')->name('api.ticket.getPurchases');
    Route::post('ticket/box/close', 'Api\TicketInvoiceController@closeBox')->name('api.ticket.closeBox');
    Route::get('ticket/box/total/{id}', 'Api\TicketInvoiceController@boxTotal')->name('api.ticket.boxTotal');
    Route::delete('ticket/invoice/{id}', 'Api\TicketInvoiceController@destroyTicketInvoice')->name('api.ticket.destroyTicketInvoice');
    Route::post('ticket/invoice/print', 'Api\TicketInvoiceController@printTicketInvoice')->name('api.ticket.printTicketInvoice');
    Route::post('ticket/amount/voucher', 'Api\TicketInvoiceController@getAmountVoucher')->name('api.ticket.getAmountVoucher');
    Route::post('ticket/amount/payment', 'Api\TicketInvoiceController@getAmountPayment')->name('api.ticket.getAmountPayment');
    Route::get('ticket/box/state', 'Api\TicketInvoiceController@getUserState')->name('api.ticket.getUserState');
    Route::put('cash', 'Api\TicketInvoiceController@boxUpdate')->name('api.ticket.boxUpdate');
    Route::post('reverse', 'Api\TicketInvoiceController@reverse')->name('api.ticket.reverse');

    //************************************ REPORTES ***************************************************************************************
    Route::get('report/box/{id}', 'Api\ReportController@getReportBox')->name('api.box.getReportBox');
    Route::get('report/facturaCompra/{id}', 'Api\ReportController@getReportFacturaCompra')->name('api.facturaCompra.getReportFacturaCompra');
    Route::get('report/productStock/{id}', 'Api\ReportController@getReportProductStock')->name('api.productStock.getReportProductStock');
    Route::get('report/comprobanteVenta/{id}', 'Api\ReportController@getReportComprobanteVenta')->name('api.comprobanteVenta.getReportComprobanteVenta');
});

