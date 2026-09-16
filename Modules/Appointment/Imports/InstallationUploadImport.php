<?php

namespace Modules\Appointment\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentHistory;
use Modules\Appointment\Models\AppointmentStatus;
use Modules\Appointment\Models\AppointmentStatusHistory;
use Modules\Appointment\Models\AppointmentType;
use Modules\Appointment\Models\SubAppointmentType;
use Modules\Outages\Models\Customer;
use Modules\Outages\Models\Olt;

/**
 * Bulk-loads site-visit / installation records into the Appointment list
 * (type = Installation) so a dispatcher can then assign a technician via the
 * normal appointment edit screen — this import never assigns one itself and
 * never enqueues into the live FIFO queue, since these rows are historical
 * backlog data, not new real-time requests.
 *
 * Column -> field mapping (WithHeadingRow slugs "Customer Name" to
 * customer_name, "Infra Feedback Date And Time" to
 * infra_feedback_date_and_time, etc.):
 *   Customer Name, Account Number, Contact Number, Alternative Contact
 *   Number, Date Received, FDT, OLT, Road Name, Dispatcher, Team Assigned,
 *   Installation Type, Category, Dispatch Update, Escalation Date,
 *   Location Coords, Escalation Notes, Infra Feedback,
 *   Infra Feedback Date And Time, Design Feedback
 *
 * Every row also upserts a Modules\Outages\Models\Customer by account
 * number (matching the OltUploadImport pattern) so contact details captured
 * here also show up in the Customer search used elsewhere in the app.
 */
class InstallationUploadImport implements ToCollection, WithHeadingRow
{
    /** @var string[] */
    public array $errors = [];

    public int $processedRows = 0;

    private ?AppointmentType $installationType = null;

    private ?AppointmentStatus $defaultStatus = null;

    public function collection(Collection $rows): void
    {
        $this->installationType = AppointmentType::where('type_name', 'Installation')->first();
        if (! $this->installationType) {
            $this->errors[] = 'No "Installation" appointment type is configured — nothing was imported. Set one up under Appointment Types first.';

            return;
        }

        $this->defaultStatus = AppointmentStatus::where('name', 'scheduled-open')->first();
        if (! $this->defaultStatus) {
            $this->errors[] = 'No "scheduled-open" appointment status is configured — nothing was imported.';

            return;
        }

        foreach ($rows as $index => $row) {
            // +1 because $index is 0-based, +1 again because row 1 is the
            // heading row consumed by WithHeadingRow.
            $rowNumber = $index + 2;

            if ($this->isBlankRow($row)) {
                continue;
            }

            try {
                DB::transaction(fn () => $this->processRow($row));
                $this->processedRows++;
            } catch (\Throwable $e) {
                $this->errors[] = "Row {$rowNumber}: ".$e->getMessage();
            }
        }
    }

    private function isBlankRow(Collection $row): bool
    {
        return $row->filter(fn ($value) => trim((string) $value) !== '')->isEmpty();
    }

    private function processRow(Collection $row): void
    {
        $userId = Auth::id();

        $accountNumber = trim((string) $row->get('account_number', ''));
        if ($accountNumber === '') {
            throw new \InvalidArgumentException('Account Number is required.');
        }

        $customerName = trim((string) ($row->get('customer_name') ?? ''));
        $contactNumber = trim((string) ($row->get('contact_number') ?? ''));
        $alternativeContactNumber = trim((string) ($row->get('alternative_contact_number') ?? ''));
        $oltName = trim((string) ($row->get('olt') ?? ''));

        $subType = $this->resolveSubType($row->get('installation_type'));

        $olt = $oltName !== '' ? Olt::where('name', $oltName)->first() : null;

        $prefix = $this->installationType->code_prefix ?: 'INS';
        $ticketNumber = \App\Services\SequenceNumberService::next('appointment:'.$prefix);

        $appointment = Appointment::create([
            'account_number' => $accountNumber,
            'appointment_ticket_id' => $prefix.'-'.$ticketNumber,
            'appointment_id' => $this->installationType->id,
            'appointment_type_id' => $subType?->id,
            'customer_name' => $customerName !== '' ? $customerName : null,
            'contact_number' => $contactNumber !== '' ? $contactNumber : null,
            'alternative_contact_number' => $alternativeContactNumber !== '' ? $alternativeContactNumber : null,
            'date_received' => $this->parseDate($row->get('date_received')),
            'fdt_code' => $this->nullIfBlank($row->get('fdt')),
            'olt_name_raw' => $oltName !== '' ? $oltName : null,
            'olt_id' => $olt?->id,
            'road_name' => $this->nullIfBlank($row->get('road_name')),
            'dispatcher' => $this->nullIfBlank($row->get('dispatcher')),
            'team_assigned_name' => $this->nullIfBlank($row->get('team_assigned')),
            'imported_category' => $this->nullIfBlank($row->get('category')),
            'dispatch_update' => $this->nullIfBlank($row->get('dispatch_update')),
            'escalation_date' => $this->parseDate($row->get('escalation_date')),
            'location_coords' => $this->nullIfBlank($row->get('location_coords')),
            'escalation_notes' => $this->nullIfBlank($row->get('escalation_notes')),
            'infra_feedback' => $this->nullIfBlank($row->get('infra_feedback')),
            'infra_feedback_at' => $this->parseDateTime($row->get('infra_feedback_date_and_time')),
            'design_feedback' => $this->nullIfBlank($row->get('design_feedback')),
            'imported_status_raw' => $this->nullIfBlank($row->get('status')),
            'priority' => 'Medium',
            'status' => $this->defaultStatus->name,
            'description_notes' => $this->nullIfBlank($row->get('dispatch_update'))
                ?? 'Imported from installation data upload.',
            'appointment_location' => $this->nullIfBlank($row->get('road_name')),
            'created_by' => $userId,
            'edited_by' => $userId,
        ]);

        AppointmentStatusHistory::create([
            'appointment_id' => $appointment->id,
            'previous_status' => null,
            'new_status' => $appointment->status,
            'notes' => 'Created via installation data bulk upload',
            'changed_by' => $userId,
        ]);

        AppointmentHistory::create([
            'appointment_id' => $appointment->id,
            'action' => 'created',
            'ticket_id' => $appointment->appointment_ticket_id,
            'action_by' => $userId,
            'status' => $appointment->status,
            'account_number' => $appointment->account_number,
            'priority' => $appointment->priority,
            'appointment_type_id' => $appointment->appointment_type_id,
            'olt_id' => $appointment->olt_id,
        ]);

        if ($customerName !== '' || $contactNumber !== '' || $alternativeContactNumber !== '') {
            $customer = Customer::firstOrNew(['account_number' => $accountNumber]);
            $isNewCustomer = ! $customer->exists;
            $customer->name = $this->stringOrKeep($customerName, $customer->name);
            $customer->mobile_number = $this->stringOrKeep($contactNumber, $customer->mobile_number);
            $customer->alternative_number = $this->stringOrKeep($alternativeContactNumber, $customer->alternative_number);
            $customer->address = $this->stringOrKeep($row->get('road_name'), $customer->address);
            if ($isNewCustomer) {
                $customer->status = 'Active';
                $customer->created_by = $userId;
            } else {
                $customer->edited_by = $userId;
            }
            $customer->save();
        }
    }

    private function resolveSubType($value): ?SubAppointmentType
    {
        $name = trim((string) ($value ?? ''));

        $query = SubAppointmentType::where('appointment_type_id', $this->installationType->id);

        if ($name !== '') {
            $match = (clone $query)->whereRaw('LOWER(sub_type_name) = ?', [strtolower($name)])->first();
            if ($match) {
                return $match;
            }
        }

        return $query->orderBy('id')->first();
    }

    private function nullIfBlank($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }

    private function stringOrKeep($value, $existing)
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : $existing;
    }

    private function parseDate($value): ?string
    {
        $date = $this->parseDateTime($value);

        return $date?->toDateString();
    }

    private function parseDateTime($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return $this->withinSaneRange(Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)));
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return $this->withinSaneRange(Carbon::parse((string) $value));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Source rows include junk like "0/1/00" — PHP's date parser doesn't
     * reject that, it silently rolls month 0 back into December of the
     * previous year (1999-12-01), planting a plausible-looking but fake
     * date. Since every real date in this system's data falls in a narrow
     * recent range, anything outside it is treated as unparseable.
     */
    private function withinSaneRange(?Carbon $date): ?Carbon
    {
        if ($date === null) {
            return null;
        }

        $year = (int) $date->format('Y');

        return ($year >= 2015 && $year <= (int) now()->addYears(2)->format('Y')) ? $date : null;
    }
}
