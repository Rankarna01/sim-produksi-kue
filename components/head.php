<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistem Produksi Kue</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Poppins', 'sans-serif'] },
                colors: {
                    surface: '#FFFFFF',
                    background: '#F8FAFC',
                    primary: '#2563EB',
                    secondary: '#94A3B8',
                    accent: '#F59E0B',
                    danger: '#EF4444',
                    success: '#10B981'
                }
            }
        }
    }
</script>

<style>
    body { font-family: 'Poppins', sans-serif; background-color: theme('colors.background'); }
    #global-loader { display: none; backdrop-filter: blur(4px); }
</style> 