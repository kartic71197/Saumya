
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - {{ $appointment->organization->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-2xl w-full">
            <!-- Success Card -->
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <!-- Success Icon -->
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h1 class="text-3xl font-medium text-gray-800 mb-2">Booking Confirmed!</h1>
                <p class="text-gray-600 mb-8">Your appointment has been successfully booked</p>

                <!-- Appointment Details -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                    <h2 class="text-lg font-medium text-gray-800 mb-4">Appointment Details</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Booking ID</span>
                            <span class="font-medium text-gray-800">#{{ $appointment->id }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Organization</span>
                            <span class="font-medium text-gray-800">{{ $appointment->organization->name }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Date</span>
                            <span class="font-medium text-gray-800">
                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, F j, Y') }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Time</span>
                            <span class="font-medium text-gray-800">
                                {{ \Carbon\Carbon::parse($appointment->start_time)->format('g:i A') }} - 
                                {{ \Carbon\Carbon::parse($appointment->end_time)->format('g:i A') }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Duration</span>
                            <span class="font-medium text-gray-800">{{ $appointment->duration }} minutes</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Staff</span>
                            <span class="font-medium text-gray-800">{{ $appointment->staff->name }}</span>
                        </div>

                        <div class="flex justify-between pt-3 border-t border-gray-200">
                            <span class="text-lg font-medium text-gray-800">Total Price</span>
                            <span class="text-xl font-semibold text-gray-900">${{ number_format($appointment->price, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="bg-blue-50 rounded-lg p-4 mb-6 text-left">
                    <p class="text-sm text-blue-800">
                        <span class="font-medium">📧 Confirmation sent to:</span> {{ $appointment->customer->email }}
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4">
                    <a href="{{ route('appointments.client.index') }}" 
                       class="flex-1 bg-gray-800 hover:bg-gray-700 text-white py-3 rounded-lg transition-colors font-medium text-center">
                        Book Another
                    </a>
                    <button onclick="window.print()" 
                            class="flex-1 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 py-3 rounded-lg transition-colors font-medium">
                        Print Confirmation
                    </button>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="mt-6 text-center text-sm text-gray-600">
                <p>Need to make changes? Contact {{ $appointment->organization->name }} directly.</p>
            </div>
        </div>
    </div>
</body>
</html>