# Order Management System: Functional & Technical Specification

**Target stack:** PHP Laravel (existing CRM at mycrm.technofra.com)
**Integrates with:** the existing Vendor module and Client module
**Document purpose:** A complete description of what must be built. It contains no code. The implementing agent should decide the exact code structure, following the existing CRM's conventions (layout, authentication, naming, UI library).

---

## 1. Purpose and Scope

### 1.1 Goal
Add an **Order Management** module inside the existing CRM to track the full life of a trade order:

1. A **client** needs goods.
2. We buy those goods from a **vendor**.
3. The vendor delivers to **us**.
4. We deliver to the **client**.

The module records every date, quantity and status along this journey, and **reminds the responsible CRM user** at the right times so that no vendor delivery or client delivery is missed.

### 1.2 In scope
- Creating and managing orders linked to an existing Client and an existing Vendor
- Free-text products with quantity (no product catalog)
- Two independent tracking legs: Purchase (vendor to us) and Delivery (us to client)
- Status management with a history log
- Automatic reminders (in-app, plus optional email) sent only to the assigned CRM user
- Order list, detail, dashboard widgets and Orders tabs on Client and Vendor profiles
- Attachments, notes and an audit trail
- Role-based permissions

### 1.3 Out of scope (do NOT build)
- Product master, inventory or stock management
- Client or vendor login portals (vendors and clients never log in or receive reminders)
- Invoicing, payments, accounting
- Multi-step approval workflows
- Shipping carrier integrations or tracking APIs
- SMS or WhatsApp notifications (can be a future enhancement)

### 1.4 Key principle
**Reminders go to the CRM user who owns the order, never to the client or vendor.**

---

## 2. Glossary

| Term | Meaning |
|---|---|
| Order | One record linking one client, one vendor and a list of items |
| Purchase leg | Vendor to us: we place the order with the vendor and receive goods |
| Delivery leg | Us to client: we dispatch and deliver goods to the client |
| Owner / Assigned user | The CRM user responsible for the order, who receives all reminders |
| Vendor expected date | The date we expect to receive goods from the vendor |
| Client delivery date | The date we have committed to deliver to the client |
| Overdue | A target date has passed and that leg is not complete |

---

## 3. Users, Roles and Permissions

Use the CRM's existing user and role system. If it has none, use Laravel's standard authorization (policies and gates) with these permission levels.

| Capability | Admin | Manager | Staff |
|---|---|---|---|
| View all orders | Yes | Yes | Only orders assigned to or created by them (configurable) |
| Create order | Yes | Yes | Yes |
| Edit order | Yes | Yes | Only own orders |
| Change status | Yes | Yes | Only own orders |
| Reassign owner | Yes | Yes | No |
| Cancel order | Yes | Yes | Only own, with reason |
| Delete order | Admin only (prefer soft delete) | No | No |
| Configure reminder settings | Admin only | No | No |

Rules:
- Cancelling is preferred over deleting. Deletion must be soft delete only.
- Every create, edit, status change, reassignment and cancellation must be recorded in the history log with the acting user and timestamp.

---

## 4. Data Model

Use Laravel migrations and Eloquent models with proper relationships, foreign keys, indexes and soft deletes where stated. Reuse the existing clients and vendors tables via foreign keys and do not duplicate their data.

### 4.1 Table: orders

| Field | Type / Notes |
|---|---|
| id | Primary key |
| order_no | Unique, auto-generated, human-readable (format: ORD-YYYY-NNNN, with the sequence resetting yearly) |
| client_id | Foreign key to the existing clients table (required) |
| vendor_id | Foreign key to the existing vendors table (required) |
| assigned_user_id | Foreign key to users. This is the reminder recipient (required, defaults to the creator) |
| order_date | Date, defaults to today |
| vendor_expected_date | Date: when we expect to receive from the vendor (required) |
| vendor_received_date | Date, nullable, set when fully received |
| client_delivery_date | Date: promised delivery date to the client (required) |
| client_delivered_date | Date, nullable, set when delivered |
| purchase_status | Enum (see section 5) |
| delivery_status | Enum (see section 5) |
| overall_status | Enum: Open, Completed, Cancelled. Derived automatically, never set manually |
| is_on_hold | Boolean flag (see section 5.4) |
| priority | Enum: Normal, High |
| notes | Long text, nullable |
| cancel_reason | Text, required only when cancelled |
| cancelled_by and cancelled_at | Nullable |
| created_by, updated_by | Users |
| created_at, updated_at, deleted_at | Timestamps and soft delete |

Indexes are needed on: client_id, vendor_id, assigned_user_id, vendor_expected_date, client_delivery_date, purchase_status, delivery_status, overall_status.

### 4.2 Table: order_items
Products are free text. There is no product table.

| Field | Notes |
|---|---|
| id | Primary key |
| order_id | Foreign key to orders (cascade with the order) |
| product_name | Free text, required |
| quantity | Positive number, required (decimals allowed for kg, litres, etc.) |
| unit | Optional (pcs, kg, box, set, etc.) with a suggested list but allowing custom values |
| qty_received | Number, default 0, used for partial receipts from the vendor |
| qty_delivered | Number, default 0, used for partial deliveries to the client (optional but recommended) |
| purchase_rate | Optional decimal (only if profit tracking is wanted) |
| selling_rate | Optional decimal |
| remarks | Optional short text |
| sort_order | Integer for row ordering |

### 4.3 Table: order_status_history
An append-only log. Never edited or deleted.

| Field | Notes |
|---|---|
| id | Primary key |
| order_id | Foreign key |
| track | Purchase, Delivery or Order (for general events like reassignment and cancellation) |
| from_status, to_status | Text/enum values |
| changed_by | User |
| changed_at | Timestamp |
| remark | Optional note entered by the user |
| meta | Optional JSON for details such as old and new dates or old and new owner |

### 4.4 Table: order_attachments

| Field | Notes |
|---|---|
| id, order_id | Keys |
| type | Enum: PO to Vendor, Vendor Invoice, Delivery Challan, Proof of Delivery, Other |
| file_path, original_name, mime_type, size | File details (use Laravel's storage system, private disk) |
| uploaded_by, created_at | Audit |

### 4.5 Table: order_reminders (reminder log and de-duplication)

| Field | Notes |
|---|---|
| id | Primary key |
| order_id | Foreign key |
| user_id | The recipient (the assigned user at the time of sending) |
| reminder_type | Enum (see section 7.2) |
| reminder_for_date | The target date that triggered it |
| sent_on | Date of sending |
| channel | in_app or email |
| status | Sent or Failed |

A **unique rule** must exist on the combination of order, reminder type, recipient and the date sent, so the daily job can never send the same reminder twice in one day, even if it runs twice.

### 4.6 Table: notifications
Use Laravel's built-in database notifications table (or the CRM's existing notification system if there is one). Each notification stores the order id, type, message, and read/unread state.

### 4.7 Settings
A settings store (reuse the CRM's settings table if available) holding:
- Reminder lead days for vendor delivery (default 2)
- Reminder lead days for client delivery (default 2)
- Daily reminder run time (default 09:00, Asia/Kolkata)
- Whether to also send email (default on)
- Whether overdue reminders repeat daily (default on) and the maximum repeat days (default 7, then weekly)
- Whether staff can see only their own orders

---

## 5. Status Management

There are **two independent status tracks** plus a derived overall status.

### 5.1 Purchase status (vendor to us)

| Status | Meaning |
|---|---|
| Draft | Order created internally, not yet placed with the vendor |
| Ordered | Order placed with the vendor, waiting for goods |
| Partially Received | Some items or quantity received |
| Received | All goods received from the vendor |

### 5.2 Delivery status (us to client)

| Status | Meaning |
|---|---|
| Pending | Waiting for goods or not yet ready |
| Ready to Dispatch | Goods in hand and prepared |
| Dispatched | Goods sent to the client |
| Partially Delivered (optional) | Part of the goods delivered |
| Delivered | Client received everything |

### 5.3 Overall status (derived, read-only)
- **Open**: any leg is incomplete
- **Completed**: Purchase is Received and Delivery is Delivered
- **Cancelled**: the order was cancelled

### 5.4 Special states
- **On Hold**: a flag that pauses reminders and shows a clear badge. It requires a remark. Releasing the hold resumes reminders.
- **Cancelled**: a terminal state. It requires a reason. It stops all reminders and locks the order from further status changes (admin-only reopen is optional).
- **Overdue**: a calculated badge and not a stored status. It appears when the vendor expected date has passed and Purchase is not Received, or the client delivery date has passed and Delivery is not Delivered.

### 5.5 Allowed transitions and rules

Purchase: Draft to Ordered to Partially Received to Received. Going from Ordered directly to Received is allowed. Backward moves are allowed only for Admin and Manager, with a remark.

Delivery: Pending to Ready to Dispatch to Dispatched to Delivered (Partially Delivered is optional).

Business rules:
1. Delivery cannot move to Ready to Dispatch, Dispatched or Delivered unless Purchase is Partially Received or Received.
2. Purchase cannot be Received unless every item's received quantity has reached its ordered quantity. The system can offer a "Mark all as received" shortcut which fills the quantities.
3. Marking Purchase as Received sets the vendor received date to today (editable).
4. Marking Delivery as Delivered sets the client delivered date to today (editable).
5. If any item has received quantity above zero and below the ordered quantity, Purchase becomes Partially Received automatically.
6. Cancelled and On Hold orders cannot have status moved forward.
7. A cancelled or completed order cannot be edited except by Admin.
8. Every status change must create a history entry with an optional remark.

---

## 6. Screens and User Flows

Follow the existing CRM's layout, sidebar, styling and component library. Add an **Orders** menu item in the sidebar.

### 6.1 Orders List Page
- Table columns: Order No, Client, Vendor, Items summary (for example the first product plus "+2 more"), Vendor Expected Date, Client Delivery Date, Purchase Status, Delivery Status, Owner, Priority, Overdue badge
- Filters: Overall status, Purchase status, Delivery status, Client, Vendor, Owner, Priority, Overdue only, Date ranges (order date, vendor date, client date)
- Search: by order number, product name, client name, vendor name
- Quick filter tabs: All, My Orders, Due Soon, Overdue, Completed, Cancelled
- Sorting by dates, with the default being nearest upcoming date first
- Colored status badges and a red highlight for overdue rows
- Pagination
- Export to CSV or Excel (optional but recommended)
- "New Order" button

### 6.2 Create Order Page
Sections:

**A. Order Details**
- Client: searchable dropdown from the existing Client module (with an option to quickly add a client via the existing module if that is easy)
- Vendor: searchable dropdown from the existing Vendor module
- Assigned To: user dropdown, defaulting to the logged-in user (only Admin and Manager can change it)
- Order Date: defaults to today
- Vendor Expected Delivery Date: required
- Client Delivery Date: required
- Priority: Normal or High
- Notes

**B. Items (dynamic rows)**
- Add and remove rows
- Product Name (free text), Quantity, Unit, optional Purchase Rate, optional Selling Rate, Remarks
- At least one item is required
- Optional: suggest product names previously used in earlier orders (autocomplete from past entries, without creating a master list)

**C. Attachments** (optional at creation)

Validations:
- Client and Vendor must exist
- Quantity must be greater than zero
- The client delivery date must be on or after the vendor expected date (show a warning, and allow Admin override with a remark if the business sometimes delivers before receiving, which is unlikely)
- The order date cannot be later than the vendor expected date
- Prevent duplicate item submission via double-click

On save:
- Auto-generate the order number
- Set Purchase to Draft (or Ordered if the user ticks "Already placed with vendor") and Delivery to Pending
- Write the first history entry
- Show a success message and open the Order Detail page

### 6.3 Order Detail Page
Top section:
- Order number, overall status, overdue and on-hold badges, priority
- Client and vendor names (clickable to their profiles), owner

Two status "steppers" side by side:
- Purchase stepper showing the current step, with action buttons such as "Mark as Ordered", "Record Receipt" (opens a quantity-per-item dialog), "Mark All Received"
- Delivery stepper with action buttons such as "Ready to Dispatch", "Mark Dispatched", "Mark Delivered"
- Buttons appear only when allowed by the rules in section 5.5, and each opens a small confirmation dialog with a date and remark field

Other sections:
- Dates panel: order date, vendor expected vs actual received (with days early or late), client promised vs actual delivered (with days early or late)
- Items table with ordered, received and delivered quantities and progress indicators
- Attachments (upload, download, delete with permission)
- Notes
- History timeline (who, what, when, remark)
- Actions menu: Edit, Reassign Owner, Put On Hold or Resume, Cancel, Print or Export PDF (optional)

### 6.4 Edit Order
- The same form as create
- Changing a date or the owner must be logged in history
- Changing a date triggers reminder recalculation (see section 7.5)
- Locked fields once the order is Completed or Cancelled

### 6.5 Dashboard Widgets (on the existing CRM dashboard)
- Vendor deliveries due in the next 7 days
- Client deliveries due in the next 7 days
- Overdue vendor deliveries
- Overdue client deliveries
- My open orders (count and link)
- Orders by status summary
- Each widget shows the top 5 items with a "View all" link that opens the Orders list with the filter pre-applied

### 6.6 Client and Vendor Profile Integration
- Add an **Orders** tab on each client profile and vendor profile showing that party's orders, with counts of open, completed and overdue
- Include a "New Order" button prefilled with that client or vendor

### 6.7 Notification Center
- A bell icon in the CRM header with an unread count and a dropdown list
- Each notification links to the order
- Mark as read, and mark all as read
- A full notifications page with history

---

## 7. Reminder Engine

### 7.1 Principles
- Reminders are sent **only to the order's assigned user**.
- Clients and vendors never receive reminders.
- Reminders are evaluated by a **daily scheduled job** using Laravel's task scheduler (which itself requires a single cron entry on the server pointing to the Laravel scheduler).
- All logic uses the **Asia/Kolkata** timezone.
- Reminders must be idempotent (see the unique rule in section 4.5).

### 7.2 Reminder types

| Type | Trigger condition | Message intent |
|---|---|---|
| Vendor delivery upcoming | Vendor expected date minus lead days equals today, and Purchase is not Received | "Vendor delivery for ORD-xxx from [Vendor] is due in N days" |
| Vendor delivery today | Vendor expected date is today, and Purchase is not Received | "Vendor delivery for ORD-xxx is due today" |
| Vendor delivery overdue | Vendor expected date is before today, and Purchase is not Received | "Vendor delivery for ORD-xxx is overdue by N days" |
| Client delivery upcoming | Client delivery date minus lead days equals today, and Delivery is not Delivered | "Delivery to [Client] for ORD-xxx is due in N days" |
| Client delivery today | Client delivery date is today, and Delivery is not Delivered | "Deliver ORD-xxx to [Client] today" |
| Client delivery overdue | Client delivery date is before today, and Delivery is not Delivered | "Delivery to [Client] for ORD-xxx is overdue by N days" |
| Goods received but not dispatched (optional) | Purchase is Received, Delivery is still Pending or Ready, and the client date is within the lead days | "Goods for ORD-xxx are received. Please dispatch to [Client]" |

### 7.3 Suppression rules
Do not send reminders when:
- The order is Cancelled or On Hold
- That leg is already complete (vendor reminders stop once Received, client reminders stop once Delivered)
- The order is soft-deleted
- The same reminder was already sent today (de-duplication)

### 7.4 Overdue repeat behaviour
- Overdue reminders repeat **daily** up to the configured maximum (default 7 days), then **weekly** until the leg is completed or cancelled.
- High priority orders may repeat daily with no cap (optional).

### 7.5 Recalculation on change
- Reminders are computed on the fly each day from the current dates and statuses, so changing a date automatically takes effect and no stale reminders remain.
- If the owner is reassigned, future reminders go to the new owner. Already-sent notifications stay with the old owner.
- If a date is moved, the history log records the change.

### 7.6 Channels
- **In-app notification** (always on)
- **Email** to the assigned user's registered email (configurable). Send as queued jobs so a mail failure does not stop the scheduler. Log failures in the reminder log.
- Optional: a **daily digest email** at the configured time that lists all the user's items for the day, instead of one email per order. This is recommended when a user has many orders.

### 7.7 Scheduler and queue requirements
- Define the daily reminder command in the Laravel scheduler, prevent overlapping runs, and run it on one server only.
- Use queued notifications with the database or Redis queue driver, and note that a queue worker must be running (supervisor or equivalent).
- The command should log how many reminders it sent, skipped and failed.
- The command should be runnable manually from the command line for testing, with an option to simulate a specific date.

---

## 8. Business Rules Summary

1. One order links exactly one client and one vendor.
2. Products are free text with a quantity, and are not linked to any master.
3. The vendor expected date and the client delivery date are both required.
4. Two status tracks (Purchase and Delivery) move independently, subject to the dependency rule in section 5.5.
5. The overall status is always derived.
6. Reminders go only to the assigned user and stop when the relevant leg completes or the order is cancelled or on hold.
7. Every change is recorded in the history log.
8. Delete is soft only, and cancel is the normal way to end an order.
9. Staff see only their own orders unless the setting says otherwise.

---

## 9. Non-Functional Requirements

- **Security:** CSRF protection, authorization policies on every action, validation of every input, private file storage for attachments with an allowed file types list (PDF, images, Office documents) and a size limit (for example 10 MB per file).
- **Performance:** Indexed filters, pagination, and eager loading of relationships on lists. The reminder job should use chunked queries.
- **Auditability:** History and reminder logs are never deleted.
- **Timezone and dates:** Store dates in a consistent format, and display them in the CRM's date format using Asia/Kolkata.
- **Consistency with the CRM:** Reuse existing layout, menus, form components, validation message style and permission system.
- **Data integrity:** Foreign keys, transactions when saving an order with its items and history, and protection against deleting a client or vendor that has open orders (block it, or show a clear message).
- **Responsive UI:** The key screens must work on mobile, because users may update statuses on the go.

---

## 10. Reports (recommended, lightweight)

- Orders by status and by date range
- Vendor performance: average delay between expected and actual received date, and percentage delivered on time
- Client delivery performance: on-time delivery rate
- Open and overdue orders by owner
- Export these as CSV or Excel

---

## 11. Implementation Plan (suggested build order)

**Phase 1: Foundation**
1. Database migrations and models for all tables in section 4
2. Relationships to existing clients, vendors and users
3. Order number generator
4. Permissions and policies

**Phase 2: Core order management**
5. Orders list with filters and search
6. Create and edit order with dynamic items
7. Order detail page with history timeline
8. Status actions with all business rules and the history log
9. Partial receipt handling
10. Attachments

**Phase 3: Reminders**
11. Notification classes (in-app and email)
12. Daily scheduled reminder command with de-duplication and suppression rules
13. Notification bell and notifications page
14. Settings page for reminder configuration

**Phase 4: Integration and polish**
15. Dashboard widgets
16. Orders tab on client and vendor profiles
17. Overdue badges and highlighting
18. Reports and exports
19. Sidebar menu entry

**Phase 5: Testing**
20. Automated tests covering the scenarios in section 12

---

## 12. Acceptance Criteria and Test Scenarios

1. A user can create an order by picking an existing client and vendor, entering free-text items with quantities, and entering both dates. An order number is generated.
2. Saving with no items, a zero quantity or a missing date is rejected with clear messages.
3. A client delivery date earlier than the vendor expected date is warned about or blocked per section 6.2.
4. Delivery status cannot advance while Purchase is still Draft or Ordered.
5. Recording a partial receipt sets Purchase to Partially Received, and recording the full quantity sets it to Received with the received date stored.
6. Marking Delivery as Delivered while Purchase is Received completes the order and sets the overall status to Completed.
7. Two days before the vendor expected date, the assigned user (and nobody else) receives an in-app notification (and an email if enabled).
8. On the vendor expected date, if goods are not received, a "due today" reminder is sent. The next day an "overdue" reminder is sent.
9. Once Purchase is Received, vendor reminders stop, and client reminders begin according to the client date.
10. Running the reminder command twice on the same day does not create duplicate reminders.
11. Cancelled and On Hold orders produce no reminders. Resuming a held order restores them.
12. Reassigning the owner sends all future reminders to the new owner.
13. Changing a delivery date changes when reminders fire, with no manual cleanup, and the change appears in history.
14. A staff user cannot see, edit or change orders owned by others (when the restriction is on), and cannot delete any order.
15. Every status change, date change, reassignment, hold and cancellation appears in the history timeline with the user and time.
16. The client and vendor profiles show their orders in an Orders tab.
17. The dashboard widgets show correct counts and links.
18. Attachments upload, download and delete according to permissions, and invalid file types are rejected.
19. Deleting a client or vendor that has open orders is blocked with a clear message.

---

## 13. Future Enhancements (not part of this build)

- WhatsApp or SMS reminders
- Invoice and payment tracking
- Vendor and client portals
- Product master with stock levels
- Recurring orders
- Mobile app push notifications
- Calendar view of all deliveries and a calendar sync (Google Calendar)

---

## 14. Notes for the Implementing Agent

- Inspect the existing CRM first: its authentication, user and role system, layout, Blade or frontend framework, table component, notification setup and settings storage. Match all of these rather than introducing new patterns.
- Reuse the existing clients and vendors tables and models, and do not alter their structure unless absolutely necessary.
- Keep the module self-contained (its own models, controllers, policies, notifications, console command, views and routes grouped under an "Orders" namespace or folder).
- Where this document leaves a small detail open (exact labels, colors, column order), follow the closest existing CRM convention.
- Where a rule here conflicts with the existing CRM's conventions, prefer this document's business rules and the CRM's visual conventions.
- After building, provide a short setup note listing the required server steps: run the migrations, add the scheduler cron entry, start the queue worker, and configure the mail settings.
