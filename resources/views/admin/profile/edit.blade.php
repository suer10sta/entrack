<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">

    <div class="container mx-auto max-w-4xl p-4 md:p-8">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edit Profile</h1>
            <p class="text-gray-600 mt-1">Update your personal and business information</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center space-x-6">
                <div class="w-20 h-20 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-3xl font-bold">JD</span>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Profile Picture</h2>
                    <p class="text-sm text-gray-500 mt-1">JPG, PNG or GIF. Max size 5MB</p>
                    <button class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M.5 8.5a.5.5 0 01.5-.5h2a.5.5 0 010 1H1a.5.5 0 01-.5-.5zM8 3a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L2 7.586V4a1 1 0 00-1-1H0a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V5.414l2.293 2.293a1 1 0 001.414 0L10 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L8 5.414V4a1 1 0 00-1-1H6a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V5.414l2.293 2.293a1 1 0 001.414 0L18 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L16 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L14 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L12 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L10 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L8 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L6 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1H0.5z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-10.25a.75.75 0 00-1.5 0v2.5a.75.75 0 001.5 0v-2.5z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10.75 8.5a.75.75 0 00-1.5 0v2a.75.75 0 001.5 0v-2zM9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10 18a8 8 0 100-16 8 8 0 000 16zM.5 8.5a.5.5 0 01.5-.5h2a.5.5 0 010 1H1a.5.5 0 01-.5-.5zM8 3a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L2 7.586V4a1 1 0 00-1-1H0a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V5.414l2.293 2.293a1 1 0 001.414 0L10 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L8 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L6 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1H0.5zM10 18a8 8 0 100-16 8 8 0 000 16zm.75-10.25a.75.75 0 00-1.5 0v2.5a.75.75 0 001.5 0v-2.5z" />
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-10.25a.75.75 0 00-1.5 0v2.5a.75.75 0 001.5 0v-2.5z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10.75 8.5a.75.75 0 00-1.5 0v2a.75.75 0 001.5 0v-2zM9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10 18a8 8 0 100-16 8 8 0 000 16zM.5 8.5a.5.5 0 01.5-.5h2a.5.5 0 010 1H1a.5.5 0 01-.5-.5zM8 3a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L2 7.586V4a1 1 0 00-1-1H0a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V5.414l2.293 2.293a1 1 0 001.414 0L10 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L8 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L6 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1H0.5zM10 18a8 8 0 100-16 8 8 0 000 16zm.75-10.25a.75.75 0 00-1.5 0v2.5a.75.75 0 001.5 0v-2.5z" />
                            <path fill-rule="evenodd" d="M9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10.75 8.5a.75.75 0 00-1.5 0v2a.75.75 0 001.5 0v-2zM9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10 18a8 8 0 100-16 8 8 0 000 16zM.5 8.5a.5.5 0 01.5-.5h2a.5.5 0 010 1H1a.5.5 0 01-.5-.5zM8 3a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L2 7.586V4a1 1 0 00-1-1H0a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V5.414l2.293 2.293a1 1 0 001.414 0L10 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L8 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L6 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1H0.5z" />
                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-10.25a.75.75 0 00-1.5 0v2.5a.75.75 0 001.5 0v-2.5z" />
                            <path d="M9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10.75 8.5a.75.75 0 00-1.5 0v2a.75.75 0 001.5 0v-2zM9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10 18a8 8 0 100-16 8 8 0 000 16zm.75-10.25a.75.75 0 00-1.5 0v2.5a.75.75 0 001.5 0v-2.5z" />
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-10.25a.75.75 0 00-1.5 0v2.5a.75.75 0 001.5 0v-2.5z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10.75 8.5a.75.75 0 00-1.5 0v2a.75.75 0 001.5 0v-2zM9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10 18a8 8 0 100-16 8 8 0 000 16zm.75-10.25a.75.75 0 00-1.5 0v2.5a.75.75 0 001.5 0v-2.5z" />
                            <path fill-rule="evenodd" d="M9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10.75 8.5a.75.75 0 00-1.5 0v2a.75.75 0 001.5 0v-2zM9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10 18a8 8 0 100-16 8 8 0 000 16zM.5 8.5a.5.5 0 01.5-.5h2a.5.5 0 010 1H1a.5.5 0 01-.5-.5zM8 3a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L2 7.586V4a1 1 0 00-1-1H0a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V5.414l2.293 2.293a1 1 0 001.414 0L10 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L8 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-1a1 1 0 00-1 1v2.586l-2.293-2.293a1 1 0 00-1.414 0L6 5.414V9a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1H0.5zM10 18a8 8 0 100-16 8 8 0 000 16zm.75-10.25a.75.75 0 00-1.5 0v2.5a.75.75 0 001.5 0v-2.5z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10.75 8.5a.75.75 0 00-1.5 0v2a.75.75 0 001.5 0v-2zM9.25 10a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V10zM10 18a8 8 0 100-16 8 8 0 000 16zm.75-10.25a.75.75 0 00-1.5 0v2.5a.75.75 0 001.5 0v-2.5z" />
                        </svg>
                        Upload Photo
                    </button>
                </div>
            </div>
        </div>

        <form class="bg-white rounded-lg shadow-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" id="first_name" value="John" autocomplete="given-name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="Doe" autocomplete="family-name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="email_address" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="email_address" id="email_address" value="john.doe@example.com" autocomplete="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="text" name="phone_number" id="phone_number" value="+1 (555) 123-4567" autocomplete="tel" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
            </div>

            <div class="mt-6">
                <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name</label>
                <input type="text" name="company_name" id="company_name" value="Acme Corporation" autocomplete="organization" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>

            <div class="mt-6">
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <input type="text" name="address" id="address" value="123 Business Street" autocomplete="street-address" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                    <input type="text" name="city" id="city" value="New York" autocomplete="address-level2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                    <input type="text" name="country" id="country" value="United States" autocomplete="country-name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
            </div>

            <div class="mt-6">
                <label for="zip_code" class="block text-sm font-medium text-gray-700">Zip Code</label>
                <input type="text" name="zip_code" id="zip_code" value="10001" autocomplete="postal-code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>

            <div class="mt-6">
                <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                <textarea id="bio" name="bio" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">Business owner and entrepreneur</textarea>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <button type="button" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
    
</body>
</html>