
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Optional: You can customize Tailwind config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1E40AF',
                    }
                }
            }
        }
    </script>

    @livewireStyles
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Page Content -->
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>