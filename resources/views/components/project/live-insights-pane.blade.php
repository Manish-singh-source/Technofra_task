<div class="tab-pane fade" id="live-insights" role="tabpanel" aria-labelledby="live-insights-tab">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card radius-10 h-100">
                <div class="card-body">
                    <p class="mb-1 text-muted">Total Tasks</p>
                    <h4 class="mb-0" id="insight-total-tasks">-</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card radius-10 h-100">
                <div class="card-body">
                    <p class="mb-1 text-muted">Overdue Tasks</p>
                    <h4 class="mb-0 text-danger" id="insight-overdue-tasks">-</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card radius-10 h-100">
                <div class="card-body">
                    <p class="mb-1 text-muted">Milestone Completion</p>
                    <h4 class="mb-0 text-success" id="insight-milestone-completion">-</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-5">
            <div class="card radius-10 h-100">
                <div class="card-header"><h5 class="mb-0">Task Status Chart (Live)</h5></div>
                <div class="card-body">
                    <div id="liveInsightStatusChart" style="min-height: 280px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card radius-10 h-100">
                <div class="card-header"><h5 class="mb-0">Activity Feed (Live)</h5></div>
                <div class="card-body" id="liveActivityFeed" style="max-height: 320px; overflow: auto;">
                    <p class="text-muted mb-0">Loading activity feed...</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="card radius-10">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mini Kanban Snapshot</h5>
                    <a href="{{ route('task.kanban', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-primary radius-30">
                        Open Full Kanban
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3" id="liveMiniKanban">
                        <div class="col-12"><p class="text-muted mb-0">Loading Kanban board...</p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

