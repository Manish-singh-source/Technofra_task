create a single flowchart diagram using the following details then after that give me codex prompt that will edit my existing project which is half done with following details but not completely done. so give me prompt that will check and correct all these modules for me. also check following details and improve if something is missing from me.

**Module: Projects**

1. admin can create, update, delete and view projects.
2. also assigned team leader can view, update project details.
3. assigned members can view project details.
4. admin can assign/reassign/remove team leader for that project.
5. admin can assign/reassign/remove team member for that project.
6. team leader can view projects (only assigned)
7. team leader can add/remove team member to that project.
8. In project admin can add/update/delete project documentation (requirement gathering) file.
9. In projects admin/team leader can create/update/delete project milestones.

Module: Tasks

1. admin and team leader can create, update, delete and view tasks.
2. admin and team leader can assign/reassign/remove tasks to the team member.
3. team member can view task details (only assigned).
4. team member can update task details (specific like status, with resolve/unresolve solution, etc.)
5. team leader can also be assigned for some tasks.
6. team leader can also update task details (specific like status, with resolve/unresolve solution, etc.)
7. team member can comments for the task for chatting with team leader/admin.
8. after team member task completed it will go to review for team leader/admin.
9. if not approved then rework will be added.
10. if approved then task will be closed.

Project Tracking: based on start_date & end_date.

1. on create if start_date > today then status will be not_started
2. if start_date <= today then status will be in_progress
3. if end_date <= today then status will be overdue
4. complete status will be manual edit.
5. on_hole status will be manual edit.
6. cancelled status will be manual edit.

conditions:

1. if project is in overdue, completed, on_hold, cancelled status then only admin can edit that project details. other staff cannot edit project details.

Tasks Tracking: based on start_date & due_date.

1. on create if start_date > today then status will be not_started
2. if start_date <= today then status will be in_progress
3. if due_date <= today then status will be overdue
4. complete status will be manual edit.

