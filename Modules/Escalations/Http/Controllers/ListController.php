<?php

namespace Modules\Escalations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SubDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Escalations\Entities\Category;
use Modules\Escalations\Entities\EscalationList;
use Modules\Escalations\Entities\Subcategory;
use Modules\Escalations\Services\NotificationService;

class ListController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        // $this->middleware('permission:view-list-escalation')->only(['index']);
        $this->middleware('permission:view-create-escalation')->only(['create', 'store']);
        $this->middleware('permission:view-view-escalation')->only(['show']);
        $this->middleware('permission:view-edit-escalation')->only(['edit', 'update']);
        $this->middleware('permission:view-delete-escalation')->only(['destroy']);
    }

    // AJAX endpoint to get subcategories for a category
    public function getSubcategories($category_id)
    {
        $subcategories = \Modules\Escalations\Entities\Subcategory::where('category_id', $category_id)->get();

        return response()->json($subcategories);
    }

    public function index()
    {
        $lists = EscalationList::with(['category', 'subcategory', 'sub_department'])
            ->orderBy('id', 'desc')
            ->get();

        return view('escalations::list.index', compact('lists'));
    }

    public function create()
    {
        $categories = Category::all();
        $subcategories = Subcategory::all();
        $departments = \App\Models\Department::where('department_status', 1)->get();
        $subDepartments = SubDepartment::where('sub_department_status', 1)->get();
        $regions = \App\Models\Region::active()->orderBy('name')->get();

        return view('escalations::list.create', [
            'categories' => $categories,
            'subcategories' => $subcategories,
            'departments' => $departments,
            'subDepartments' => $subDepartments,
            'regions' => $regions,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_number' => 'required',
            'department_id' => 'required',
            'category_id' => 'required',
            'sub_category_id' => 'required',
            'description' => 'required',
            'priority' => 'required',
            'status' => 'required',
        ]);

        // Previously: `EscalationList::orderByDesc('id')->first()` then +1, with no
        // lock or transaction — the same unsafe-numbering pattern the spec warns
        // against for appointments, here a third time. Two concurrent submissions
        // could compute the same ESC-N. Also previously unwrapped: a failure after
        // creating $list but before $escalation/$history would leave a ticket with
        // no escalation record — this is now one atomic transaction.
        //
        // SequenceNumberService::next() is called BEFORE the transaction, not
        // inside it: next() commits its own increment immediately, so a later
        // failure inside the transaction can't roll back the increment too and
        // silently hand this same ticket number out again to a different
        // escalation created right after.
        $ticketNumber = \App\Services\SequenceNumberService::next('escalation:ESC');
        $ticket_id = 'ESC-'.$ticketNumber;

        [$list, $escalation, $queueEntry] = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $ticket_id) {
            $list = EscalationList::create([
                'account_number' => $request->account_number,
                'ticket_id' => $ticket_id,
                'department_id' => $request->department_id,
                'sub_department_id' => $request->sub_department_id,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'description' => $request->description,
                'priority' => $request->priority,
                'status' => $request->status,
                'created_by' => Auth::id(),
            ]);

            $escalation = \Modules\Escalations\App\Models\Escalation::create([
                'escalation_id' => $list->id, // Link to the EscalationList
                'ticket_id' => $ticket_id,
                'account_number' => $request->account_number,
                'department_id' => $request->department_id,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'description' => $request->description,
                'status' => 'Escalated-Open',
                'priority' => $request->priority,
                'sub_department_id' => $request->sub_department_id,
                'region_id' => $request->region_id,
                'assigned_to' => null,
                'created_by' => Auth::id(),
            ]);

            \Modules\Escalations\App\Models\EscalationHistory::create([
                'escalation_id' => $escalation->id, // Link to the Escalation
                'ticket_id' => $ticket_id, // Store the ticket reference
                'status' => 'Escalated-Open',
                'department_id' => $request->department_id,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'description' => $request->description,
                'account_number' => $request->account_number,
                'priority' => $request->priority,
                'sub_department_id' => $request->sub_department_id,
                'action_by' => Auth::id(),
            ]);

            // FIFO: every new escalation enters the queue unassigned. A dispatcher
            // (or a scheduled `fifo:dispatch` run) hands it out via
            // FifoQueueService::assignNext()/bulkAssignToUser() — oldest-eligible
            // first within its priority band, region-aware.
            $queueEntry = (new \App\Services\Fifo\FifoQueueService)->enqueue(
                'escalation',
                'escalation',
                $escalation->id,
                $request->priority,
                $request->region_id
            );

            return [$list, $escalation, $queueEntry];
        });

        // Create notification for the new escalation
        // This will generate both the database notification and the toast notification
        $this->notificationService->notifyCreation($escalation);

        return redirect()
            ->route('list.index')
            ->with('success', 'Ticket created!');
    }

    public function show($id)
    {
        $list = EscalationList::with(['category', 'subcategory', 'sub_department'])->findOrFail($id);

        return view('escalations::list.show', compact('list'));
    }

    public function edit($id)
    {
        $list = EscalationList::with(['category', 'subcategory', 'department', 'sub_department'])->findOrFail($id);
        $categories = Category::all();
        $subcategories = Subcategory::all();
        $departments = \App\Models\Department::where('department_status', 1)->get();
        $subDepartments = \App\Models\SubDepartment::where('sub_department_status', 1)->get();

        return view('escalations::list.edit', [
            'list' => $list,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'departments' => $departments,
            'subDepartments' => $subDepartments,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'account_number' => 'required',
            'department_id' => 'required',
            'category_id' => 'required',
            'sub_category_id' => 'required',
            'description' => 'required',
            'priority' => 'required',
            'status' => 'required',
        ]);

        $list = EscalationList::with(['category', 'subcategory', 'sub_department'])->findOrFail($id);

        // Store old values for notification
        $changes = [
            'account_number' => [$list->account_number, $request->account_number],
            'department_id' => [$list->department_id, $request->department_id],
            'sub_department_id' => [$list->sub_department_id, $request->sub_department_id],
            'category_id' => [$list->category_id, $request->category_id],
            'sub_category_id' => [$list->sub_category_id, $request->sub_category_id],
            'description' => [$list->description, $request->description],
            'priority' => [$list->priority, $request->priority],
            'status' => [$list->status, $request->status],
        ];

        // Update the escalation list
        $list->update([
            'account_number' => $request->account_number,
            'department_id' => $request->department_id,
            'sub_department_id' => $request->sub_department_id,
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => $request->status,
            'edited_by' => Auth::id(),
        ]);

        // Find the related escalation
        $escalation = \Modules\Escalations\App\Models\Escalation::where('escalation_id', $list->id)->first();

        if ($escalation) {
            // Update escalation with edited_by
            $escalation->edited_by = Auth::id();
            $escalation->save();

            // Create a new history record for this update
            \Modules\Escalations\App\Models\EscalationHistory::create([
                'escalation_id' => $escalation->id,
                'ticket_id' => $escalation->ticket_id,
                'status' => $request->status,
                'department_id' => $request->department_id,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'description' => $request->description,
                'account_number' => $request->account_number,
                'priority' => $request->priority,
                'sub_department_id' => $request->sub_department_id,
                'action_by' => Auth::id(),
            ]);

            // Create direct toast notification for update
            $editor = \App\Models\User::find(Auth::id());
            $editorName = $editor ? $editor->name : 'Unknown user';
            $toastMessage = "{$editorName} just updated ticket {$escalation->ticket_id}";
            $this->notificationService->setToastNotification('info', $toastMessage);

            // Create notification for the editor themselves
            $this->notificationService->createNotification(
                Auth::id(),
                $escalation->id,
                'update',
                "You updated ticket {$escalation->ticket_id}."
            );

            // Notify the creator if different from editor
            if ($escalation->created_by && $escalation->created_by != Auth::id()) {
                $this->notificationService->createNotification(
                    $escalation->created_by,
                    $escalation->id,
                    'update',
                    "{$editorName} updated ticket {$escalation->ticket_id}."
                );
            }

            // Notify the assigned user if different from editor
            if ($escalation->assigned_to && $escalation->assigned_to != Auth::id()) {
                $this->notificationService->createNotification(
                    $escalation->assigned_to,
                    $escalation->id,
                    'update',
                    "{$editorName} updated ticket {$escalation->ticket_id} assigned to you."
                );
            }
        }

        return redirect()
            ->route('list.index')
            ->with('success', 'Ticket updated!');
    }

    public function destroy($id)
    {
        $list = EscalationList::with(['category', 'subcategory', 'sub_department'])->findOrFail($id);

        // Find the related escalation before deleting
        $escalation = \Modules\Escalations\App\Models\Escalation::where('escalation_id', $list->id)->first();

        if ($escalation) {
            // Set the user who deleted it
            $escalation->deleted_by = Auth::id();
            $escalation->save();

            // Create notification for deletion
            $this->notificationService->setToastNotification('error', Auth::user()->name.' just deleted ticket '.$escalation->ticket_id);
        }

        $list->delete();

        return redirect()
            ->route('list.index')
            ->with('success', 'Ticket deleted!');
    }
}
