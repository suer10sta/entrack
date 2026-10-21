                                <p class="text-[11px] font-bold uppercase tracking-[0.07em] text-slate-400 mb-2">{{ __('messages.Personal Infos') }}</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Name') }} <span class="text-red-500">*</span></label>
                                        <input form="create-client-form" type="text" name="name" required placeholder="{{ __('messages.Full Name') }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Client Number') }}</label>
                                        <input form="create-client-form" type="text" name="client_number" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Email') }}</label>
                                        <input form="create-client-form" type="email" name="email" placeholder="*****@gmail.com" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Phone') }}</label>
                                        <input form="create-client-form" type="text" name="phone" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                </div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.07em] text-slate-400 mt-4 mb-2">{{ __('messages.Address Infos') }}</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="sm:col-span-2">
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Street') }} <span class="text-red-500">*</span></label>
                                        <input form="create-client-form" type="text" name="address" required placeholder="{{ __('messages.Address') }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.City') }} <span class="text-red-500">*</span></label>
                                        <input form="create-client-form" type="text" name="city" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Zip Code') }} <span class="text-red-500">*</span></label>
                                        <input form="create-client-form" type="text" name="zip" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Country') }} <span class="text-red-500">*</span></label>
                                        <input form="create-client-form" type="text" name="country" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Due Date (days)') }} <span class="text-red-500">*</span></label>
                                        <input form="create-client-form" type="text" name="due_date" required placeholder="{{ __('messages.Due Date by Days') }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.TVA number') }}</label>
                                        <input form="create-client-form" type="text" name="TVA" value="0" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                </div>
                                <div class="flex items-center mt-3">
                                    <input form="create-client-form" id="ic-billing-same" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25">
                                    <label for="ic-billing-same" class="ml-2 text-[12.5px] font-medium text-slate-700">{{ __('messages.Billing address is the same as client address') }}</label>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Billing Street') }}</label>
                                        <input form="create-client-form" type="text" name="billing_street" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Billing City') }}</label>
                                        <input form="create-client-form" type="text" name="billing_city" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Billing Zip Code') }}</label>
                                        <input form="create-client-form" type="text" name="billing_zip" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Billing Country') }}</label>
                                        <input form="create-client-form" type="text" name="billing_country" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                </div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.07em] text-slate-400 mt-4 mb-2">{{ __('messages.Accounting Info') }}</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Account Manager Name') }}</label>
                                        <input form="create-client-form" type="text" name="comp_name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Email') }}</label>
                                        <input form="create-client-form" type="text" name="comp_email" placeholder="*****@gmail.com" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Phone Number') }}</label>
                                        <input form="create-client-form" type="text" name="comp_phone" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                    <div>
                                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Address') }}</label>
                                        <input form="create-client-form" type="text" name="comp_address" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                    </div>
                                </div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.07em] text-slate-400 mt-4 mb-2">{{ __('messages.Contacts Infos') }}</p>
                                <div id="ic-contacts" class="space-y-3">
                                    <div class="contact-entry space-y-3 rounded-lg border border-slate-200 bg-white p-3">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Full Name') }}</label>
                                                <input form="create-client-form" type="text" name="contact_name[]" placeholder="{{ __('messages.Full Name') }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                            </div>
                                            <div>
                                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Function') }}</label>
                                                <input form="create-client-form" type="text" name="contact_function[]" placeholder="{{ __('messages.Functionality') }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                            </div>
                                            <div>
                                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Phone Number') }}</label>
                                                <input form="create-client-form" type="text" name="contact_mobile_fix[]" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                            </div>
                                            <div>
                                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Email') }}</label>
                                                <input form="create-client-form" type="text" name="contact_email[]" placeholder="*****@gmail.com" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Address') }}</label>
                                                <input form="create-client-form" type="text" name="contact_address[]" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="ic-add-contact" class="mt-2 w-full rounded-lg border border-dashed border-slate-300 px-3 py-2 text-[12.5px] font-semibold text-slate-500 hover:border-[#2D7080] hover:text-[#2D7080] transition-colors">+ {{ __('messages.Contacts Infos') }}</button>
