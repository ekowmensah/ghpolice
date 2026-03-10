# Investigation Management System

## Overview
The Investigation Management System provides comprehensive tools for managing case investigations, including task assignments, checklists, milestones, and timeline tracking.

## Features

### 1. **Investigation Dashboard**
Access from any case view page via the "Investigation Management" button in the case information card.

**URL Pattern:** `/investigations/{case_id}`

**Features:**
- Real-time progress tracking
- Task management
- Investigation checklist
- Milestone tracking
- Investigation timeline

---

### 2. **Investigation Checklist**

**Purpose:** Track completion of standard investigation procedures

**Default Checklist Items:**
1. Initial complaint recorded (Initial Response)
2. Scene visited and documented (Initial Response)
3. Witnesses identified (Witnesses)
4. Statements recorded (Witnesses)
5. Evidence collected (Evidence)
6. Suspects identified (Suspects)
7. Forensic analysis requested (Evidence)
8. Investigation report prepared (Documentation)
9. Case file reviewed (Documentation)
10. Prosecution recommendation made (Case Closure)

**Categories:**
- Initial Response
- Witnesses
- Suspects
- Evidence
- Documentation
- Court Preparation
- Case Closure

**Features:**
- ✅ Real-time progress bar showing completion percentage
- ✅ Click checkbox to mark items complete
- ✅ Automatic timestamp and user tracking
- ✅ Category-based organization

**Database Table:** `case_investigation_checklist`

---

### 3. **Investigation Tasks**

**Purpose:** Assign and track specific investigation tasks to officers

**Task Types:**
- Interview
- Evidence Collection
- Document Review
- Follow-up
- Court Preparation
- Other

**Priority Levels:**
- 🔴 Urgent (Red)
- 🟠 High (Orange)
- 🔵 Medium (Blue)
- ⚪ Low (Gray)

**Task Status:**
- Pending
- In Progress
- Completed
- Cancelled

**Features:**
- ✅ Assign tasks to specific officers
- ✅ Set due dates
- ✅ Priority-based sorting
- ✅ Status tracking
- ✅ Task descriptions and notes
- ✅ Completion tracking

**How to Add a Task:**
1. Click "Add Task" button
2. Enter task title (required)
3. Add description (optional)
4. Select task type
5. Set priority level
6. Assign to an officer
7. Set due date
8. Click "Create Task"

**Database Table:** `case_investigation_tasks`

---

### 4. **Investigation Milestones**

**Purpose:** Track major investigation milestones and target dates

**Features:**
- ✅ Set milestone titles and descriptions
- ✅ Define target dates
- ✅ Track achievement status
- ✅ Visual indicators (achieved vs pending)

**Examples:**
- Arrest warrant obtained
- Suspect arrested
- Forensic report received
- Case file submitted to prosecutor
- Court date scheduled

**How to Add a Milestone:**
1. Click "Add" button in Milestones section
2. Enter milestone title (required)
3. Add description (optional)
4. Set target date
5. Click "Add Milestone"

**Database Table:** `case_milestones`

---

### 5. **Investigation Timeline**

**Purpose:** Chronological record of all investigation activities

**Activity Types:**
- Investigation
- Evidence
- Interview
- Arrest
- Court
- Administrative
- Other

**Features:**
- ✅ Automatic chronological ordering
- ✅ Activity descriptions
- ✅ User and timestamp tracking
- ✅ Activity type categorization

**Database Table:** `case_investigation_timeline`

---

## Technical Implementation

### Controllers
**File:** `app/Controllers/InvestigationController.php`

**Methods:**
- `show($caseId)` - Display investigation dashboard
- `addTask($caseId)` - Create new investigation task
- `updateTaskStatus($taskId)` - Update task status
- `updateChecklistItem($caseId)` - Toggle checklist item
- `addMilestone($caseId)` - Create new milestone

### Services
**File:** `app/Services/InvestigationService.php`

**Methods:**
- `getInvestigationDetails($caseId)` - Fetch all investigation data
- `getChecklist($caseId)` - Get checklist items
- `getTasks($caseId)` - Get investigation tasks
- `getTimeline($caseId)` - Get timeline entries
- `getMilestones($caseId)` - Get milestones
- `createTask($data)` - Create new task
- `updateTaskStatus($taskId, $status, $notes, $userId)` - Update task
- `updateChecklistItem($itemId, $completed, $userId)` - Update checklist
- `createMilestone($data)` - Create milestone
- `addTimelineEntry($caseId, $eventType, $description, $userId)` - Add timeline entry
- `initializeChecklist($caseId)` - Initialize default checklist

### Routes
**File:** `routes/web.php`

```php
$router->get('/investigations/{id}', 'InvestigationController@show');
$router->post('/investigations/{id}/tasks', 'InvestigationController@addTask');
$router->post('/investigations/{id}/checklist', 'InvestigationController@updateChecklistItem');
$router->post('/investigations/{id}/milestones', 'InvestigationController@addMilestone');
$router->post('/investigations/tasks/{id}/status', 'InvestigationController@updateTaskStatus');
```

### Views
**File:** `views/investigations/dashboard.php`

**Styling:** Ghana Police Service theme (black, white, blue)

---

## Database Schema

### case_investigation_checklist
```sql
- id (INT, PRIMARY KEY)
- case_id (INT, FK to cases)
- checklist_item (VARCHAR 200)
- item_description (VARCHAR 200)
- item_category (ENUM)
- item_order (INT)
- is_completed (BOOLEAN)
- completed_by (INT, FK to users)
- completed_date (DATETIME)
- completed_at (DATETIME)
- notes (TEXT)
- created_at (TIMESTAMP)
```

### case_investigation_tasks
```sql
- id (INT, PRIMARY KEY)
- case_id (INT, FK to cases)
- task_title (VARCHAR 200)
- task_description (TEXT)
- task_type (ENUM)
- assigned_to (INT, FK to officers)
- assigned_by (INT, FK to users)
- priority (ENUM: Low, Medium, High, Urgent)
- due_date (DATE)
- status (ENUM: Pending, In Progress, Completed, Cancelled)
- completion_date (DATETIME)
- completed_at (DATETIME)
- completed_by (INT)
- completion_notes (TEXT)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### case_milestones
```sql
- id (INT, PRIMARY KEY)
- case_id (INT, FK to cases)
- milestone_title (VARCHAR 200)
- milestone_description (TEXT)
- target_date (DATE)
- achieved_date (DATETIME)
- is_achieved (BOOLEAN)
- created_by (INT, FK to users)
- created_at (TIMESTAMP)
```

### case_investigation_timeline
```sql
- id (INT, PRIMARY KEY)
- case_id (INT, FK to cases)
- milestone_id (INT, FK to investigation_milestones)
- activity_type (ENUM)
- activity_title (VARCHAR 200)
- activity_description (TEXT)
- activity_date (DATETIME)
- event_type (VARCHAR 100)
- event_description (TEXT)
- event_date (DATETIME)
- completed_by (INT, FK to users)
- recorded_by (INT, FK to users)
- location (VARCHAR 255)
- outcome (TEXT)
- next_steps (TEXT)
- attachments (VARCHAR 255)
- is_milestone (BOOLEAN)
- created_at (TIMESTAMP)
```

---

## Usage Guide

### For Investigators

1. **Access Investigation Dashboard**
   - Navigate to case view page
   - Click "Investigation Management" button

2. **Track Progress**
   - Monitor checklist completion percentage
   - Review pending tasks
   - Check upcoming milestones

3. **Manage Tasks**
   - Create tasks for team members
   - Update task status as work progresses
   - Monitor overdue tasks

4. **Complete Checklist Items**
   - Click checkbox when procedure is completed
   - System automatically records who completed it and when

5. **Set Milestones**
   - Define key investigation milestones
   - Set target dates
   - Track achievement

### For Supervisors

1. **Monitor Team Performance**
   - Review task assignments
   - Check completion rates
   - Identify bottlenecks

2. **Assign Work**
   - Create and assign tasks to officers
   - Set priorities and deadlines
   - Monitor progress

3. **Track Investigation Progress**
   - Review checklist completion
   - Monitor milestone achievement
   - Review timeline for gaps

---

## Design Features

### Ghana Police Service Theme
- **Primary Colors:** Black (#1a1a1a, #2c3e50)
- **Accent Color:** Blue (#3498db)
- **Success Color:** Green (#27ae60)
- **Warning Color:** Orange (#f39c12)
- **Danger Color:** Red (#e74c3c)

### Responsive Design
- ✅ Mobile-friendly layout
- ✅ Touch-optimized controls
- ✅ Adaptive font sizes
- ✅ Collapsible sections

### User Experience
- ✅ Real-time updates
- ✅ Visual progress indicators
- ✅ Color-coded priorities
- ✅ Hover effects and animations
- ✅ Clear status indicators

---

## Best Practices

1. **Initialize Checklist Early**
   - Checklist is auto-initialized when case is created
   - Review and customize as needed

2. **Assign Tasks Promptly**
   - Create tasks as investigation needs arise
   - Set realistic due dates
   - Assign to appropriate officers

3. **Update Status Regularly**
   - Mark checklist items as completed
   - Update task status as work progresses
   - Keep timeline current

4. **Set Meaningful Milestones**
   - Focus on major investigation events
   - Set achievable target dates
   - Update when achieved

5. **Review Dashboard Daily**
   - Check for overdue tasks
   - Monitor progress
   - Identify blockers

---

## Troubleshooting

### Checklist Not Showing
- Ensure case exists in database
- Check if checklist was initialized
- Run `initializeChecklist($caseId)` if needed

### Tasks Not Saving
- Verify CSRF token is present
- Check officer assignment is valid
- Ensure all required fields are filled

### Timeline Empty
- Timeline entries are created automatically
- Manual entries can be added via service layer
- Check case_investigation_timeline table

---

## Future Enhancements

- [ ] Email notifications for task assignments
- [ ] Task deadline reminders
- [ ] Investigation report generation
- [ ] Evidence linking to tasks
- [ ] Witness/suspect linking to tasks
- [ ] Gantt chart view for tasks
- [ ] Investigation analytics dashboard
- [ ] Export investigation summary
- [ ] Mobile app integration
- [ ] Real-time collaboration features

---

## Support

For technical issues or feature requests, contact the system administrator or refer to the main GHPIMS documentation.

**Last Updated:** December 2024
**Version:** 1.0
