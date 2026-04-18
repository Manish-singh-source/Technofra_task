create controller file if not already exists in Controllers/Api/ folder.
Check existing functionality for similar in web first if available then use similar logic for api too.

    // Settings routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings')->middleware('permission:view_general_settings|view_company_information|view_email_settings');
        Route::put('/settings/general', [SettingController::class, 'updateGeneral'])->name('settings.update.general')->middleware('permission:view_general_settings');
        Route::put('/settings/company', [SettingController::class, 'updateCompany'])->name('settings.update.company')->middleware('permission:view_company_information');
        Route::put('/settings/email', [SettingController::class, 'updateEmail'])->name('settings.update.email')->middleware('permission:view_email_settings');
        Route::put('/settings/renewal', [SettingController::class, 'updateRenewal'])->name('settings.update.renewal')->middleware('permission:view_email_settings');
        Route::put('/settings/teams', [SettingController::class, 'updateTeams'])->name('settings.update.teams')->middleware('permission:view_general_settings');
        Route::put('/settings/departments', [SettingController::class, 'updateDepartments'])->name('settings.update.departments')->middleware('permission:view_general_settings');
        Route::post('/settings/test-email', [SettingController::class, 'sendTestEmail'])->name('settings.test.email')->middleware('permission:view_email_settings');
        Route::get('/settings/search-tags', [SettingController::class, 'searchTags'])->name('settings.search.tags');
    });


always return valid errors formats and proper http codes.
check authentication and authorization properly.

after making all apis create a readme file in docs/ folder where all these apis curl will be written



Check following routes and correct my methods for store and update as i have already updated my views. according to my views use same for store and then update methods correct edit blade page also. 
Route::controller(RoleController::class)->group(function () {
    Route::get('/roles', 'index')->name('roles.index')->middleware('permission:view_roles');
    Route::get('/create-role', 'create')->name('role.create')->middleware('permission:create_roles');
    Route::post('/store-role', 'store')->name('role.store')->middleware('permission:create_roles');
    Route::get('/edit-role/{id}', 'edit')->name('role.edit')->middleware('permission:edit_roles');
    Route::put('/edit-role/{id}', 'update')->name('role.update')->middleware('permission:edit_roles');
    Route::delete('/role/delete/{id}', 'destroy')->name('role.delete')->middleware('permission:delete_roles');
    Route::delete('/role/delete-selected', 'deleteSelected')->name('delete.selected.role')->middleware('permission:delete_roles');
    // permanent delete & restore 
});



check following tables structures and make migration files if not already available. and if already available then update those tables.


update users table: 

users: 

first_name
last_name
email
email_verified_at
phone
password
status - enum('active', 'inactive') - default('active')
remember_token
profile_image 
created_at
updated_at
deleted_at



user_address:

user_id 
address_line_1 
address_line_2 
city 
state
country 
pincode 
created_at
updated_at
deleted_at


staff_team 

user_id 
team_id 
created_at
updated_at
deleted_at


staff_department 

user_id 
department_id 
created_at
updated_at
deleted_at

client_business_details 

user_id
client_type 
industry (optional)
website (optional)





1. i have updated users table for so it can store client and staff data as well using role column
2. so check my leadcontroller for now. 
3. in this controlle check if there staff or client model is using or not 
4. if staff or client models are using then replace with user model cause now i have moved those data in my users table as you know. 
5. also check all relationships with those models and create in user model that relationships so my code will not break. 
6. check verify that all is working properly.