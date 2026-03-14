@props(['id' => 'createEscalationModal', 'title' => 'Create New Escalation', 'escalation' => null])

<!-- Modal -->
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ $title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ $escalation ? route('escalations.update', $escalation->id) : route('escalations.store') }}" method="POST">
        @csrf
        @if($escalation)
          @method('PUT')
        @endif
        
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="form-group">
                <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                <input type="text" name="account_number" id="account_number" class="form-control @error('account_number') is-invalid @enderror" 
                       value="{{ old('account_number', $escalation->account_number ?? '') }}" required>
                @error('account_number')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                <input type="text" name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" 
                       value="{{ old('category_id', $escalation->category_id ?? '') }}" required>
                @error('category_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="sub_category_id" class="form-label">Sub Category <span class="text-danger">*</span></label>
                <input type="text" name="sub_category_id" id="sub_category_id" class="form-control @error('sub_category_id') is-invalid @enderror" 
                       value="{{ old('sub_category_id', $escalation->sub_category_id ?? '') }}" required>
                @error('sub_category_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="source_id" class="form-label">Source <span class="text-danger">*</span></label>
                <input type="text" name="source_id" id="source_id" class="form-control @error('source_id') is-invalid @enderror" 
                       value="{{ old('source_id', $escalation->source_id ?? '') }}" required>
                @error('source_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-12">
              <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                          rows="3">{{ old('description', $escalation->description ?? '') }}</textarea>
                @error('description')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                  <option value="">Select Priority</option>
                  <option value="low" {{ old('priority', $escalation->priority ?? '') == 'low' ? 'selected' : '' }}>Low</option>
                  <option value="medium" {{ old('priority', $escalation->priority ?? '') == 'medium' ? 'selected' : '' }}>Medium</option>
                  <option value="high" {{ old('priority', $escalation->priority ?? '') == 'high' ? 'selected' : '' }}>High</option>
                </select>
                @error('priority')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                  <option value="">Select Status</option>
                  <option value="open" {{ old('status', $escalation->status ?? '') == 'open' ? 'selected' : '' }}>Open</option>
                  <option value="open-escalated" {{ old('status', $escalation->status ?? '') == 'open-escalated' ? 'selected' : '' }}>Open Escalated</option>
                  <option value="closed" {{ old('status', $escalation->status ?? '') == 'closed' ? 'selected' : '' }}>Closed</option>
                  <option value="closed-escalated" {{ old('status', $escalation->status ?? '') == 'closed-escalated' ? 'selected' : '' }}>Closed Escalated</option>
                  <option value="in_progress" {{ old('status', $escalation->status ?? '') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                  <option value="resolved" {{ old('status', $escalation->status ?? '') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                </select>
                @error('status')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary btn-xs" data-bs-dismiss="modal">
            <i class="ti ti-x me-1"></i> Cancel
          </button>
          <button type="submit" class="btn btn-primary btn-xs">
            <i class="ti ti-device-floppy me-1"></i> Save
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

