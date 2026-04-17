create controller file if not already exists in Controllers/Api/ folder.
Check existing functionality for similar in web first if available then use similar logic for api too.
    // Service CRUD routes refer this
    Route::middleware(['auth'])->group(function () {
        Route::get('/client-issue', [ClientIssueController::class, 'index'])->name('client-issue');
        Route::get('/client-issue/create', [ClientIssueController::class, 'create'])->name('client-issue.create');
        Route::post('/client-issue', [ClientIssueController::class, 'store'])->name('client-issue.store');
        Route::get('/client-issue/{id}', [ClientIssueController::class, 'show'])->name('client-issue.show');
        Route::post('/client-issue/{clientIssue}/assign', [ClientIssueController::class, 'assignTeam'])->name('client-issue.assign');
        Route::patch('/client-issue/{id}/status', [ClientIssueController::class, 'updateStatus'])->name('client-issue.update-status');
        Route::delete('/client-issue/{id}', [ClientIssueController::class, 'destroy'])->name('client-issue.destroy');
        Route::delete('/client-issue/delete-selected', [ClientIssueController::class, 'deleteSelected'])->name('delete.selected.client-issue');

        // Client issue task routes
        Route::post('/client-issue/{clientIssue}/task', [ClientIssueController::class, 'taskStore'])->name('client-issue.task.store');
        Route::get('/client-issue/{clientIssue}/task/{task}', [ClientIssueController::class, 'taskShow'])->name('client-issue.task.show');
        Route::put('/client-issue/{clientIssue}/task/{task}', [ClientIssueController::class, 'taskUpdate'])->name('client-issue.task.update');
        Route::patch('/client-issue/{clientIssue}/task/{task}/status', [ClientIssueController::class, 'taskUpdateStatus'])->name('client-issue.task.update-status');
        Route::delete('/client-issue/{clientIssue}/task/{task}', [ClientIssueController::class, 'taskDestroy'])->name('client-issue.task.destroy');
    });

always return valid errors formats and proper http codes.
check authentication and authorization properly.

after making all apis create a readme file in docs/ folder where all these apis curl will be written
