<!-- Escalation Modal Component -->
<div class="modal fade" id="escalationModal" tabindex="-1" aria-labelledby="escalationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="escalationModalLabel">Add New Escalation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Your escalation form fields go here -->
        <form id="escalationForm">
          <div class="mb-3">
            <label for="escalation-title" class="form-label">Title</label>
            <input type="text" class="form-control" id="escalation-title" name="title" required>
          </div>
          <div class="mb-3">
            <label for="escalation-description" class="form-label">Description</label>
            <textarea class="form-control" id="escalation-description" name="description" rows="3" required></textarea>
          </div>
          <!-- Add more fields as needed -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary btn-xs" form="escalationForm">Save Escalation</button>
      </div>
    </div>
  </div>
</div>

