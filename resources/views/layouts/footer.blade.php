<!-- Footer Section -->
<footer class="relative w-full bottom-0 hidden sm:block bg-gradient-to-r from-cyan-900 to-cyan-500 text-white py-6">
    <div class="container sm:flex mx-auto text-center justify-between">
        <div class="text-start border-r-white border-r-2 px-5 mb-4">
            <p>&copy; 2024 <span class="text-lg">Meph<span class="text-cyan-300">Ed</span> </span>All rights reserved.</p>
            <p>Phone: 07062599737 | 08062869170</p>
            <p>Email: info@mephed.ng</p>
        </div>
        <div class="grid px-5 sm:px-2 mb-4">
            <a href="{{ url('/privacy-policy') }}" class="hover:underline">Privacy Policy</a>
            <a wire:navigate href="{{ route('terms.service') }}" class="hover:underline">Terms of Service</a>
        </div>
        <div class="grid px-5 sm:px-2 text-end mb-4">
            <a href="https://www.facebook.com/mephed" class="hover:underline text-blue-200">Facebook</a>
            <a href="https://www.x.com/mephconsults" class="hover:underline text-sky-900">Twitter</a>
            <a href="https://www.instagram.com/mephed.ng" class="hover:underline text-pink-600">Instagram</a>
            <a href="https://www.youtube.com/MephedLtd" class="hover:underline text-red-600">YouTube</a>
        </div>
    </div>
</footer>


<!-- Mobile Footer Section -->
<footer class="sm:hidden bg-gradient-to-r from-cyan-900 to-cyan-500 text-white py-6">
    <div class="container sm:flex mx-auto text-center justify-between">
        <div class="flex text-start justify-between mb-8">
            <div class="grid px-5 sm:px-2 mb-4">
                <a href="{{ url('/privacy-policy') }}" class="hover:underline">Privacy Policy</a>
                <a href="{{ route('terms.service') }}" class="hover:underline">Terms of Service</a>
            </div>
            <div class="grid px-5 sm:px-2">
                <a href="https://www.facebook.com/mephed" class="hover:underline text-blue-200">Facebook</a>
                <a href="https://www.x.com/mephconsults" class="hover:underline text-sky-900">Twitter</a>
                <a href="https://www.instagram.com/mephed.ng" class="hover:underline text-pink-600">Instagram</a>
                <a href="https://www.youtube.com/MephedLtd" class="hover:underline text-red-600">YouTube</a>
            </div>
        </div>
        <div class="border-r-white border-r-2 px-5 mb-4">
            <p>&copy; 2024 <span class="text-xl">Meph<span class="text-cyan-300">Ed</span> </span>All rights reserved.
            </p>
            <p>Phone: 07062599737 | 08062869170</p>
            <p>Email: info@mephed.ng</p>
        </div>

    </div>
</footer>
