# Flutter Mobile App Debugging Guide

## Issue: TypeError: null: type 'Null' is not a subtype of type 'String'

This error occurs when your Flutter app receives null values from the API but expects strings. Here's how to resolve it:

## ✅ Backend API Status
- **Appointments API**: ✅ Working correctly with null-safe data
- **Outages API**: ✅ Working correctly with null-safe data  
- **Authentication**: ✅ Working with Sanctum tokens
- **Debug Endpoints**: ✅ Available for testing

## 🔧 Troubleshooting Steps

### Step 1: Clear Flutter App Cache
```bash
# In your Flutter project directory
flutter clean
flutter pub get
```

### Step 2: Test API Endpoints Directly
Use these working endpoints to verify data:

**Debug Endpoint:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json" \
     http://127.0.0.1:8000/api/mobile/debug
```

**Test Endpoint (Simple Data):**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json" \
     http://127.0.0.1:8000/api/mobile/test
```

**Appointments Endpoint:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json" \
     http://127.0.0.1:8000/api/mobile/appointments?page=1
```

### Step 3: Check Flutter Model Classes
Ensure your Dart model classes handle null values properly:

```dart
class Appointment {
  final int id;
  final String accountNumber;
  final String status;
  final String notes;
  
  Appointment({
    required this.id,
    required this.accountNumber,
    required this.status,
    required this.notes,
  });
  
  factory Appointment.fromJson(Map<String, dynamic> json) {
    return Appointment(
      id: json['id'] ?? 0,
      accountNumber: json['account_number'] ?? '',
      status: json['status'] ?? '',
      notes: json['notes'] ?? '', // This should now be '' instead of null
    );
  }
}
```

### Step 4: Update Flutter HTTP Calls
Make sure your API calls handle errors properly:

```dart
Future<List<Appointment>> fetchAppointments() async {
  try {
    final response = await http.get(
      Uri.parse('$baseUrl/api/mobile/appointments?page=1'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    );
    
    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      final appointments = (data['appointments'] as List)
          .map((json) => Appointment.fromJson(json))
          .toList();
      return appointments;
    } else {
      throw Exception('Failed to load appointments: ${response.statusCode}');
    }
  } catch (e) {
    print('Error fetching appointments: $e');
    throw Exception('Network error: $e');
  }
}
```

### Step 5: Add Null Safety Checks
Add defensive programming in your Flutter widgets:

```dart
Widget build(BuildContext context) {
  return ListView.builder(
    itemCount: appointments.length,
    itemBuilder: (context, index) {
      final appointment = appointments[index];
      return ListTile(
        title: Text(appointment.accountNumber.isNotEmpty 
            ? appointment.accountNumber 
            : 'No Account Number'),
        subtitle: Text(appointment.status.isNotEmpty 
            ? appointment.status 
            : 'No Status'),
        trailing: Text(appointment.notes.isNotEmpty 
            ? appointment.notes 
            : 'No Notes'),
      );
    },
  );
}
```

## 🧪 Test Token Generation
Get a fresh token for testing:

```php
php artisan tinker
$user = App\Models\User::where('email', 'fieldtech@test.com')->first();
$token = $user->createToken('mobile-app-debug')->plainTextToken;
echo $token;
```

## 📊 API Response Structure
The appointments API now returns this structure (all nulls converted to empty strings):

```json
{
  "appointments": [
    {
      "id": 8,
      "account_number": "sfl9393242",
      "appointment_ticket_id": "SHI-2",
      "status": "Scheduled-Closed",
      "notes": "",
      "comment": "",
      "team_type": {
        "id": 1,
        "type_name": "Inhouse",
        "status": "Active"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 5
  }
}
```

## 🚨 Common Issues & Solutions

### Issue: Still getting null values
**Solution**: Clear app cache and restart the app completely

### Issue: Authentication errors
**Solution**: Generate a new token using the test script

### Issue: Network timeouts
**Solution**: Ensure Laravel server is running on `http://127.0.0.1:8000`

### Issue: Wrong API endpoints
**Solution**: Use `/api/mobile/appointments` not `/api/mobile/mobile/appointments`

## 📱 Mobile App Debugging Commands

```bash
# Enable Flutter debugging
flutter run --debug

# View detailed logs
flutter logs

# Hot reload after changes
r (in flutter run session)

# Hot restart
R (in flutter run session)
```

## ✅ Verification Checklist

- [ ] Laravel server running on port 8000
- [ ] Fresh authentication token generated
- [ ] Flutter app cache cleared
- [ ] API endpoints returning data without null values
- [ ] Flutter model classes handle empty strings
- [ ] Error handling in HTTP calls
- [ ] Null safety checks in widgets

## 🔗 Working API Endpoints

- **Debug**: `GET /api/mobile/debug`
- **Test**: `GET /api/mobile/test`  
- **Appointments**: `GET /api/mobile/appointments`
- **Outages**: `GET /api/mobile/outages`

All endpoints require `Authorization: Bearer TOKEN` header.
