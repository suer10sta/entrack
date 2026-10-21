                                <div class="signup-feature-strip">
                                    <div class="signup-feature-strip-grid" role="list">
                                        @foreach([
                                            [__('messages.auth_register_card_projects_label'), __('messages.auth_register_card_projects_value'), 'M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z'],
                                            [__('messages.auth_register_card_planning_label'), __('messages.auth_register_card_planning_value'), 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm3 12 2 2 5-5'],
                                            [__('messages.auth_register_card_invoices_label'), __('messages.auth_register_card_invoices_value'), 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6ZM14 2v6h6M8 13h8M8 17h5'],
                                        ] as [$label, $value, $icon])
                                            <div class="signup-feature-strip-item" role="listitem">
                                                <svg class="signup-feature-strip-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}" /></svg>
                                                <span class="signup-feature-strip-title text-[0.7rem] font-bold uppercase tracking-[0.08em]">{{ $label }}</span>
                                                <span class="signup-feature-strip-value text-[13px] text-[#5b6b73]">{{ $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
