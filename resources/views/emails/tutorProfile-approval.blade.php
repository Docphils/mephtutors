<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Profile Status</title>
    
     @vite(['resources/css/app.css', 'resources/js/app.js'])
     @livewireStyles
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="max-w-2xl mx-auto mt-10 bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header Section -->
        <div class="bg-cyan-900 p-6 text-white">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/MephEd.png') }}" alt="MephEd Logo" class="w-12 h-6 mr-4">
                <h1 class="text-2xl font-bold">Tutor Profile Status Update</h1>
            </div>
        </div>

        <!-- Body Section -->
        <div class="p-6">
            <p class="text-lg font-medium mb-4">Hello {{ $tutorProfile['fullName'] }},</p>
            <p>We have reviewed your tutor profile details and have updated it's status. See details below:</p>

            <!-- Request Details -->
            <div class="mt-6 bg-gray-50 p-4 rounded-lg shadow">
                @php
                    if($tutorProfile['Approved'] == false ){
                        $status = 'Review Profile';
                    }else{
                        $status = 'Approved';
                    }
                @endphp
                <ul class="space-y-2 text-sm">
                    <li><strong>Status:</strong> {{ $status }}</li>
                    <li><strong>Remark:</strong> {{ $tutorProfile['approvalRemark'] }}</li>
                </ul>
            </div>

            @if ($status == 'Approved' )
            <p class="mt-6 text-gray-600">Congratulations! You are now eligible for tutoring roles with MephEd. Do join our tutors community for exciting updates</p>  

            @else
            <p class="mt-6 text-gray-600">Please visit your dashboard and effect the necessary recommendations for opportunity to join our esteemed league of tutors.</p>  
            @endif
            
            <p class="mt-4 text-gray-600">Best regards,<br><strong>MephEd Support Team</strong></p>
        </div>

        <!-- Footer Section -->
        <div class="bg-cyan-800 p-4 text-center text-white text-sm">
            &copy; {{ date('Y') }} MephEd. All rights reserved.
        </div>
    </div>
</body>
</html>
