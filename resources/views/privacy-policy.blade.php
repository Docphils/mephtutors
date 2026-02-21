<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MephEd') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-cyan-300 font-sans leading-normal tracking-normal h-screen"> 
        @include('layouts.header')    
        <div class="container mx-auto p-6 bg-white shadow-md rounded-sm">
            <h1 class="text-3xl font-bold mb-4">Privacy Policy</h1>

            <p class="mb-4">Last updated: 1st August, 2024</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">Introduction</h2>
            <p class="mb-4">MephEd values your privacy and is committed to protecting your personal information. This Privacy Policy outlines our practices regarding the collection, use, and protection of your data.</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">Information We Collect</h2>
            <ul class="list-disc ml-6 mb-4">
                <li><strong>Personal Information:</strong> Name, email address, phone number, and payment details.</li>
                <li><strong>Usage Data:</strong> Browser type, IP address, pages visited, and time spent on our site.</li>
                <li><strong>Cookies:</strong> To enhance your experience and gather analytics.</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">How We Use Your Information</h2>
            <ul class="list-disc ml-6 mb-4">
                <li><strong>To Provide Services:</strong> Process your requests, manage your account, and provide customer support.</li>
                <li><strong>To Improve Our Services:</strong> Analyze usage to enhance user experience and develop new features.</li>
                <li><strong>To Communicate:</strong> Send updates, newsletters, and promotional materials, if you opt-in.</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">Sharing Your Information</h2>
            <p class="mb-4">We do not sell, trade, or rent your personal information to others. We may share your data with:</p>
            <ul class="list-disc ml-6 mb-4">
                <li><strong>Service Providers:</strong> Trusted third parties who assist in operating our website and services, subject to confidentiality agreements.</li>
                <li><strong>Legal Requirements:</strong> When required by law to comply with legal processes or protect our rights.</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">Data Security</h2>
            <p class="mb-4">We implement robust security measures to protect your information from unauthorized access, alteration, or disclosure. However, no internet transmission is completely secure, and we cannot guarantee absolute security.</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">Your Rights</h2>
            <ul class="list-disc ml-6 mb-4">
                <li><strong>Access and Correction:</strong> You can request access to your data and correct any inaccuracies.</li>
                <li><strong>Opt-Out:</strong> Unsubscribe from our communications at any time.</li>
                <li><strong>Delete Data:</strong> Request the deletion of your personal information, subject to legal and contractual obligations.</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">Children's Privacy</h2>
            <p class="mb-4">Our services are not directed to individuals under 13. We do not knowingly collect personal information from children under 13. If we become aware of such data, we will take steps to delete it.</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">Changes to This Policy</h2>
            <p class="mb-4">We may update this Privacy Policy periodically. We will notify you of any changes by posting the new policy on our website. Your continued use of our services after changes are posted constitutes your acceptance of the updated policy.</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">Contact Us</h2>
            <p class="mb-4">If you have any questions or concerns about this Privacy Policy or our data practices, please contact us at:</p>
            <p class="mb-4">
                MephEd<br>
                Email: <a href="https://www.mephconsults.com.ng" class="text-blue-500">info@mephed.ng</a><br>
                Phone: <a href="tel:+2347062599737" class="text-blue-500">+234(0)7062599737, +234(0)8062869170</a>
            </p>
        </div>
        @include('layouts.footer')
        @livewireScripts
    </body>
</html>