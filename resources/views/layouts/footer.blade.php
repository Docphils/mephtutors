<footer class="w-full bg-gradient-to-r from-cyan-900 to-cyan-600 text-white py-10 mt-auto">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start text-center md:text-left">

            <div class="space-y-3">
                <p class="text-2xl font-bold tracking-tight">
                    Meph<span class="text-cyan-300">Ed</span>
                </p>
                <p class="text-sm opacity-80">Empowering education through technology.</p>
                <div class="text-sm space-y-1">
                    <p class="flex items-center justify-center md:justify-start gap-2">
                        <i class="fa-solid fa-phone text-cyan-300"></i> 07062599737 | 08062869170
                    </p>
                    <p class="flex items-center justify-center md:justify-start gap-2">
                        <i class="fa-solid fa-envelope text-cyan-300"></i> info@mephed.ng
                    </p>
                </div>
            </div>

            <div class="flex flex-col space-y-3">
                <h4
                    class="font-semibold text-lg border-b border-cyan-400/30 pb-2 mb-2 inline-block md:block mx-auto md:mx-0">
                    Company</h4>
                <a href="{{ url('/privacy-policy') }}" class="text-sm hover:text-cyan-300 transition-colors">Privacy
                    Policy</a>
                <a wire:navigate href="{{ route('terms.service') }}"
                    class="text-sm hover:text-cyan-300 transition-colors">Terms of Service</a>
            </div>

            <div class="flex flex-col items-center md:items-end space-y-4">
                <h4 class="font-semibold text-lg">Follow our socials</h4>
                <div class="flex items-center gap-3">
                    <a href="https://www.facebook.com/mephed"
                        class="p-2 bg-white/10 rounded-full hover:bg-cyan-500 hover:scale-110 transition-all">
                        <i class="fa-brands fa-facebook-f w-5 h-5 flex items-center justify-center"></i>
                    </a>
                    <a href="https://www.x.com/mephconsults"
                        class="p-2 bg-white/10 rounded-full hover:bg-cyan-500 hover:scale-110 transition-all">
                        <i class="fa-brands fa-x-twitter w-5 h-5 flex items-center justify-center"></i>
                    </a>
                    <a href="https://www.instagram.com/mephed.ng"
                        class="p-2 bg-white/10 rounded-full hover:bg-cyan-500 hover:scale-110 transition-all">
                        <i class="fa-brands fa-instagram w-5 h-5 flex items-center justify-center"></i>
                    </a>
                    <a href="https://www.youtube.com/MephedLtd"
                        class="p-2 bg-white/10 rounded-full hover:bg-cyan-500 hover:scale-110 transition-all">
                        <i class="fa-brands fa-youtube w-5 h-5 flex items-center justify-center"></i>
                    </a>
                    <a href="https://www.linkedin.com/company/mephed"
                        class="p-2 bg-white/10 rounded-full hover:bg-cyan-500 hover:scale-110 transition-all">
                        <i class="fa-brands fa-linkedin-in w-5 h-5 flex items-center justify-center"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-10 pt-6 border-t border-white/10 text-center text-xs opacity-60">
            <p>All rights reserved. &copy; {{ date('Y') }} MephEd Educational Services.</p>
        </div>
    </div>
</footer>
