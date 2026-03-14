<?php

namespace Modules\Outages\Services;

use Modules\Outages\Models\Outage;
use Modules\Outages\Models\OutageNotification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class OutageNotificationService
{
  /**
   * Create a notification for an outage.
   *
   * @param int $outageId
   * @param int $userId
   * @param string $message
   * @param string $type
   * @return OutageNotification
   */
  public function createNotification(int $outageId, int $userId, string $message, string $type): OutageNotification
  {
    // Create the notification
    $notification = OutageNotification::create([
      'outage_id' => $outageId,
      'user_id' => $userId,
      'message' => $message,
      'type' => $type,
      'read' => false,
      'created_by' => Auth::id(),
    ]);

    return $notification;
  }

  /**
   * Set toast notification in session.
   *
   * @param string $type success|info|warning|error
   * @param string $message
   * @return void
   */
  public function setToastNotification($type, $message)
  {
    Session::flash('toast_type', $type);
    Session::flash('toast_message', $message);
    Session::flash('toast_show', true);
  }

  /**
   * Create notifications for outage creation.
   *
   * @param Outage $outage
   * @return void
   */
  public function notifyOutageCreated(Outage $outage): void
  {
    $creator = Auth::user();
    $ticketId = $outage->ticket_id;

    // Create notification for the current user
    $message = "You created a new outage ticket {$ticketId}";
    $this->createNotification($outage->id, $creator->id, $message, 'create');

    // Also create notification for the assigned team members if available
    if (!empty($outage->assigned_team_id)) {
      $teamMembers = User::where('team_type_id', $outage->assigned_team_id)
        ->where('id', '!=', $creator->id)
        ->get();

      foreach ($teamMembers as $member) {
        $message = "{$creator->name} created a new outage ticket {$ticketId} assigned to your team";
        $this->createNotification($outage->id, $member->id, $message, 'create');
      }
    }

    // Create toast notification with user info
    $this->setToastNotification('success', "{$creator->name} just created outage ticket {$ticketId}");
  }

  /**
   * Create notifications for outage update.
   *
   * @param Outage $outage
   * @param User $editor
   * @return void
   */
  public function notifyOutageUpdated(Outage $outage, User $editor): void
  {
    $ticketId = $outage->ticket_id;

    // Always create a notification for the editor
    $message = "You updated outage ticket {$ticketId}";
    $this->createNotification($outage->id, $editor->id, $message, 'update');

    // Notify the creator if different from editor
    if (!empty($outage->created_by) && $outage->created_by != $editor->id) {
      $creator = User::find($outage->created_by);
      if ($creator) {
        $message = "{$editor->name} updated outage ticket {$ticketId}";
        $this->createNotification($outage->id, $creator->id, $message, 'update');
      }
    }

    // Notify assigned team members if different from editor
    if (!empty($outage->assigned_team_id)) {
      $teamMembers = User::where('team_type_id', $outage->assigned_team_id)
        ->where('id', '!=', $editor->id)
        ->get();

      foreach ($teamMembers as $member) {
        // Skip if this is the creator (already notified above)
        if (!empty($outage->created_by) && $member->id == $outage->created_by) {
          continue;
        }

        $message = "{$editor->name} updated outage ticket {$ticketId}";
        $this->createNotification($outage->id, $member->id, $message, 'update');
      }
    }

    // Create toast notification with user info
    $this->setToastNotification('info', "{$editor->name} just updated outage ticket {$ticketId}");
  }

  /**
   * Create notifications for outage assignment.
   *
   * @param Outage $outage
   * @param User $assigner
   * @param int $teamId
   * @return void
   */
  public function notifyOutageAssigned(Outage $outage, User $assigner, int $teamId): void
  {
    $ticketId = $outage->ticket_id;

    // Notify all team members
    $teamMembers = User::where('team_type_id', $teamId)->get();

    foreach ($teamMembers as $member) {
      $message = "{$assigner->name} assigned outage ticket {$ticketId} to your team";
      $this->createNotification($outage->id, $member->id, $message, 'assign');
    }

    // Create toast notification
    $teamName = $teamMembers->first()?->teamType?->type_name ?? 'Team';
    $this->setToastNotification('success', "Outage ticket {$ticketId} assigned to {$teamName} successfully");
  }

  /**
   * Create notifications for outage closure.
   *
   * @param Outage $outage
   * @param User $closer
   * @return void
   */
  public function notifyOutageClosed(Outage $outage, User $closer): void
  {
    $ticketId = $outage->ticket_id;

    // Notify the creator if different from closer
    if (!empty($outage->created_by) && $outage->created_by != $closer->id) {
      $creator = User::find($outage->created_by);
      if ($creator) {
        $message = "{$closer->name} closed outage ticket {$ticketId}";
        $this->createNotification($outage->id, $creator->id, $message, 'close');
      }
    }

    // Notify assigned team members if different from closer
    if (!empty($outage->assigned_team_id)) {
      $teamMembers = User::where('team_type_id', $outage->assigned_team_id)
        ->where('id', '!=', $closer->id)
        ->get();

      foreach ($teamMembers as $member) {
        // Skip if this is the creator (already notified above)
        if (!empty($outage->created_by) && $member->id == $outage->created_by) {
          continue;
        }

        $message = "{$closer->name} closed outage ticket {$ticketId}";
        $this->createNotification($outage->id, $member->id, $message, 'close');
      }
    }

    // Create toast notification
    $this->setToastNotification('success', "Outage ticket {$ticketId} closed successfully");
  }

  /**
   * Create notifications for outage download/export.
   *
   * @param Outage $outage
   * @param User $downloader
   * @param string $downloadType
   * @return void
   */
  public function notifyOutageDownloaded(Outage $outage, User $downloader, string $downloadType = 'report'): void
  {
    $ticketId = $outage->ticket_id;

    // Create notification for the downloader
    $message = "You downloaded {$downloadType} for outage ticket {$ticketId}";
    $this->createNotification($outage->id, $downloader->id, $message, 'download');

    // Notify the creator if different from downloader
    if (!empty($outage->created_by) && $outage->created_by != $downloader->id) {
      $creator = User::find($outage->created_by);
      if ($creator) {
        $message = "{$downloader->name} downloaded {$downloadType} for outage ticket {$ticketId}";
        $this->createNotification($outage->id, $creator->id, $message, 'download');
      }
    }

    // Create toast notification
    $this->setToastNotification('info', "{$downloadType} for outage ticket {$ticketId} downloaded successfully");
  }

  /**
   * Create notifications for outage status change.
   *
   * @param Outage $outage
   * @param User $updater
   * @param string $oldStatus
   * @param string $newStatus
   * @return void
   */
  public function notifyOutageStatusChanged(Outage $outage, User $updater, string $oldStatus, string $newStatus): void
  {
    $ticketId = $outage->ticket_id;

    // Create notification for the updater
    $message = "You changed status of outage ticket {$ticketId} from {$oldStatus} to {$newStatus}";
    $this->createNotification($outage->id, $updater->id, $message, 'status_change');

    // Notify the creator if different from updater
    if (!empty($outage->created_by) && $outage->created_by != $updater->id) {
      $creator = User::find($outage->created_by);
      if ($creator) {
        $message = "{$updater->name} changed status of outage ticket {$ticketId} from {$oldStatus} to {$newStatus}";
        $this->createNotification($outage->id, $creator->id, $message, 'status_change');
      }
    }

    // Notify assigned team members
    if (!empty($outage->assigned_team_id)) {
      $teamMembers = User::where('team_type_id', $outage->assigned_team_id)
        ->where('id', '!=', $updater->id)
        ->get();

      foreach ($teamMembers as $member) {
        // Skip if this is the creator (already notified above)
        if (!empty($outage->created_by) && $member->id == $outage->created_by) {
          continue;
        }

        $message = "{$updater->name} changed status of outage ticket {$ticketId} from {$oldStatus} to {$newStatus}";
        $this->createNotification($outage->id, $member->id, $message, 'status_change');
      }
    }

    // Create toast notification
    $this->setToastNotification('info', "Status of outage ticket {$ticketId} changed to {$newStatus}");
  }
}
