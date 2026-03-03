<div>
    @if (session()->has('info'))
        <div class="flex justify-center mx-auto bg-cyan-50 border-t-4 border-cyan-500 rounded-b text-cyan-900 px-4 py-3 shadow-sm mb-6"
            role="alert">
            <div class="flex py-1 items-center">
                <i class="fa-solid fa-circle-info text-cyan-900 text-3xl mr-2"></i>
                <div>
                    <p class="font-bold">Info</p>
                    <p class="text-sm">{{ session('info') }}</p>
                </div>
            </div>
        </div>
    @endif

    <section class="py-20 bg-gradient-to-r from-slate-900 to-cyan-900">
        <div class="container mx-auto w-3/4 md:w-2/3">
            <h3 class="text-3xl font-semibold text-cyan-100 text-center">Contact Us</h3>
            <h6 class="text-xl my-4 text-slate-200 text-center">
                Send us a message and a member of our team will reach out to you within the shortest possible time.
            </h6>
            <div class="relative grid mx-auto sm:grid-cols-3 gap-8">
                <div class="relative mt-12 sm:col-span-2 w-full mx-auto bg-white p-8 rounded-lg shadow-sm border border-slate-200">
                    <livewire:contact-form />
                </div>

                <div class="mt-12 md:ml-6 border-l-8 border-cyan-500 p-6 bg-slate-800/70 shadow-sm w-full rounded-lg text-cyan-100">
                    <div class="mb-10">
                        <h4 class="text-white text-xl font-bold">Contact Address:</h4>
                        <p>House 101, Zone D, Apo Resettlement, Abuja, Nigeria</p>
                    </div>
                    <div class="relative mb-10">
                        <h4 class="text-white text-xl font-bold">Contact Info:</h4>
                        <div class="flex flex-wrap md:flex-row sm:text-sm justify-between text-between">
                            <p>07062599737</p>
                            <p>08062869170</p>
                            <p class="mt-2 sm:mt-0">Email: info@mephed<wbr>.ng</p>
                        </div>
                    </div>

                    <div class="mb-10">
                        <h4 class="text-white text-xl font-bold">Office Hours:</h4>
                        <p>Mondays - Fridays: 8:00AM to 6:00PM (WAT)</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
