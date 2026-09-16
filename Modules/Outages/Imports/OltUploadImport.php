<?php

namespace Modules\Outages\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Outages\Models\Customer;
use Modules\Outages\Models\Fat;
use Modules\Outages\Models\Fdt;
use Modules\Outages\Models\Olt;
use Modules\Outages\Models\OltSlot;
use Modules\Outages\Models\PonPort;

/**
 * Bulk-loads the OLT -> Slot -> PON Port -> FDT -> FAT -> Customer hierarchy
 * from a spreadsheet. One row = one customer's full network path, with the
 * OLT/slot/port/FDT/FAT columns repeated on every row that shares them —
 * so this is ToCollection (each row can touch six different tables), not
 * ToModel (which only ever creates one row in one table per spreadsheet row).
 *
 * Column -> field mapping (WithHeadingRow slugs "OLT Name" to olt_name,
 * "IP address" to ip_address, etc — see Maatwebsite's HeadingRowFormatter):
 *   OLT Name, Vendor, IP address, Location, FDT, FAT, Slots, PON Ports,
 *   Customer Account Number, Customer Name, Onu Type, ONU physical Address,
 *   Bandwidth Profile Name
 *
 * "Slots" and "PON Ports" are the exact slot/PON-port number this row's FDT
 * is wired into (not the OLT's total capacity) — confirmed with the user.
 */
class OltUploadImport implements ToCollection, WithHeadingRow
{
    /** @var string[] */
    public array $errors = [];

    public int $processedRows = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            // +1 because $index is 0-based, +1 again because row 1 is the
            // heading row consumed by WithHeadingRow — so the first data
            // row (index 0) is spreadsheet row 2.
            $rowNumber = $index + 2;

            if ($this->isBlankRow($row)) {
                continue;
            }

            try {
                DB::transaction(fn () => $this->processRow($row));
                $this->processedRows++;
            } catch (\Throwable $e) {
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
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

        $oltName = trim((string) $row->get('olt_name', ''));
        $ipAddress = trim((string) $row->get('ip_address', ''));

        if ($oltName === '' || $ipAddress === '') {
            throw new \InvalidArgumentException('OLT Name and IP address are required.');
        }

        $olt = Olt::firstOrNew(['ip_address' => $ipAddress]);
        $isNewOlt = !$olt->exists;
        $olt->name = $oltName;
        $olt->vendor = $this->stringOrKeep($row->get('vendor'), $olt->vendor);
        $olt->location = $this->stringOrKeep($row->get('location'), $olt->location);
        if ($isNewOlt) {
            $olt->status = 'Active';
            $olt->created_by = $userId;
        } else {
            $olt->edited_by = $userId;
        }
        $olt->save();

        $slotNumber = $this->extractNumber($row->get('slots'));
        $portNumber = $this->extractNumber($row->get('pon_ports'));

        if ($slotNumber === null || $portNumber === null) {
            throw new \InvalidArgumentException('Slots and PON Ports must be numeric — they identify the exact slot/port this FDT is wired into.');
        }

        $slot = OltSlot::firstOrCreate(
            ['olt_id' => $olt->id, 'slot_number' => $slotNumber],
            ['status' => 'active', 'created_by' => $userId]
        );

        $port = PonPort::firstOrCreate(
            ['olt_slot_id' => $slot->id, 'pon_port_number' => $portNumber],
            ['status' => 'active', 'created_by' => $userId]
        );

        $fat = null;
        $fdtNumber = $this->extractNumber($row->get('fdt'));
        if ($fdtNumber !== null) {
            $fdt = Fdt::firstOrCreate(
                ['pon_port_id' => $port->id, 'fdt_number' => $fdtNumber],
                ['status' => 'active', 'created_by' => $userId]
            );

            $fatNumber = $this->extractNumber($row->get('fat'));
            if ($fatNumber !== null) {
                $fat = Fat::firstOrCreate(
                    ['fdt_id' => $fdt->id, 'fat_number' => $fatNumber],
                    ['status' => 'active', 'created_by' => $userId]
                );
            }
        }

        $accountNumber = trim((string) $row->get('customer_account_number', ''));
        if ($accountNumber !== '') {
            $customer = Customer::firstOrNew(['account_number' => $accountNumber]);
            $isNewCustomer = !$customer->exists;
            $customer->name = $this->stringOrKeep($row->get('customer_name'), $customer->name);
            $customer->onu_type = $this->stringOrKeep($row->get('onu_type'), $customer->onu_type);
            $customer->onu_physical_address = $this->stringOrKeep($row->get('onu_physical_address'), $customer->onu_physical_address);
            $customer->bandwidth_profile = $this->stringOrKeep($row->get('bandwidth_profile_name'), $customer->bandwidth_profile);
            if ($fat) {
                $customer->fat_id = $fat->id;
            }
            if ($isNewCustomer) {
                $customer->status = 'Active';
                $customer->created_by = $userId;
            } else {
                $customer->edited_by = $userId;
            }
            $customer->save();
        }
    }

    private function stringOrKeep($value, $existing)
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : $existing;
    }

    /**
     * Pulls a plain integer out of a cell that might just be "3", or might
     * be a label like "Slot 3" / "FDT-12" — the template's own example
     * values weren't specified, so this tolerates either without failing
     * the whole row over a formatting quirk.
     */
    private function extractNumber($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (preg_match('/(\d+)/', (string) $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
