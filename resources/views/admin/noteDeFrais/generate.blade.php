<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients - DailyTrac</title> 
    @vite('resources/css/app.css')
    @livewireStyles
    
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        <!-- Main Content -->
        <div class="p-8 animate-fadeInSlideUp">
            <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-xl transition-all duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">New Expense Note</h2>

                @if(session('message'))
                    <div class="mb-4 p-4 rounded-lg bg-green-50 text-green-700 border border-green-200">
                        {{ session('message') }}
                    </div>
                @endif

                {{-- Dynamic form action based on role --}}
                <form id="devisForm" method="POST"
                    action="{{ 
                        route(
                            Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.notedefrais.saveNoteDeFraisPdf' :
                            (Auth::user()->hasRole('Admin') ? 'admin.notedefrais.saveNoteDeFraisPdf' :
                            (Auth::user()->hasRole('Employee') ? 'employee.notedefrais.saveNoteDeFraisPdf' :
                            'Responsable_gestionnaire.notedefrais.saveNoteDeFraisPdf'))
                        ) 
                    }}"
                    autocomplete="off"
                    class="space-y-6">

                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Employee Name <span class="text-red-500">*</span></label>
                            <input type="text" id="employee_name" name="employee_name" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Total Budget</label>
                            <input type="number" step="0.0001" id="projectBudget" name="budget" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Description <span class="text-red-500">*</span></label>
                        <input type="text" id="projectDescription" name="description" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none">
                    </div>

                    <div id="dynamicInput" class="space-y-6 mt-6">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Details <span class="text-red-500">*</span></label>
                                <input type="text" name="task[]" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Quantity <span class="text-red-500">*</span></label>
                                <input type="number" step="0.0001" name="quantity[]" class="taskQuantity w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Budget <span class="text-red-500">*</span></label>
                                <input type="number" step="0.0001" name="taskBudget[]" class="taskBudget w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg" onclick="addInput('dynamicInput');">Add</button>
                        <button type="button" class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium px-4 py-2 rounded-lg" onclick="removeInput('dynamicInput');">Delete</button>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                        <a href="{{ 
                            route(
                                Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.index' :
                                (Auth::user()->hasRole('Admin') ? 'admin.index' :
                                (Auth::user()->hasRole('Employee') ? 'employee.index' :
                                'Responsable_gestionnaire.index'))
                            )
                        }}" 
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">Cancel</a>

                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                            Generate Expense Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // --- THIS IS THE NEW CODE YOU NEED ---
        // Run this script when the page is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Find all *initial* inputs and attach the calculator function
            // This makes the first row work.
            document.querySelectorAll('.taskBudget, .taskQuantity').forEach(input => {
                input.addEventListener('input', updateProjectBudget);
            });
        });
        // --- END OF NEW CODE ---


        function addInput(divName) {
            var newdiv = document.createElement('div');
            newdiv.classList.add('grid', 'grid-cols-1', 'sm:grid-cols-3', 'gap-6');
            newdiv.innerHTML = `
                <div><input type="text" name="task[]" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none"></div>
                <div><input type="number" step="0.0001" name="quantity[]" class="taskQuantity w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none"></div>
                <div><input type="number" step="0.0001" name="taskBudget[]" class="taskBudget w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none"></div>
            `;
            document.getElementById(divName).appendChild(newdiv);
            
            // This part was already correct: it attaches listeners to *new* inputs
            newdiv.querySelectorAll('.taskBudget, .taskQuantity').forEach(input => input.addEventListener('input', updateProjectBudget));
        }

        function removeInput(divName) {
            const div = document.getElementById(divName);
            if (div.children.length > 1) {
                div.removeChild(div.lastChild);
                updateProjectBudget(); // Recalculate after removing
            }
        }

        // Your updateProjectBudget function is already correct for your request.
        // It multiplies budget by quantity for each row.
        function updateProjectBudget() {
            let total = 0;
            document.querySelectorAll('.taskBudget').forEach((b, i) => {
                const budget = parseFloat(b.value) || 0;
                // Find the corresponding quantity input by its index
                const qty = parseFloat(document.querySelectorAll('.taskQuantity')[i].value) || 0;
                
                // Add the (budget * quantity) to the total
                total += budget * qty;
            });
            document.getElementById('projectBudget').value = total.toFixed(2);
        }
    </script>
    

</body>
</html>
