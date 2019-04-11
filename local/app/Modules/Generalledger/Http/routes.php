<?php

/*
|--------------------------------------------------------------------------
| Module Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for the module.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::any('cash-flow/654321', 'Report\CashFlowWebController@index');

Route::group(['prefix' => 'general-ledger'], function() {
    Route::group(['prefix' => 'master'], function() {
        Route::group(['prefix' => 'master-coa'], function() {
            Route::any('/', 'Master\MasterCoaController@index');
            Route::get('add', 'Master\MasterCoaController@add');
            Route::get('edit/{id}', 'Master\MasterCoaController@edit');
            Route::post('save', 'Master\MasterCoaController@save');
            Route::post('delete', 'Master\MasterCoaController@delete');
            Route::get('print-pdf', 'Master\MasterCoaController@printPdf');
            Route::get('print-excel', 'Master\MasterCoaController@printExcel');
        });
        Route::group(['prefix' => 'master-coa-combination'], function() {
            Route::any('/', 'Master\MasterCoaCombinationController@index');
            Route::get('add', 'Master\MasterCoaCombinationController@add');
            Route::get('edit/{id}', 'Master\MasterCoaCombinationController@edit');
            Route::post('save', 'Master\MasterCoaCombinationController@save');
            Route::post('delete', 'Master\MasterCoaCombinationController@delete');
        });
        Route::group(['prefix' => 'master-bank'], function() {
            Route::any('/', 'Master\MasterBankController@index');
            Route::get('add', 'Master\MasterBankController@add');
            Route::get('edit/{id}', 'Master\MasterBankController@edit');
            Route::post('save', 'Master\MasterBankController@save');
            Route::post('delete', 'Master\MasterBankController@delete');
            Route::get('print-excel', 'Master\MasterBankController@printExcel');
        });
        Route::group(['prefix' => 'setting-journal'], function() {
            Route::any('/', 'Master\SettingJournalController@index');
            Route::get('edit/{id}', 'Master\SettingJournalController@edit');
            Route::post('save', 'Master\SettingJournalController@save');
            Route::get('get-json-coa', 'Master\SettingJournalController@getJsonCoa');
        });
    });

    Route::group(['prefix' => 'transaction'], function() {
        Route::group(['prefix' => 'journal-entry'], function() {
            Route::any('/', 'Transaction\JournalEntryController@index');
            Route::get('add', 'Transaction\JournalEntryController@add');
            Route::get('edit/{id}', 'Transaction\JournalEntryController@edit');
            Route::post('save', 'Transaction\JournalEntryController@save');
            Route::any('post-all', 'Transaction\JournalEntryController@postAll');
            Route::post('save-post-all', 'Transaction\JournalEntryController@savePostAll');
            Route::get('get-json-account-combination', 'Transaction\JournalEntryController@getJsonAccountCombination');
            Route::get('print-excel', 'Transaction\JournalEntryController@printExcel');
        });
    });

    Route::group(['prefix' => 'report'], function() {
        Route::group(['prefix' => 'daily-cash'], function() {
            Route::any('/', 'Report\DailyCashController@index');
            Route::get('print-pdf', 'Report\DailyCashController@printPdf');
            Route::get('print-excel', 'Report\DailyCashController@printExcel');
        });
        Route::group(['prefix' => 'general-journal'], function() {
            Route::any('/', 'Report\GeneralJournalController@index');
            Route::get('print-pdf', 'Report\GeneralJournalController@printPdf');
            Route::get('print-excel', 'Report\GeneralJournalController@printExcel');
        });
        Route::group(['prefix' => 'account-post'], function() {
            Route::any('/', 'Report\AccountPostController@index');
            Route::get('print-pdf', 'Report\AccountPostController@printPdf');
            Route::get('print-excel', 'Report\AccountPostController@printExcel');
        });
        Route::group(['prefix' => 'income'], function() {
            Route::any('/', 'Report\IncomeController@index');
            Route::get('print-pdf', 'Report\IncomeController@printPdf');
            Route::get('print-excel', 'Report\IncomeController@printExcel');
        });
        Route::group(['prefix' => 'trial-balance'], function() {
            Route::any('/', 'Report\TrialBalanceController@index');
            Route::get('print-pdf', 'Report\TrialBalanceController@printPdf');
            Route::get('print-excel', 'Report\TrialBalanceController@printExcel');
        });
    });
});
