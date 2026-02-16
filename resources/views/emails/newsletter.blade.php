    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>MephEd Newsletter</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            .body{
                color: black;
            }
            .container{
                max-width: 80%;
                margin: auto;
                margin-top: 3rem;
                border-radius: 25px;
            }
            .header{
                background-color: darkslategray;
                color: white;
                padding: 2rem;
                text-align: center;
            }
            .mail-body{
                color: darkslategray;
            }
            .footer{
                background-color: darkslategray;
                color: white;
                text-align: center;
                font-size: 0.75em;
                padding: 2em;
            }
        </style>

        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class=" body bg-gray-100 text-gray-800">
        <div class="container max-w-2xl mx-auto mt-10 bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Header Section -->
            <div class="header bg-gray-800 p-6 text-white">
                <div class="flex items-center justify-center bg-cyan-800">
                    <img src="{{ asset('images/MephEd.png') }}" alt="MephEd Logo" class="w-12 h-6 mr-4">
                    <h1 class="text-lg font-bold">{{ $content['title'] }}</h1>
                </div>
            </div>

            <!-- Body Section -->
            <div class="mail-body p-6">
                <p class="text-lg font-medium mb-4">Greetings,</p>
                <p></p>
                

                <p class="mt-6 text-gray-600">{!! $content['body'] !!}</p>
                <p class="mt-4 text-gray-600">{{ $content['body2'] }}</p>
                <p class="mt-4 text-gray-600">Best regards,<br><strong>MephEd Support Team</strong></p>
            </div>

            <!-- Footer Section -->
            <div class="footer bg-cyan-800 p-4 text-center text-white text-sm">
                &copy; {{ date('Y') }} MephEd. All rights reserved.
            </div>
        </div>
        @livewireScripts
    </body>
    </html>
