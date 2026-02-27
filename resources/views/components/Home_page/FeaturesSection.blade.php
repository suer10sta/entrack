<main class="bg-gradient-to-b from-background to-primary/5">

    {{-- Features Overview Section --}}
    <section id="features" class="py-16 lg:py-24">
        <div class="container mx-auto px-6">
            <div class="scroll-fade-in mx-auto max-w-3xl text-center mb-16">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    {{ __('messages.Comprehensive Features') }}
                </div>
                
                <h2 class="text-4xl font-bold text-foreground lg:text-5xl mb-4">
                    {{ __('messages.Everything You Need to Run Your Business') }}
                </h2>
                <p class="text-lg leading-relaxed text-muted-foreground">
                    {{ __('messages.From invoicing to project management, time tracking to team collaboration—all the tools you need in one integrated platform.') }}
                </p>
            </div>

            {{-- Main Features Grid --}}
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                
                {{-- Time Tracking Feature --}}
                <div class="scroll-fade-in group relative overflow-hidden rounded-2xl border border-border bg-card p-8 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 cursor-pointer" data-scroll-delay="0" onclick="openVideoModal('{{ asset('videos/employeesTimerDemo.mp4') }}')">
                    <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-red-500 text-white shadow-lg transition-transform group-hover:scale-110">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-foreground">{{ __('messages.Chrono-Based Time Tracking') }}</h3>
                    <p class="mb-4 text-sm leading-relaxed text-muted-foreground">
                        {{ __('messages.Give your teams a simple start/stop chrono on every project and task while DalyTrac does the heavy lifting in the background—turning raw minutes into clean timesheets, billable hours, and profitability insights.') }}
                    </p>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Real-time chrono with start/stop on each project and task') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Automatic timesheets by employee, project, and client') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Accurate data for billing, payroll, and project profitability') }}</span>
                        </li>
                    </ul>
                </div>

                {{-- Invoicing Feature --}}
                <div class="scroll-fade-in group relative overflow-hidden rounded-2xl border border-border bg-card p-8 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 cursor-pointer" data-scroll-delay="100" onclick="openVideoModal('{{ asset('videos/InvoiceDemo.mp4') }}')">
                    <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-400 text-white shadow-lg transition-transform group-hover:scale-110">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-foreground">{{ __('messages.Professional Invoicing') }}</h3>
                    <p class="mb-4 text-sm leading-relaxed text-muted-foreground">
                        {{ __('messages.Create, send, and track professional invoices through multiple channels. Support for both traditional email invoicing and modern E-invoicing via PEPPOL network. Generate quotes (devis), manage credit notes, and automate payment reminders.') }}
                    </p>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Email invoicing with PDF export') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.E-invoicing via PEPPOL network') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Automated payment reminders') }}</span>
                        </li>
                    </ul>
                </div>
				
                {{-- Client Management Feature --}}
                <div class="scroll-fade-in group relative overflow-hidden rounded-2xl border border-border bg-card p-8 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 cursor-pointer" data-scroll-delay="200" onclick="openVideoModal('{{ asset('videos/ClientDemo.mp4') }}')">
                    <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white shadow-lg transition-transform group-hover:scale-110">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-foreground">{{ __('messages.Client Management') }}</h3>
                    <p class="mb-4 text-sm leading-relaxed text-muted-foreground">
                        {{ __('messages.Centralize all client information, communication history, and documents. Track relationships, manage contacts, and access complete client profiles.') }}
                    </p>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Complete client profiles') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Transaction history tracking') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Document management') }}</span>
                        </li>
                    </ul>
                </div>

                {{-- Project Management Feature --}}
                <div class="scroll-fade-in group relative overflow-hidden rounded-2xl border border-border bg-card p-8 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 cursor-pointer" data-scroll-delay="300" onclick="openVideoModal('{{ asset('videos/Projects_TasksDemo.mp4') }}')">
                    <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-400 text-white shadow-lg transition-transform group-hover:scale-110">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-foreground">{{ __('messages.Project Management') }}</h3>
                    <p class="mb-4 text-sm leading-relaxed text-muted-foreground">
                        {{ __('messages.Organize projects from start to finish. Create subprojects, assign teams, track progress, and manage budgets all in one place.') }}
                    </p>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Project hierarchy & subprojects') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Team assignment & collaboration') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Budget tracking & reporting') }}</span>
                        </li>
                    </ul>
                </div>

                {{-- Task Management Feature --}}
                <div class="scroll-fade-in group relative overflow-hidden rounded-2xl border border-border bg-card p-8 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 cursor-pointer" data-scroll-delay="400" onclick="openVideoModal('{{ asset('videos/Projects_TasksDemo.mp4') }}')">
                    <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 text-white shadow-lg transition-transform group-hover:scale-110">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-foreground">{{ __('messages.Task Management') }}</h3>
                    <p class="mb-4 text-sm leading-relaxed text-muted-foreground">
                        {{ __('messages.Create, assign, and track tasks across projects. Set deadlines, priorities, and status updates for seamless workflow management.') }}
                    </p>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Task assignment & delegation') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Priority levels & deadlines') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Status tracking & notifications') }}</span>
                        </li>
                    </ul>
                </div>

                {{-- Team Collaboration Feature --}}
                <div class="scroll-fade-in group relative overflow-hidden rounded-2xl border border-border bg-card p-8 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 cursor-pointer" data-scroll-delay="500" onclick="openVideoModal('{{ asset('videos/PlanningDemo.mp4') }}')">
                    <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-pink-500 to-rose-500 text-white shadow-lg transition-transform group-hover:scale-110">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-foreground">{{ __('messages.Team Collaboration') }}</h3>
                    <p class="mb-4 text-sm leading-relaxed text-muted-foreground">
                        {{ __('messages.Build and manage teams, assign members to projects, and foster collaboration with role-based permissions and access control.') }}
                    </p>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Team creation & management') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Role-based permissions') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Project team assignments') }}</span>
                        </li>
                    </ul>
                </div>

                {{-- Employee Management Feature --}}
                <div class="scroll-fade-in group relative overflow-hidden rounded-2xl border border-border bg-card p-8 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 cursor-pointer" data-scroll-delay="0" onclick="openVideoModal('{{ asset('videos/EmployeesDemo.mp4') }}')">
                    <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-yellow-500 text-white shadow-lg transition-transform group-hover:scale-110">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-foreground">{{ __('messages.Employee Management') }}</h3>
                    <p class="mb-4 text-sm leading-relaxed text-muted-foreground">
                        {{ __('messages.Manage your workforce efficiently. Track employee information, work hours, attendance, and generate comprehensive reports.') }}
                    </p>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Employee profiles & records') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Attendance & work day reports') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Performance analytics') }}</span>
                        </li>
                    </ul>
                </div>

                {{-- Calendar & Events Feature --}}
                <div class="scroll-fade-in group relative overflow-hidden rounded-2xl border border-border bg-card p-8 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 cursor-pointer" data-scroll-delay="100" onclick="openVideoModal('{{ asset('videos/PlanningDemo.mp4') }}')">
                    <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 text-white shadow-lg transition-transform group-hover:scale-110">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-foreground">{{ __('messages.Calendar & Events') }}</h3>
                    <p class="mb-4 text-sm leading-relaxed text-muted-foreground">
                        {{ __('messages.Schedule events, meetings, and deadlines. Get reminders, view daily/monthly calendars, and never miss an important date.') }}
                    </p>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Interactive calendar view') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Event reminders & notifications') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.PDF export & email sharing') }}</span>
                        </li>
                    </ul>
                </div>

                {{-- Reports & Analytics Feature --}}
                <div class="scroll-fade-in group relative overflow-hidden rounded-2xl border border-border bg-card p-8 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 cursor-pointer" data-scroll-delay="200" onclick="openVideoModal('{{ asset('videos/DashboardDemo.mp4') }}')">
                    <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-blue-500 text-white shadow-lg transition-transform group-hover:scale-110">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-foreground">{{ __('messages.Reports & Analytics') }}</h3>
                    <p class="mb-4 text-sm leading-relaxed text-muted-foreground">
                        {{ __('messages.Generate comprehensive reports on invoices, projects, time tracking, and expenses. Export to PDF and analyze your business performance.') }}
                    </p>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Custom report generation') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.PDF export & email delivery') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Real-time analytics dashboard') }}</span>
                        </li>
                    </ul>
                </div>

                {{-- Expense Management Feature --}}
                <div class="scroll-fade-in group relative overflow-hidden rounded-2xl border border-border bg-card p-8 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 cursor-pointer" data-scroll-delay="300" onclick="openVideoModal('{{ asset('videos/ExpenseDemo.mp4') }}')">
                    <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-lime-500 to-green-500 text-white shadow-lg transition-transform group-hover:scale-110">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 0a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-foreground">{{ __('messages.Expense Management') }}</h3>
                    <p class="mb-4 text-sm leading-relaxed text-muted-foreground">
                        {{ __('messages.Track business expenses, create expense notes (note de frais), and manage reimbursements with detailed categorization and reporting.') }}
                    </p>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Expense note creation') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Category management') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('messages.Expense reports & analytics') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- How It Works Section with Screenshots --}}
    <section id="how-it-works" class="py-16 lg:py-24 bg-gradient-to-b from-primary/5 to-background">
        <div class="container mx-auto px-6">
            <div class="scroll-fade-in mx-auto max-w-3xl text-center mb-16">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{ __('messages.See It In Action') }}
                </div>
                
                <h2 class="text-4xl font-bold text-foreground lg:text-5xl mb-4">
                    {{ __('messages.How DalyTrac Works') }}
                    </h2>
                    <p class="text-lg leading-relaxed text-muted-foreground">
                    {{ __('messages.Get a glimpse of our intuitive interface and powerful features in action. See how easy it is to manage your business.') }}
                </p>
            </div>

            {{-- Feature Showcase with Mockups --}}
            <div class="space-y-24">
                
                {{-- Dashboard Showcase --}}
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div class="scroll-slide-left space-y-6 order-2 lg:order-1">
                        <div class="inline-flex items-center gap-2 rounded-lg bg-primary/10 px-3 py-1.5 text-sm font-semibold text-primary">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            {{ __('messages.Dashboard Overview') }}
                        </div>
                        <h3 class="text-3xl font-bold text-foreground">{{ __('messages.Your Business at a Glance') }}</h3>
                        <p class="text-lg leading-relaxed text-muted-foreground">
                            {{ __('messages.Start your day with a comprehensive dashboard showing key metrics, recent activity, active projects, and upcoming deadlines. Everything you need to know in one place.') }}
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-1 h-5 w-5 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-foreground">{{ __('messages.Real-time revenue and invoice statistics') }}</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-1 h-5 w-5 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-foreground">{{ __('messages.Active projects and task overview') }}</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-1 h-5 w-5 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-foreground">{{ __('messages.Quick access to all modules') }}</span>
                        </li>
                        </ul>
                    </div>
                    <div class="relative order-1 lg:order-2">
                        <div class="rounded-2xl border-8 border-border bg-card p-4 shadow-2xl overflow-hidden cursor-pointer group" onclick="openVideoModal('{{ asset('videos/DashboardDemo.mp4') }}')">
                            <video 
                                id="dashboard-video-preview"
                                autoplay 
                                loop 
                                muted 
                                playsinline
                                class="w-full h-auto rounded-lg transition-transform group-hover:scale-105"
                                style="display: block;"
                            >
                                <source src="{{ asset('videos/DashboardDemo.mp4') }}" type="video/mp4">
                                {{ __('messages.Your browser does not support the video tag.') }}
                            </video>
                            <div class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/20 transition-colors rounded-lg">
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-16 h-16 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -bottom-4 -right-4 h-24 w-24 rounded-full bg-primary/10 blur-2xl"></div>
                    </div>
                </div>

                {{-- Invoice Management Showcase --}}
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div class="scroll-slide-right relative order-1 lg:order-1">
                        <div class="rounded-2xl border-8 border-border bg-card p-4 shadow-2xl overflow-hidden cursor-pointer group" onclick="openVideoModal('{{ asset('videos/InvoiceDemo.mp4') }}')">
                            <video 
                                id="invoice-video-preview"
                                autoplay 
                                loop 
                                muted 
                                playsinline
                                class="w-full h-auto rounded-lg transition-transform group-hover:scale-105"
                                style="display: block;"
                            >
                                <source src="{{ asset('videos/InvoiceDemo.mp4') }}" type="video/mp4"> 
                                {{ __('messages.Your browser does not support the video tag.') }}
                            </video>
                            <div class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/20 transition-colors rounded-lg">
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-16 h-16 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -top-4 -left-4 h-24 w-24 rounded-full bg-blue-500/10 blur-2xl"></div>
                    </div>
                    <div class="scroll-slide-left space-y-6 order-2 lg:order-2">
                        <div class="inline-flex items-center gap-2 rounded-lg bg-primary/10 px-3 py-1.5 text-sm font-semibold text-primary">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ __('messages.Invoice Management') }}
                        </div>
                        <h3 class="text-3xl font-bold text-foreground">{{ __('messages.Streamlined Invoicing Workflow') }}</h3>
                        <p class="text-lg leading-relaxed text-muted-foreground">
                            {{ __('messages.Create professional invoices in seconds. Track payment status, send automated reminders, and manage your entire billing cycle from one intuitive interface.') }}
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-1 h-5 w-5 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-foreground">{{ __('messages.One-click invoice generation with customizable templates') }}</span>
                        </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-1 h-5 w-5 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-foreground">{{ __('messages.Real-time payment status tracking') }}</span>
                        </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-1 h-5 w-5 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-foreground">{{ __('messages.Automated email delivery and PDF export') }}</span>
                        </li>
                    </ul>
                    </div>
                </div>

                {{-- Project & Time Tracking Showcase --}}
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div class="scroll-slide-left space-y-6 order-2 lg:order-1">
                        <div class="inline-flex items-center gap-2 rounded-lg bg-primary/10 px-3 py-1.5 text-sm font-semibold text-primary">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('messages.Project & Time Tracking') }}
                        </div>
                        <h3 class="text-3xl font-bold text-foreground">{{ __('messages.Track Every Minute, Manage Every Project') }}</h3>
                        <p class="text-lg leading-relaxed text-muted-foreground">
                            {{ __('messages.Monitor project progress in real-time, track time spent on tasks, and ensure your team stays on schedule. Get detailed insights into productivity and project health.') }}
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-1 h-5 w-5 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-foreground">{{ __('messages.Real-time timer with start and stop controls') }}</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-1 h-5 w-5 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-foreground">{{ __('messages.Visual project progress indicators') }}</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-1 h-5 w-5 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-foreground">{{ __('messages.Comprehensive time reports by project, task, and employee') }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="scroll-slide-right relative order-1 lg:order-2">
                        <div class="rounded-2xl border-8 border-border bg-card p-4 shadow-2xl overflow-hidden cursor-pointer group" onclick="openVideoModal('{{ asset('videos/TimeTrackerDemo.mp4') }}')">
                            <video 
                                id="tracktimer-video-preview"
                                autoplay 
                                loop 
                                muted 
                                playsinline
                                class="w-full h-auto rounded-lg transition-transform group-hover:scale-105"
                                style="display: block;"
                            >
                                <source src="{{ asset('videos/TimeTrackerDemo.mp4') }}" type="video/mp4"> 
                                {{ __('messages.Your browser does not support the video tag.') }}
                            </video>
                            <div class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/20 transition-colors rounded-lg">
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-16 h-16 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                            </div>
                            </div>
                        </div>
                        <div class="absolute -bottom-4 -right-4 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl"></div>
                    </div>
                </div>

                {{-- How It Works CTA --}}
                <div class="scroll-fade-in text-center" data-scroll-delay="200">
                    <a 
                        href="{{ route('demo') }}" 
                        class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-red-500/80 bg-white/95 px-6 py-3 text-sm font-semibold text-foreground shadow-[0_10px_30px_rgba(0,0,0,0.25)] transition-all duration-300 hover:shadow-[0_18px_45px_rgba(0,0,0,0.35)] hover:-translate-y-0.5 hover:bg-white"
                    >
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="11" fill="#FF0000" stroke="#CC0000" stroke-width="1"/>
                            <path d="M9.5 7.5L16.5 12L9.5 16.5V7.5Z" fill="white"/>
                        </svg>
                        <span>{{ __('messages.Show All Features in Demo') }}</span>
                    </a>
                </div>

            </div>
        </div>
    </section>

    {{-- Benefits Section --}}
    <section class="py-16 lg:py-24 bg-gradient-to-b from-background to-primary/5">
        <div class="container mx-auto px-6">
            <div class="mx-auto max-w-4xl">
                <div class="scroll-fade-in mx-auto max-w-2xl text-center mb-12">
                    <h2 class="text-4xl font-bold text-foreground lg:text-5xl mb-4">
                        {{ __('messages.Why Choose DalyTrac?') }}
                    </h2>
                    <p class="text-lg leading-relaxed text-muted-foreground">
                        {{ __('messages.Join thousands of businesses that trust DalyTrac to streamline their operations and boost productivity.') }}
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <div class="scroll-fade-in rounded-xl border border-border bg-card p-6 shadow-lg" data-scroll-delay="0">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-bold text-foreground">{{ __('messages.Save Time & Increase Efficiency') }}</h3>
                        <p class="text-muted-foreground">
                            {{ __('messages.Automate repetitive tasks, streamline workflows, and eliminate manual data entry. Focus on growing your business instead of managing paperwork.') }}
                        </p>
                    </div>

                    <div class="scroll-fade-in rounded-xl border border-border bg-card p-6 shadow-lg" data-scroll-delay="100">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-bold text-foreground">{{ __('messages.Secure & Reliable') }}</h3>
                        <p class="text-muted-foreground">
                            {{ __('messages.Your data is protected with enterprise-grade security. Regular backups, SSL encryption, and 99.9% uptime guarantee your business continuity.') }}
                        </p>
                    </div>

                    <div class="scroll-fade-in rounded-xl border border-border bg-card p-6 shadow-lg" data-scroll-delay="200">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-bold text-foreground">{{ __('messages.All-in-One Solution') }}</h3>
                        <p class="text-muted-foreground">
                            {{ __('messages.No need for multiple tools. DalyTrac combines invoicing, project management, time tracking, and team collaboration in one integrated platform.') }}
                        </p>
                    </div>

                    <div class="scroll-fade-in rounded-xl border border-border bg-card p-6 shadow-lg" data-scroll-delay="300">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-bold text-foreground">{{ __('messages.Mobile-Friendly') }}</h3>
                        <p class="text-muted-foreground">
                            {{ __('messages.Access your business data from anywhere, anytime. Our responsive design works seamlessly on desktop, tablet, and mobile devices.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    </main>

    {{-- Video Modal --}}
    <div id="video-modal" class="video-modal fixed inset-0 z-50 hidden items-center justify-center bg-black/90 backdrop-blur-sm">
        <div class="relative w-full h-full flex items-center justify-center p-4">
            <button 
                onclick="closeVideoModal()" 
                class="absolute top-4 right-4 z-10 text-white hover:text-primary transition-colors p-2 rounded-full hover:bg-white/10"
                aria-label="{{ __('messages.Close video') }}"
            >
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="w-full max-w-7xl aspect-video">
                <video 
                    id="video-modal-player"
                    controls
                    autoplay
                    loop
                    class="w-full h-full rounded-lg shadow-2xl"
                >
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>
    </div>

{{-- Scroll Animation Styles and Script --}}
<style>
    /* Ensure SVG icons are visible */
    .mb-6 svg {
        display: block !important;
        width: 1.75rem !important;
        height: 1.75rem !important;
        color: white !important;
        stroke: currentColor !important;
    }
    
    /* Initial state for scroll animations */
    .scroll-fade-in {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }
    
    .scroll-fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    .scroll-slide-left {
        opacity: 0;
        transform: translateX(-50px);
        transition: opacity 0.8s ease, transform 0.8s ease;
    }
    
    .scroll-slide-left.visible {
        opacity: 1;
        transform: translateX(0);
    }
    
    .scroll-slide-right {
        opacity: 0;
        transform: translateX(50px);
        transition: opacity 0.8s ease, transform 0.8s ease;
    }
    
    .scroll-slide-right.visible {
        opacity: 1;
        transform: translateX(0);
    }
    
    /* Stagger delay support */
    .scroll-fade-in[data-scroll-delay] {
        transition-delay: calc(var(--scroll-delay, 0) * 1ms);
    }
    
    /* Video Modal Styles */
    .video-modal {
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .video-modal.show {
        opacity: 1;
    }
    
    .video-modal.show {
        display: flex !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    const delay = element.getAttribute('data-scroll-delay') || 0;
                    
                    setTimeout(() => {
                        element.classList.add('visible');
                    }, parseInt(delay));
                    
                    // Unobserve after animation to improve performance
                    observer.unobserve(element);
                }
            });
        }, observerOptions);
        
        // Observe all elements with scroll animation classes
        document.querySelectorAll('.scroll-fade-in, .scroll-slide-left, .scroll-slide-right').forEach(el => {
            observer.observe(el);
        });
    });
    
    // Video Modal Functions
    function openVideoModal(videoSrc) {
        const modal = document.getElementById('video-modal');
        const video = document.getElementById('video-modal-player');
        
        if (modal && video && videoSrc) {
            // Set the video source
            video.innerHTML = '<source src="' + videoSrc + '" type="video/mp4">';
            video.load();
            
            modal.classList.remove('hidden');
            // Trigger reflow to ensure transition works
            modal.offsetHeight;
            modal.classList.add('show');
            video.play();
            
            // Prevent body scroll when modal is open
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeVideoModal() {
        const modal = document.getElementById('video-modal');
        const video = document.getElementById('video-modal-player');
        
        if (modal && video) {
            modal.classList.remove('show');
            video.pause();
            video.currentTime = 0;
            
            // Restore body scroll
            document.body.style.overflow = '';
            
            // Hide modal after transition
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    }
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeVideoModal();
        }
    });
    
    // Close modal when clicking outside the video
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('video-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                // Get the video container element (the div containing the video)
                const videoContainer = modal.querySelector('.w-full.max-w-7xl');
                
                // Close if click is outside the video container
                // The close button has its own handler, so we don't need to exclude it
                if (videoContainer && !videoContainer.contains(e.target)) {
                    closeVideoModal();
                }
            });
        }
    });
</script>
