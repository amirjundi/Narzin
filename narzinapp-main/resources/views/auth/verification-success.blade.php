<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Success</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6">
        <div class="text-center">
            <!-- Success Icon -->
            <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $message }}</h2>
            
            <div class="space-y-4 mt-6">
                <!-- Web Login Button -->
                <a href="{{ $webLoginUrl }}" 
                   class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200">
                    Continue to Web Login
                </a>
                
                <!-- Mobile App Button -->
                <a href="{{ $mobileDeepLink }}" 
                   class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg transition duration-200">
                    Open in Mobile App
                </a>
            </div>
        </div>
    </div>

    <script>
        // Handle mobile deep linking
        document.querySelector('a[href="{{ $mobileDeepLink }}"]').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Attempt to open the app
            window.location.href = '{{ $mobileDeepLink }}';
            
            // Fallback after timeout
            setTimeout(function() {
                // Check platform and redirect to appropriate store
                if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                    window.location.href = 'https://apps.apple.com/app/your-app-id'; // Replace with your iOS app store URL
                } else {
                    window.location.href = 'https://play.google.com/store/apps/details?id=your.package.name'; // Replace with your Android app store URL
                }
            }, 1500);
        });
    </script>
</body>
</html>
