<section class="bg-slate-50 py-12 sm:py-16">
    <div class="mx-auto max-w-3xl px-4">
        @if ($submitted)
            <div class="rounded-3xl bg-white p-8 text-center shadow-sm">
                <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-full bg-emerald-100 text-2xl text-emerald-700">✓</div>
                <h1 class="text-3xl font-black text-slate-900">Thank you for getting in touch</h1>
                <p class="mt-3 text-slate-600">Your enquiry has been received. Our team will review it and contact you using the details you provided.</p>
                <a href="{{ route('welcome') }}" class="mt-6 inline-block rounded-xl bg-cyan-700 px-5 py-3 font-bold text-white">Back to MephEd</a>
            </div>
        @else
            <div class="mb-8">
                <p class="text-sm font-bold uppercase tracking-wide text-cyan-700">Church Management System</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">Tell us about your church</h1>
                <p class="mt-3 text-slate-600">Share a few details and our team will follow up about the Mephed Church Management System.</p>
            </div>
            @error('form') <div class="mb-4 rounded-xl bg-rose-50 p-3 text-rose-800">{{ $message }}</div> @enderror
            <form wire:submit="submit" class="space-y-6 rounded-3xl bg-white p-5 shadow-sm sm:p-8">
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ([['church_name','Church name',true],['contact_name','Your name',true],['contact_role','Your role or relationship to the church',true],['email','Email address',true],['phone','Phone number with country code',false],['country_region','Country or region',true]] as [$field,$label,$required])
                        <label class="block text-sm font-semibold text-slate-700">{{ $label }}{{ $required ? ' *' : '' }}
                            <input @if($field==='email') type="email" @else type="text" @endif wire:model="{{ $field }}" maxlength="{{ $field==='church_name' ? 160 : ($field==='contact_name' ? 120 : 190) }}" class="mt-1 w-full rounded-xl border-slate-300" autocomplete="{{ $field==='email' ? 'email' : 'off' }}">
                            @error($field) <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                        </label>
                    @endforeach
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-semibold text-slate-700">Approximate congregation size
                        <select wire:model="congregation_size" class="mt-1 w-full rounded-xl border-slate-300"><option value="">Choose if you wish</option>@foreach(['Under 100','100–500','500–2,000','More than 2,000','Prefer not to say'] as $option)<option>{{ $option }}</option>@endforeach</select>
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">How are records managed today?
                        <select wire:model="current_records_method" class="mt-1 w-full rounded-xl border-slate-300"><option value="">Choose if you wish</option>@foreach(['Paper','Spreadsheets','Another system','Other','Prefer not to say'] as $option)<option>{{ $option }}</option>@endforeach</select>
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">Expected timeframe
                        <select wire:model="timeframe" class="mt-1 w-full rounded-xl border-slate-300"><option value="">Choose if you wish</option>@foreach(['Exploring','Within 3 months','Within 6 months','Later'] as $option)<option>{{ $option }}</option>@endforeach</select>
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">Preferred contact method
                        <select wire:model="preferred_contact_method" class="mt-1 w-full rounded-xl border-slate-300"><option value="">Choose if you wish</option><option>Email</option><option>Phone</option></select>
                    </label>
                </div>
                <fieldset>
                    <legend class="text-sm font-semibold text-slate-700">Areas of interest <span class="font-normal text-slate-500">(optional)</span></legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">@foreach(['Members and households','Attendance and services','Units or groups','Care and follow-up','Finance','Communications','Other'] as $interest)<label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" value="{{ $interest }}" wire:model="interests" class="rounded border-slate-300 text-cyan-700">{{ $interest }}</label>@endforeach</div>
                </fieldset>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-semibold text-slate-700">How did you hear about Mephed?
                        <input type="text" wire:model="source" maxlength="160" class="mt-1 w-full rounded-xl border-slate-300">
                    </label>
                    <div class="hidden" aria-hidden="true"><label>Leave this field empty<input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label></div>
                </div>
                <label class="block text-sm font-semibold text-slate-700">Your enquiry <span class="font-normal text-slate-500">(optional, up to 2,000 characters)</span>
                    <textarea wire:model="enquiry" maxlength="2000" rows="4" class="mt-1 w-full rounded-xl border-slate-300" placeholder="Please do not include passwords, member lists, pastoral information, or financial records."></textarea>
                    @error('enquiry') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                </label>
                <div class="space-y-3 border-t border-slate-100 pt-5">
                    <label class="flex items-start gap-3 text-sm text-slate-700"><input type="checkbox" wire:model="contact_consent" class="mt-1 rounded border-slate-300 text-cyan-700"><span><strong>Required:</strong> MephEd may use my information to respond to this enquiry. I understand authorized MephEd administrators can access it and I can request correction or deletion through the <a class="text-cyan-800 underline" href="{{ route('contact') }}">contact page</a>. <span class="text-rose-600">*</span>@error('contact_consent') <span class="block text-xs text-rose-600">Please agree before submitting.</span> @enderror</span></label>
                    <label class="flex items-start gap-3 text-sm text-slate-700"><input type="checkbox" wire:model="marketing_consent" class="mt-1 rounded border-slate-300 text-cyan-700"><span>Optional: I would also like to receive occasional product updates. This is separate from my enquiry.</span></label>
                </div>
                <p class="text-xs leading-5 text-slate-500">We collect this information to respond to and manage your enquiry. It is available to authorized MephEd administrators. Do not submit church member records or sensitive church information. Read our <a class="underline" href="{{ route('privacy-policy') }}">privacy policy</a>.</p>
                <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-cyan-700 px-6 py-3 font-bold text-white hover:bg-cyan-800 disabled:opacity-60">Send enquiry</button>
            </form>
        @endif
    </div>
</section>
