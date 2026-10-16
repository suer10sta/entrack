<!DOCTYPE html>
<html>
<head>
    <x-favicon />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Billit API Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h1 class="text-2xl font-bold mb-6">Billit API Test</h1>
                
                <div class="mb-6">
                    <button id="testBillit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Send Test Invoice
                    </button>
                </div>

                <div id="result" class="hidden mt-4 p-4 rounded">
                    <h2 class="text-lg font-semibold mb-2">Response:</h2>
                    <pre id="responseData" class="bg-gray-100 p-4 rounded overflow-x-auto"></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('testBillit').addEventListener('click', async function() {
        try {
            const response = await fetch('/api/billit/test-invoice', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            
            const resultDiv = document.getElementById('result');
            const responseData = document.getElementById('responseData');
            
            resultDiv.classList.remove('hidden');
            resultDiv.classList.remove('bg-red-100', 'bg-green-100');
            
            if (data.success) {
                resultDiv.classList.add('bg-green-100');
                responseData.textContent = JSON.stringify(data.data, null, 2);
            } else {
                resultDiv.classList.add('bg-red-100');
                responseData.textContent = data.message;
            }
        } catch (error) {
            const resultDiv = document.getElementById('result');
            const responseData = document.getElementById('responseData');
            
            resultDiv.classList.remove('hidden');
            resultDiv.classList.add('bg-red-100');
            responseData.textContent = 'Error: ' + error.message;
        }
    });
    </script>
</body>
</html>

