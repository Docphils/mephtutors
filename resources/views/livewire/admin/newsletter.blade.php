<div class="p-4 sm:p-6 bg-slate-50 min-h-screen">
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
            <div class="flex items-center gap-3">
                <a wire:navigate href="{{ route('admin.dashboard') }}"
                    class="group flex items-center justify-center w-10 h-10 bg-white border border-slate-200 text-cyan-600 rounded-xl shadow-sm hover:bg-cyan-600 hover:text-white transition-all shrink-0">
                    <i class="fa fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Newsletter <span
                            class="text-cyan-600">Marketing</span></h2>
                    <p class="text-slate-500 text-sm font-medium">Broadcast updates and promotions to your community.</p>
                </div>
            </div>

            <button x-on:click="$dispatch('createNewsletter')"
                class="flex items-center justify-center gap-2 bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-cyan-200 transition-all">
                <i class="fa fa-plus text-xs"></i>
                <span>New Campaign</span>
            </button>
        </div>
    </x-slot>

    @if (session()->has('success'))
        <div
            class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 animate-fade-in">
            <i class="fa-solid fa-circle-check text-xl"></i>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- Left: History & Listing --}}
        <div class="lg:col-span-5 xl:col-span-4 space-y-4">
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 p-6">
                <h3 class="font-black text-slate-800 uppercase tracking-widest text-xs mb-6">Recent Campaigns</h3>

                <div class="space-y-3">
                    @forelse ($campaigns as $campaign)
                        <div wire:click="viewDetails({{ $campaign->id }})"
                            class="group p-4 rounded-2xl border transition-all cursor-pointer {{ $editingId == $campaign->id ? 'bg-cyan-50 border-cyan-200' : 'bg-slate-50 border-transparent hover:border-slate-200' }}">
                            <div class="flex justify-between items-start mb-1">
                                <span
                                    class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-tighter
                                    {{ $campaign->status === 'Sent' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }}">
                                    {{ $campaign->status }}
                                </span>
                                <p class="text-[10px] font-bold text-slate-400 italic">
                                    {{ $campaign->created_at->format('d M') }}
                                </p>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm truncate">{{ $campaign->subject }}</h4>
                            <div class="flex items-center gap-2 mt-3">
                                <span class="text-[10px] text-slate-500 font-medium">
                                    <i class="fa fa-users mr-1"></i> {{ $campaign->recipients }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 opacity-50">
                            <i class="fa fa-inbox text-3xl mb-2"></i>
                            <p class="text-xs">No campaigns yet</p>
                        </div>
                    @endforelse
                </div>
                <div class="mt-6">{{ $campaigns->links() }}</div>
            </div>
        </div>

        {{-- Right: Editor / View Details --}}
        <div class="lg:col-span-7 xl:col-span-8">
            @if ($showForm)
                <div
                    class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden animate-fade-in-up">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="font-black text-slate-800 uppercase tracking-widest text-xs">
                            {{ $editingId ? 'Edit Draft' : 'New Campaign' }}
                        </h3>
                        <button wire:click="$set('showForm', false)"
                            class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Email
                                    Subject</label>
                                <input type="text" wire:model.defer="subject"
                                    class="w-full bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-cyan-500 text-sm py-3">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Banner Title</label>
                                <input type="text" wire:model.defer="title"
                                    class="w-full bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-cyan-500 text-sm py-3">
                            </div>
                        </div>

                        <div class="space-y-1" wire:ignore>
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Primary Message</label>
                            <p class="text-[9px] text-cyan-600 ml-2 mb-1 italic text-bold">Tip: Use {name} to
                                personalize the recipient's name.</p>
                            <input id="body" type="hidden" name="content" value="{{ $body }}">
                            <trix-editor input="body"
                                class="trix-content w-full bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-cyan-500 text-sm p-4 min-h-[300px]"
                                x-data x-on:trix-change="$dispatch('input', $event.target.value)"
                                wire:model.debounce.500ms="body">
                            </trix-editor>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Target Group</label>
                                <select wire:model.defer="recipients"
                                    class="w-full bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-cyan-500 text-sm py-3">
                                    <option value="All">All Subscribed Users</option>
                                    <option value="Clients">All Clients</option>
                                    <option value="Tutors">All Tutors</option>
                                    <option value="Admins">Admins</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Attachment</label>
                                <input type="file" wire:model="attachment" class="text-xs pt-2">
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4">
                            @if ($editingId && $status === 'Draft')
                                <button wire:click="saveDraft"
                                    class="flex-1 py-4 font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-2xl">Update
                                    Draft</button>
                                <button wire:click="send"
                                    class="flex-1 py-4 font-bold text-white bg-cyan-600 hover:bg-cyan-700 rounded-2xl shadow-lg shadow-cyan-100 flex items-center justify-center gap-2">
                                    <i class="fa fa-paper-plane"></i> Send Now
                                </button>
                            @else
                                <button wire:click="saveDraft"
                                    class="flex-1 py-4 font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-2xl">Save
                                    Draft</button>
                                <button wire:click="send"
                                    class="flex-1 py-4 font-bold text-white bg-cyan-600 hover:bg-cyan-700 rounded-2xl shadow-lg shadow-cyan-100">Send
                                    Campaign</button>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif($selectedCampaign)
                {{-- View Mode for Sent Campaigns --}}
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-8 bg-slate-800 text-white">
                        <div class="flex justify-between items-start">
                            <div>
                                <span
                                    class="text-cyan-400 text-[10px] font-black uppercase tracking-widest">{{ $selectedCampaign->status }}</span>
                                <h2 class="text-3xl font-black mt-1">{{ $selectedCampaign->subject }}</h2>
                                <p class="text-slate-400 text-sm mt-2">Sent to <span
                                        class="text-white">{{ $selectedCampaign->recipients }}</span> on
                                    {{ $selectedCampaign->sent_at?->format('F d, Y @ H:i') }}</p>
                            </div>
                            @if ($selectedCampaign->status === 'Sent')
                                <button wire:click="resend({{ $selectedCampaign->id }})"
                                    class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl text-xs font-bold transition-all">
                                    <i class="fa fa-rotate-right mr-1"></i> Resend
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-2 space-y-6">
                            <div>
                                <h5 class="text-[10px] font-black text-slate-400 uppercase mb-2">Message Body</h5>
                                <div class="prose prose-slate max-w-none text-slate-600 bg-slate-50 p-6 rounded-2xl">
                                    <h3 class="text-slate-800 font-bold mb-2">{{ $selectedCampaign->title }}</h3>
                                    {!! str_replace(
                                        ['<ul>', '<ol>'],
                                        [
                                            '<ul style="padding-left:20px; margin:10px 0; list-style-type: square;">',
                                            '<ol style="padding-left:20px; margin:10px 0; list-style-type: decimal;">',
                                        ],
                                        $selectedCampaign->body,
                                    ) !!}
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <h5 class="text-[10px] font-black text-slate-400 uppercase mb-3">Delivery Stats</h5>
                                <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-100">
                                    <div class="text-2xl font-black text-emerald-700">
                                        {{ count($selectedCampaign->sent_to ?? []) }}</div>
                                    <div class="text-[10px] font-bold text-emerald-600 uppercase">Emails Sent</div>
                                </div>
                            </div>

                            @if ($selectedCampaign->attachments)
                                <div>
                                    <h5 class="text-[10px] font-black text-slate-400 uppercase mb-3">Attachment</h5>
                                    <a href="{{ asset('storage/' . $selectedCampaign->attachments) }}"
                                        target="_blank"
                                        class="flex items-center gap-2 p-3 bg-slate-100 rounded-xl text-xs font-bold text-slate-600">
                                        <i class="fa fa-file-pdf text-red-500"></i> View Attached File
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div
                    class="h-full min-h-[400px] flex flex-col items-center justify-center text-center p-8 bg-white rounded-[2.5rem] border border-dashed border-slate-200">
                    <div
                        class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300">
                        <i class="fa fa-mouse-pointer text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-slate-800">No Campaign Selected</h3>
                    <p class="text-sm text-slate-500 max-w-xs mx-auto mt-1">Select a campaign from the list to view
                        details or create a new one to get started.</p>
                </div>
            @endif
        </div>
    </div>
</div>
