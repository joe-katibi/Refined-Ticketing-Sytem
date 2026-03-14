    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        \Log::info('Starting appointment update process', ['appointment_id' => $id]);

        try {
            $appointment = Appointment::findOrFail($id);
            \Log::debug('Appointment found', ['appointment' => $appointment->toArray()]);

            // Log all incoming request data
            \Log::debug('Request data received:', $request->all());

            // Validate the request
            $validatedData = $request->validate([
                'account_number' => 'required|string|max:50',
                'type_id' => 'required|exists:appointment_types,id',
                'appointment_type_id' => 'required|exists:sub_appointment_types,id',
                'priority' => 'required|in:High,Medium,Low',
                'scheduled_date' => 'required|date',
                'scheduled_time' => 'required',
                'appointment_location' => 'required|string|max:255',
                'appointment_venue' => 'required|string|max:255',
                'description_notes' => 'required|string',
                'status' => 'required|in:Scheduled-Open,Scheduled-Closed,Escalated-Open,Escalated-Closed,In-Progress,Completed,Cancelled',
                'team_type_id' => 'required|exists:team_types,id',
                'sub_team_type_id' => 'required|exists:sub_team_types,id',
                'assigned_team_id' => 'nullable|exists:teams,id',
                'escalated_team_id' => 'nullable|exists:sub_team_types,id',
            ]);

            // Map the form fields to the correct database columns
            $validatedData['appointment_id'] = $validatedData['type_id'];
            unset($validatedData['type_id']);

            \Log::debug('Validation passed', ['validated_data' => $validatedData]);

            // Add editor information
            $validatedData['edited_by'] = auth()->id();
            \Log::debug('Added editor info', ['edited_by' => auth()->id()]);

            // Handle appointment completion
            if ($request->status === 'Scheduled-Closed' && $appointment->status !== 'Scheduled-Closed') {
                $validatedData['completed_date'] = now()->toDateString();
                $validatedData['completed_time'] = now()->toTimeString();
                $validatedData['closed_by'] = auth()->id();
                $validatedData['closed_at'] = now();
                \Log::info('Marking appointment as completed', [
                    'completed_date' => $validatedData['completed_date'],
                    'completed_time' => $validatedData['completed_time']
                ]);
            }

            // Log original data
            $originalData = $appointment->getOriginal();
            \Log::debug('Original appointment data', $originalData);
            \Log::debug('New appointment data', $validatedData);

            // Find and log changed fields
            $changes = [];
            foreach ($validatedData as $key => $value) {
                if (!array_key_exists($key, $originalData) || $originalData[$key] != $value) {
                    $changes[$key] = [
                        'from' => $originalData[$key] ?? null,
                        'to' => $value
                    ];
                }
            }

            \Log::info('Detected changes', ['changes' => $changes]);

            if (empty($changes)) {
                \Log::warning('No changes detected in the update request');
                return redirect()->back()->with('info', 'No changes were made.');
            }

            // Update the appointment
            $appointment->update($validatedData);
            \Log::info('Appointment updated successfully', ['appointment_id' => $appointment->id]);

            // Record history if there are changes
            if (!empty($changes) && class_exists('\Modules\Appointment\Models\AppointmentHistory')) {
                try {
                    $history = \Modules\Appointment\Models\AppointmentHistory::create([
                        'appointment_id' => $appointment->id,
                        'appointment_ticket_id' => $appointment->appointment_ticket_id,
                        'escalation_ticket_id' => $appointment->escalation_ticket_id,
                        'outage_ticket_id' => $appointment->outage_ticket_id,
                        'appointment_id' => $appointment->appointment_id,
                        'appointment_type_id' => $appointment->appointment_type_id,
                        'sub_department_id' => $appointment->sub_department_id,
                        'category_id' => $appointment->category_id,
                        'sub_category_id' => $appointment->sub_category_id,
                        'olt_id' => $appointment->olt_id,
                        'priority' => $appointment->priority,
                        'status' => $appointment->status,
                        'scheduled_date' => $appointment->scheduled_date,
                        'scheduled_time' => $appointment->scheduled_time,
                        'completed_date' => $appointment->completed_date,
                        'completed_time' => $appointment->completed_time,
                        'assigned_team_id' => $appointment->assigned_team_id,
                        'escalated_team_id' => $appointment->escalated_team_id,
                        'team_type_id' => $appointment->team_type_id,
                        'sub_team_type_id' => $appointment->sub_team_type_id,
                        'closed_by' => $appointment->closed_by,
                        'notes_created' => $appointment->notes_created,
                        'notes_closed' => $appointment->notes_closed,
                        'closing_reason' => $appointment->closing_reason,
                        'escalation_reason' => $appointment->escalation_reason,
                        'escalation_notes' => $appointment->escalation_notes,
                        'appointment_type' => $appointment->appointment_type,
                        'appointment_status' => $appointment->appointment_status,
                        'appointment_location' => $appointment->appointment_location,
                        'appointment_venue' => $appointment->appointment_venue,
                        'created_by' => $appointment->created_by,
                        'edited_by' => $appointment->edited_by,
                        'closed_at' => $appointment->closed_at,
                        'escalated_at' => $appointment->escalated_at,
                        'action' => 'updated',
                        'action_by' => auth()->id(),
                        'status' => $appointment->status
                    ]);
                    \Log::debug('History record created', ['history_id' => $history->id]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create history record', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Create notifications using the notification service
            $this->notificationService->notifyAppointmentUpdated($appointment, auth()->user());

            return redirect()->route('appointment.appointments.show', $appointment->id)
                ->with('success', 'Appointment updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            throw $e;

        } catch (\Exception $e) {
            \Log::error('Error updating appointment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'appointment_id' => $id,
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the appointment. Please try again.')
                ->withErrors(['exception' => $e->getMessage()]);
        }
    }
